<?php namespace n138;
ini_set('display_errors', 1);
ini_set('error_log', 'php://stderr');
ini_set('log_errors', 1);
session_name('sessid');
session_start();

require_once('/app/define.php');
class auth extends local {
	const FILE_CONFIG = '/app'.'/config.json';
	public $config = [];
	private $conn_database = null;
	public function __construct () {
		local::init();
	}
	private function binaryToString ($str) {
		$binaryStr = base64_decode($str);
		if ($binaryStr === false || empty($binaryStr)) {
			return '';
		}

		$binaryArray = str_split($binaryStr, 8);
		$hexString = '';

		foreach ($binaryArray as $bin) {
			if (strlen($bin) < 8) continue; 
			$hexString .= str_pad(base_convert($bin, 2, 16), 2, '0', STR_PAD_LEFT);
		}
		return pack('H*', $hexString);
	}
	public function main () {
		$proc_local = new local();
		$config = $proc_local->config();
		$this->conn_database = $conn_database = $proc_local->connectDB($config['internal']['database']);
		$proc_exapi = new exapi($config, $conn_database);

		$ipinfo_inquiry = $proc_exapi->ipinfo_inquiry(parent::clientinf_get()['REMOTE_ADDR']);
		error_log(__FILE__.'#'.__LINE__.PHP_EOL.json_encode($ipinfo_inquiry, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

		if (!isset($_SERVER['REQUEST_METHOD']) || ( $_SERVER['REQUEST_METHOD'] != 'GET' && $_SERVER['REQUEST_METHOD'] != 'POST' )){
			http_response_code(405);
			header('Content-Type: application/json');
			echo json_encode([
				'status'=>405,
				'error'=>[
					'title'=>'Method NOT Allowed',
					'message'=>'Only GET or POST methods are allowed.',
				],
			]);
			exit;
		}

		if (false) {
		} elseif (isset($_REQUEST['credential']) && isset($_REQUEST['iss']) && $_REQUEST['iss']=='https://discord.com') {
			$discord_user = [];
			$discord_push_item = [];
			$client = self::clientinf_get();
			
			try {
				$url = 'https://discordapp.com/api/oauth2/token';
				$data = [
					'client_id'=>$config['external']['discord']['bot']['client_id'],
					'grant_type'=>'authorization_code',
					'code'=>self::binaryToString($_REQUEST['credential']),
					'redirect_uri'=>$client['REQUEST_SCHEME'].'://'.$client['HTTP_HOST'].'/'.basename(dirname(__DIR__)).'/',
				];
				error_log(__FILE__.'#'.__LINE__.PHP_EOL.json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
				$data['client_secret'] = $config['external']['discord']['bot']['client_secret'];
				$options = [ 'content-type: application/x-www-form-urlencoded' ];
				$curl_req = curl_init();
				curl_setopt($curl_req, CURLOPT_URL, $url);
				curl_setopt($curl_req, CURLOPT_POST, TRUE);
				curl_setopt($curl_req, CURLOPT_POSTFIELDS, http_build_query($data));
				curl_setopt($curl_req, CURLOPT_HTTPHEADER, $options);
				curl_setopt($curl_req, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($curl_req, CURLOPT_FOLLOWLOCATION, TRUE);
				$curl_res=curl_exec($curl_req);
				$curl_res=json_decode($curl_res, TRUE);
				$curl_req=null;
				if (isset($curl_res['id_token'])) { $curl_res['id_token'] = null; }
				error_log(__FILE__.'#'.__LINE__.PHP_EOL.json_encode($curl_res, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
				if (isset($curl_res['error']) || isset($curl_res['error_description'])) {
					throw new \Exception(trim("{$curl_res['error']}: {$curl_res['error_description']}"));
				}
				$curl_res['issued_at'] = time();
				$curl_res['expires_in'] = $curl_res['expires_in'] ?? 0;
				$curl_res['expires_at'] = $curl_res['issued_at'] + $curl_res['expires_in'];

				$curl_res['token_type'] = $curl_res['token_type'] ?? null;
				$curl_res['access_token'] = $curl_res['access_token'] ?? null;
				$credential = "{$curl_res['token_type']} {$curl_res['access_token']}";

				$url = 'https://discordapp.com/api/users/@me';
				$options = [ "Authorization: {$credential}" ];
				$curl_req = curl_init();
				curl_setopt($curl_req, CURLOPT_URL, $url);
				curl_setopt($curl_req, CURLOPT_HTTPHEADER, $options);
				curl_setopt($curl_req, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($curl_req, CURLOPT_FOLLOWLOCATION, TRUE);
				$curl_res=curl_exec($curl_req);
				$curl_res=json_decode($curl_res, TRUE);
				$curl_req=null;
				error_log(__FILE__.'#'.__LINE__.PHP_EOL.json_encode($curl_res, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
				if (isset($curl_res['error']) || isset($curl_res['error_description'])) {
					throw new \Exception(trim("{$curl_res['error']}: {$curl_res['error_description']}"));
				}

				$discord_isStatus = [
					'verified'=>$curl_res['verified'] ?? false,
					'mfa_enabled'=>$curl_res['mfa_enabled'] ?? false,
				];
				if (isset($curl_res['id']) && $discord_isStatus['verified'] && $discord_isStatus['mfa_enabled']) {
					$discord_user = $curl_res;
				} else {
					throw new \Exception('[AUTHORIZATION REJECT] The security of your account could not be verified. Please try again after completing Discord email address verification and setting up two-factor authentication (2FA).');
				}
			} catch (\Exception $e) {
				error_log('Error has occured on '.$e->getFile().'#'.$e->getLine());
				error_log('[Stack trace]'.PHP_EOL.$e->getTraceAsString());
				http_response_code(401);
				echo json_encode([
					'status'=>401,
					'error'=>[
						'title'=>'Authentication Failure.',
						'message'=>$e->getMessage(),
					],
				]);
				exit;
			}
			try {
				$discord_push_item['embeds'] = [];
				$discord_push_item['embed'] = [];
				$discord_push_item['embed']['color'] = parent::color('get', false, 'success_green');
				$discord_push_item['embed']['timestamp'] = 'OAuth Test PASS: #'.($discord_user['id']??'unknown');
				$discord_push_item['embed']['title'] = 'OAuth Test PASS: #'.($discord_user['id']??'unknown');
				$discord_push_item['embed']['timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
				$discord_push_item['embed']['thumbnail'] = [];
				$discord_push_item['embed']['thumbnail']['url'] = 'https://cdn.discordapp.com/avatars/'.$discord_user['id'].'/'.$discord_user['avatar'].'.png';
				$discord_push_item['embed']['description'] = '';
				$discord_push_item['embed']['description'] .= 'Discord OAuth Test has Passed.'.PHP_EOL;
				$discord_push_item['embed']['description'] .= 'OAuth ISS(Issuer): '.$_REQUEST['iss'].PHP_EOL;
				$discord_push_item['embed']['description'] .= 'Discord UID: '.$discord_user['id'].PHP_EOL;
				$discord_push_item['embed']['description'] .= 'Discord Username: '.($discord_user['username']??'').PHP_EOL;
				$discord_push_item['embed']['description'] .= 'Discord Global Name: '.($discord_user['global_name']??'').PHP_EOL;
				$discord_push_item['embed']['description'] .= 'Discord User Locale: '.($discord_user['locale']??'').PHP_EOL;
				array_push($discord_push_item['embeds'], $discord_push_item['embed']);
				unset($discord_push_item['embed']);

				$curl_res = [];
				{
					$curl_opt = [ 'Content-Type: application/json', 'Content-Length: '.strlen(json_encode($discord_push_item)), ];
					$curl_req = curl_init();
					curl_setopt($curl_req, CURLOPT_POST, TRUE);
					curl_setopt($curl_req, CURLOPT_POSTFIELDS, json_encode($discord_push_item));
					curl_setopt($curl_req, CURLOPT_HTTPHEADER, $curl_opt);
					curl_setopt($curl_req, CURLOPT_RETURNTRANSFER, TRUE);
					curl_setopt($curl_req, CURLOPT_FOLLOWLOCATION, TRUE);
					foreach ($config['external']['discord']['trap'] as $k => $v) {
						curl_setopt($curl_req, CURLOPT_URL, explode('?', $v)[0].'?wait=true');
						$c = curl_exec($curl_req);
						$curl_res[] = [
							'POST' => [
								'url' => curl_getinfo($curl_req)['url'],
								'response' => [
									json_decode($c, TRUE),
								],
							],
							'PATCH' => [
								'url' => explode('?', curl_getinfo($curl_req)['url'])[0].'/messages/'.json_decode($c, TRUE)['id'],
								'response' => [],
							],
						];
					}

					curl_setopt($curl_req, CURLOPT_CUSTOMREQUEST, 'PATCH');
					foreach ($curl_res as $k => $v) {
						curl_setopt($curl_req, CURLOPT_URL, $v['PATCH']['url']);
						$c = curl_exec($curl_req);
						$curl_res[$k]['PATCH']['response'][] = json_decode($c, TRUE);
					}
					unset($curl_req);
				}
				error_log(__FILE__.'#'.__LINE__.PHP_EOL.var_export($curl_res, 1));
			} catch (\Exception $th) {
				error_log('Error has occured on '.$e->getFile().'#'.$e->getLine());
				error_log('[Stack trace]'.PHP_EOL.$e->getTraceAsString());
			}
			try {
				$session_user = [];
				for ($i=0;$i<2;$i++) {
					$sql = "SELECT id,rawjson FROM app_user WHERE rawjson->>'id' = ?";
					$this->conn_database->beginTransaction();
					$stm = $this->conn_database->prepare($sql);
					$res = $stm->execute([$discord_user['id']]);
					$this->conn_database->commit();
					if ($stm->rowCount()>0) {
						$res = $stm->fetch(\PDO::FETCH_ASSOC);
						$res['id'] = $res['id'] ?? '_';
						$res['rawjson'] = json_decode($res['rawjson'], true) ?? [];
						$session_user = $res;
						error_log(__FILE__.'#'.__LINE__.PHP_EOL.json_encode($res, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

						$sql = 'UPDATE app_user SET rawjson=? WHERE id=?';
						$this->conn_database->beginTransaction();
						$stm = $this->conn_database->prepare($sql);
						$res = $stm->execute([
							json_encode($discord_user),
							$res['id'],
						]);
						$this->conn_database->commit();
					} else {
						$sql = 'INSERT INTO app_user (rawjson) VALUES (?)';
						$this->conn_database->beginTransaction();
						$stm = $this->conn_database->prepare($sql);
						$res = $stm->execute([json_encode($discord_user)]);
						$this->conn_database->commit();
					}
				}

				$this->conn_database->beginTransaction();
				$sql = 'INSERT INTO app_authn_session (userid, ip_address, user_agent, expires_in, payload) VALUES (?,?,?,?,?)';
				$stm = $this->conn_database->prepare($sql);
				$res = $stm->execute([
					$session_user['id'],
					parent::clientinf_get()['REMOTE_ADDR'],
					$_SERVER['HTTP_USER_AGENT'],
					3600,
					json_encode([
						'_SERVER' => $_SERVER,
						'_SESSION' => $_SESSION,
						'_POST' => $_POST,
						'_GET' => $_GET,
						'client' => parent::clientinf_get(),
						'public-key-cryptography' => $config['internal']['public-key-cryptography']['publish-key'],
					], JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR),
				]);

				$sql = 'SELECT userid uid, session_id sid, expires_in, created_at, updated_at, FLOOR(EXTRACT(EPOCH FROM (updated_at + make_interval(secs => expires_in))))::BIGINT expires_at FROM app_authn_session WHERE userid = ? AND user_agent = ? AND ip_address = ? ORDER BY updated_at DESC LIMIT 1;';
				$stm = $this->conn_database->prepare($sql);
				$res = $stm->execute([
					$session_user['id'],
					$_SERVER['HTTP_USER_AGENT'],
					parent::clientinf_get()['REMOTE_ADDR'],
				]);
				if ($stm->rowCount()>0) {
					$res = $stm->fetch(\PDO::FETCH_ASSOC);

					session_regenerate_id(true);
					$_SESSION['uid'] = $res['uid'];
					$_SESSION['sid'] = $res['sid'];
					$_SESSION['expires_at'] = $res['expires_at'];
					error_log(__FILE__.'#'.__LINE__.PHP_EOL.json_encode($_SESSION, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE));

					$sql = "UPDATE app_authn_session SET payload = jsonb_set(payload, '{_SESSION}', ?::jsonb, true) WHERE session_id = ?";
					$stm = $this->conn_database->prepare($sql);
					$stm->execute([
						json_encode($_SESSION),
						$_SESSION['sid'],
					]);
					if ($this->conn_database->inTransaction()) {
						$this->conn_database->commit();
					}
				} else {
					if ($this->conn_database->inTransaction()) {
						$this->conn_database->rollback();
					}
				}
			} catch (\PDOException $e) {
				if ($this->conn_database->inTransaction()) {
					$this->conn_database->rollback();
				}
				error_log('Error has occured on '.$e->getFile().'#'.$e->getLine());
				error_log('[Stack trace]'.PHP_EOL.$e->getTraceAsString());
				error_log('PDO Error has occured: '.$e->getMessage());
			}

			$discord_push_item['embeds'][0]['description'] .= "Internal userid: `{$_SESSION['uid']}`".PHP_EOL;
			$discord_push_item['embeds'][0]['description'] .= "Internal sessionid: `{$_SESSION['sid']}`".PHP_EOL;
			{
				$curl_opt = [ 'Content-Type: application/json', 'Content-Length: '.strlen(json_encode($discord_push_item)), ];
				$curl_req = curl_init();
				curl_setopt($curl_req, CURLOPT_CUSTOMREQUEST, 'PATCH');
				curl_setopt($curl_req, CURLOPT_POSTFIELDS, json_encode($discord_push_item));
				curl_setopt($curl_req, CURLOPT_HTTPHEADER, $curl_opt);
				curl_setopt($curl_req, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($curl_req, CURLOPT_FOLLOWLOCATION, TRUE);
				foreach ($curl_res as $k => $v) {
					curl_setopt($curl_req, CURLOPT_URL, $v['PATCH']['url']);
					$c = curl_exec($curl_req);
					$curl_res[$k]['PATCH']['response'][] = json_decode($c, TRUE);
				}
				unset($curl_req);
			}
			error_log(__FILE__.'#'.__LINE__.PHP_EOL.var_export($curl_res, 1));

			http_response_code(200);
			echo json_encode([
				'status' => 200,
				'location' => '../../0/',
				'message' => 'Authentication Success',
			], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			exit;
		}







		header('Content-Type: application/json');
		http_response_code(401);
		exit(10401);
	}
}

header('Content-Type: application/json');
use \n138\auth;
$proc = new auth();
$proc->main();
