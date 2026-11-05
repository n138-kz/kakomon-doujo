<?php namespace n138;
ini_set('display_errors', 1);
ini_set('error_log', 'php://stderr');
ini_set('log_errors', 1);

class local {
	const FILE_CONFIG = '/app'.'/config.json';
	public $config = [];
	private $conn_database = null;
	public function config ($method='get',$data=[]) {
		$keypare = self::keypare_create();
		$default = [];
		$default['internal'] = [];
		$default['internal']['database'] = [];
		$default['internal']['database']['host'] = 'localhost';
		$default['internal']['database']['port'] = 0;
		$default['internal']['database']['database'] = 'postgres';
		$default['internal']['database']['username'] = 'postgres';
		$default['internal']['database']['password'] = 'postgres';
		$default['internal']['public-key-cryptography'] = [];
		$default['internal']['public-key-cryptography']['private-key'] = '';
		$default['internal']['public-key-cryptography']['publish-key'] = '';
		$default['internal']['symmetric-key-cryptography'] = [];
		$default['internal']['symmetric-key-cryptography']['common-key'] = '';
		$default['external'] = [];
		$default['external']['google'] = [];
		$default['external']['ipinfo'] = [];
		$default['external']['ipinfo']['token'] = '';
		$default['external']['ipinfo']['scheme'] = 'Bearer';
		$default['external']['discord'] = [];
		$default['external']['discord']['trap'] = [];
		$default['external']['discord']['bot'] = [];

		if(!file_exists(self::FILE_CONFIG)){
			http_response_code(500);
			error_log('No such File or Directory: '.self::FILE_CONFIG);
			if (is_writable(self::FILE_CONFIG)) {
				file_put_contents(self::FILE_CONFIG, json_encode($default, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
			} else {
				error_log('Please make sure the below to text file.'.PHP_EOL.json_encode($default, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
			}
			die('Server Error'.PHP_EOL);
		}

		$config_data_text=file_get_contents(self::FILE_CONFIG);
		$config_data_json=json_decode($config_data_text, TRUE);
		$config = array_merge($default, $config_data_json);
		if($method=='get') {
			$this->config = $config_data_json;
			return $config_data_json;
		}
		return;
	}
	private function keypare_create () {
		# RSA秘密鍵
		$config = array(
			"private_key_bits" => 3072,
			"private_key_type" => OPENSSL_KEYTYPE_RSA,
		);
		$res = openssl_pkey_new($config);
		openssl_pkey_export($res, $privateKey);

		# 公開鍵
		$res = openssl_pkey_get_details($res);
		$publicKey = $res["key"];
		$res = null;

		# 共通鍵
		$keyLength = 32;
		$symmetricKey = random_bytes($keyLength);
		$commonKey = base64_encode($symmetricKey);

		return ['privateKey'=>$privateKey, 'publicKey'=>$publicKey, 'commonKey'=>$commonKey, ];
	}
	protected function connectDB ($conn) {
		$conn['conndsn'] = "pgsql:host={$conn['host']};port={$conn['port']};dbname={$conn['database']}";
		try {
			$conn_database = new \PDO($conn['conndsn'], $conn['username'], $conn['password'], [
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
			]);
			$this->conn_database = $conn_database;
		} catch (\PDOException $e) {
			http_response_code(500);
			$conn_database = NULL;
			error_log(__FILE__.'#'.__LINE__.': '. $e->getMessage());
			die('Server Error'.PHP_EOL);
		} finally {
			return $conn_database;
		}
	}
}

class exapi {
	private $config = [];
	private $conn_database = null;
	public function __construct($config=null, $conn_database=null) {
		if (is_null($config) || is_null($conn_database)) {
			error_log('class exapi initilization error on '.__FILE__.'#'.__LINE__);
			die('Server Error'.PHP_EOL);
		}
		$this->config = $config;
		$this->conn_database = $conn_database;
		return;
	}
	public function ipinfo_inquiry ($ipaddress='127.0.0.1') {
		$cache = null;
		$res_fetched = null;

		$is_ipv4 = filter_var($ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
		$is_ipv6 = filter_var($ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
		if (!$is_ipv4 && !$is_ipv6) {
			return;
		}

		$is_internal = false;
		if ($is_ipv4) {
			// プライベート範囲、または予約済み範囲（127.0.0.1 や 169.254.x.x を含む）に「該当する場合」は true
			if (filter_var($ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
				$is_internal = true;
			}
		}
		if ($is_ipv6) {
			// まずPHP標準のフラグでチェック（ループバックや一部の予約済みを判定）
			if (filter_var($ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
				$is_internal = true;
			} else {
				// PHPのフラグでは漏れる可能性のある ULA や リンクローカルをバイナリで厳密にチェック
				$binary_ip = inet_pton($ipaddress);
				if ($binary_ip !== false) {
					$first_byte = ord($binary_ip);

					// ULA (fc00::/7) のチェック
					$is_ula = ($first_byte & 0xfe) === 0xfc;

					// リンクローカル (fe80::/10) のチェック
					$is_link_local = ($first_byte & 0xc0) === 0x80; 

					if ($is_ula || $is_link_local) {
						$is_internal = true;
					}
				}
			}
		}
		if ($is_internal) {
			return [ 'bogon' => true, 'ip' => $ipaddress, 'resolver' => 'local php:'.phpversion() ];
		}

		$sql = 'SELECT rawjson FROM ipinfo_history WHERE ip=?';
		try {
			$this->conn_database->beginTransaction();
			$stm = $this->conn_database->prepare($sql);
			$res = $stm->execute([$ipaddress]);
			$this->conn_database->commit();
			$cache = $stm->rowCount();
			if ($cache>0) {
				$res_fetched = $stm->fetch();
				$res_fetched = json_decode($res_fetched[0], true);
			}
		} catch (\PDOException $e) {
			$this->conn_database->rollback();
			error_log('Error has occured on '.__FILE__.'#'.__LINE__);
			error_log('PDO Error has occured: '.$e->getMessage());
			$cache = 0;
		}

		if ($cache==0) {
			$url = "https://api.ipinfo.io/lite/{$ipaddress}";

			$options = [
				'http' => [
					'method' => 'GET',
					'header' => implode("\r\n", [
						'User-Agent: php/'.phpversion(),
						'Accept: application/json',
						'Authorization: ' . $this->config['external']['ipinfo']['scheme'] . ' ' . $this->config['external']['ipinfo']['token'],
					]),
					'ignore_errors' => true,
				],
			];

			$context = stream_context_create($options);

			$res = file_get_contents($url, false, $context);
			error_log("{$url}: fetched size: ".strlen($res)." bytes");
			$res = $res ? json_decode($res, true) : null;
			$res = isset($res['ip']) ? $res : null;

			if (is_null($res)) {
				error_log('#'.__LINE__.' '.var_export($res, true));
				return null;
			}
			$res_fetched = $res;
			error_log('#'.__LINE__.' '.var_export($res, true));

			try {
				$sql = 'INSERT INTO ipinfo_history(ip,rawjson) VALUES (?,?)';
				$this->conn_database->beginTransaction();
				$stm = $this->conn_database->prepare($sql);
				$res = $stm->execute([$ipaddress, json_encode($res)]);
				$this->conn_database->commit();
			} catch (\PDOException $e) {
				$this->conn_database->rollback();
				error_log('Error has occured on '.__FILE__.'#'.__LINE__);
				error_log('PDO Error has occured: '.$e->getMessage());
			}
		}
		return $res_fetched;
	}
}
