<?php
ini_set('display_errors', 1);
ini_set('error_log', 'php://stderr');
ini_set('log_errors', 1);
session_name('sessid');
session_start();

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
		} catch (PDOException $e) {
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
			} catch (PDOException $e) {
				$this->conn_database->rollback();
				error_log('Error has occured on '.__FILE__.'#'.__LINE__);
				error_log('PDO Error has occured: '.$e->getMessage());
			}
		}
		return $res_fetched;
	}
}
class auth {
	const FILE_CONFIG = '/app'.'/config.json';
	public $config = [];
	private $conn_database = null;
	private function init () {
		for($i=0;$i<10;$i++){
			error_log('');
		}
		error_log('------------------------------');
		error_log('php version: '.phpversion());
		return;
	}
	private function config ($method='get',$data=[]) {
		$default = [];
		$default['internal'] = [];
		$default['internal']['database'] = [];
		$default['internal']['database']['host'] = 'localhost';
		$default['internal']['database']['port'] = 0;
		$default['internal']['database']['database'] = 'postgres';
		$default['internal']['database']['username'] = 'postgres';
		$default['internal']['database']['password'] = 'postgres';
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
	private function connectDB ($conn) {
		$conn['conndsn'] = "pgsql:host={$conn['host']};port={$conn['port']};dbname={$conn['database']}";
		try {
			$conn_database = new PDO($conn['conndsn'], $conn['username'], $conn['password'], [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			]);
			$this->conn_database = $conn_database;
		} catch (PDOException $e) {
			http_response_code(500);
			$conn_database = NULL;
			error_log(__FILE__.'#'.__LINE__.': '. $e->getMessage());
			die('Server Error'.PHP_EOL);
		} finally {
			return $conn_database;
		}
	}
	private function cacheDB ($method='get', $params=[]) {
	}
	public function main () {
		self::init();
		$config = self::config();
		$conn_database = self::connectDB($config['internal']['database']);
		self::cacheDB('get', ['name'=>'ipinfo_history']);
		$proc_exapi = new \exapi($config, $conn_database);
		
		$ipinfo_inquiry = $proc_exapi->ipinfo_inquiry($_SERVER['REMOTE_ADDR']);
		error_log(json_encode($ipinfo_inquiry, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

		if (!isset($_SERVER['REQUEST_METHOD']) || ( $_SERVER['REQUEST_METHOD'] != 'GET' && $_SERVER['REQUEST_METHOD'] != 'POST' )){
			http_response_code(405);
			header('Content-Type: Application/json');
			echo json_encode([
				'status'=>405,
				'error'=>[
					'title'=>'Method NOT Allowed',
					'message'=>'Only GET or POST methods are allowed.',
				],
			]);
			exit;
		}




		http_response_code(418);echo '418'.PHP_EOL;
	}
}
$proc = new \auth();
$proc->main();
