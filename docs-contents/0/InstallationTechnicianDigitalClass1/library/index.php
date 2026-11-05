<?php namespace n138;
ini_set('display_errors', 1);
ini_set('error_log', 'php://stderr');
ini_set('log_errors', 1);
session_name('sessid');
session_start();

require_once('/app/define.php');

use \n138\resource;
class resource extends local {
	private $conn_database = null;
	public function __construct () {
		parent::init();
		$this->config = parent::config('get');
		$this->conn_database = local::connectDB($this->config['internal']['database']);
	}
	function checkAuthn() {
		$_SESSION['expires_at'] = $_SESSION['expires_at'] ?? 0;
		if ( $_SESSION['expires_at']-time()<=0 ) {
			http_response_code(401);
			$_SESSION = [];
			header('Content-Type: application/json');
			die(json_encode([
				'status' => 401,
				'error' => [
					'title' => 'Session Expired.',
					'message' => 'Session has timeouted. Please relogin.',
				],
			]));
		}
		$u = self::session_table('get');
		error_log('sid: '.$_SESSION['sid']);
		return $_SESSION['expires_at']-time();
	}
	function session_table ($method='get') {
		if (false){
		} elseif ($method == 'get') {
			try {
				$column = ['s.userid','s.ip_address','s.user_agent','s.session_id','s.updated_at','u.app_username' ];
				if (!$this->conn_database->inTransaction()) { $this->conn_database->beginTransaction(); }
				{
					$sql = 'SELECT '.implode(',', $column).' FROM app_authn_session s JOIN app_user u ON s.userid=u.id WHERE session_id = ?;';
					$stm = $this->conn_database->prepare($sql);
					$stm->execute([$_SESSION['sid']]);
					$res = $stm->fetch(\PDO::FETCH_ASSOC);
					foreach ($res as $k => $v) {
						$res[$k] = $res[$k] ?? 'null';
						$res[$k] = strlen($res[$k])>0 ? $res[$k]:'null';
					}
					return $res;
				}
				if ($this->conn_database->inTransaction()) { $this->conn_database->commit(); }
			} catch (\PDOException $e) {
				if ($this->conn_database->inTransaction()) { $this->conn_database->rollback(); }
				error_log('Error has occured on '.$e->getFile().'#'.$e->getLine());
				error_log('[Stack trace]'.PHP_EOL.'[0] '.$e->getMessage().PHP_EOL.'[1] '.$e->getTraceAsString());
			} catch (\Exception $e) {
				error_log('Error has occured on '.$e->getFile().'#'.$e->getLine());
				error_log('[Stack trace]'.PHP_EOL.'[0] '.$e->getMessage().PHP_EOL.'[1] '.$e->getTraceAsString());
			}
			
			return;
		} elseif ($method == 'refresh-expire') {
			try {
				if (!$this->conn_database->inTransaction()) { $this->conn_database->beginTransaction(); }
				{
					$sql = 'UPDATE app_authn_session SET updated_at=CURRENT_TIMESTAMP WHERE session_id = ? RETURNING FLOOR(EXTRACT(EPOCH FROM (updated_at + make_interval(secs => expires_in))))::BIGINT expires_at';
					$stm = $this->conn_database->prepare($sql);
					$stm->execute([$_SESSION['sid']]);
					$res = $stm->fetch(\PDO::FETCH_ASSOC);
				}
				if ($this->conn_database->inTransaction()) { $this->conn_database->commit(); }
				return $res;
			} catch (\PDOException $e) {
				if ($this->conn_database->inTransaction()) { $this->conn_database->rollback(); }
				error_log('Error has occured on '.$e->getFile().'#'.$e->getLine());
				error_log('[Stack trace]'.PHP_EOL.'[0] '.$e->getMessage().PHP_EOL.'[1] '.$e->getTraceAsString());
			}
		}
	}
	function update_expires_at() {
		$_SESSION = array_merge($_SESSION, self::session_table('refresh-expire'));
		$_SESSION['expires_at'] = $_SESSION['expires_at'] ?? 0;
		return;
	}
	function mondais($method='get', $options=[]) {
		if (false){
		} elseif ($method == 'get') {
			$options['limit'] = $options['limit'] ?? 1;
			$options['limit'] = is_numeric($options['limit']) ? 1*$options['limit'] : 1;
			try {
				if (!$this->conn_database->inTransaction()) { $this->conn_database->beginTransaction(); }
				{
					$sql = "SELECT m.id internal_id,m.openid id,m.mondai,c.name FROM InstallationTechnicianDigitalClass1_mondaiset m JOIN installationtechniciandigitalclass1_category c ON m.mondai->>'category' = c.id::text WHERE deleted = false ORDER BY random() LIMIT ?;";
					$stm = $this->conn_database->prepare($sql);
					$stm->execute([$options['limit']]);
					$res = $stm->fetchAll(\PDO::FETCH_ASSOC);
				}
				if ($this->conn_database->inTransaction()) { $this->conn_database->commit(); }
				for ($i=0; $i < count($res); $i++) {
					$res[$i]['mondai'] = json_decode($res[$i]['mondai'],1);
				}
				return [
					'options' => $options,
					'content' => $res,
				];
			} catch (\PDOException $e) {
				if ($this->conn_database->inTransaction()) { $this->conn_database->rollback(); }
				error_log('Error has occured on '.$e->getFile().'#'.$e->getLine());
				error_log('[Stack trace]'.PHP_EOL.'[0] '.$e->getMessage().PHP_EOL.'[1] '.$e->getTraceAsString());
			}
		} else {
			error_log('Unknown params $1: ');
		}
	}

}

$proc = new resource();
header('Content-Type: text/plain');
$proc->checkAuthn();
$proc->update_expires_at();

$requestForm = $_GET ?? [];
$requestForm['limit'] = $requestForm['limit'] ?? 1;
$requestForm['limit'] = is_numeric($requestForm['limit']) ? 1*$requestForm['limit'] : 1;

$content = $proc->mondais('get', $requestForm)['content'];
foreach ($content as $k => $v) {
	$mondai = $v['mondai'];
	$content[$k] = array_merge($v, $mondai);
}
header('Content-Type: application/json');
echo json_encode($content);
exit;
