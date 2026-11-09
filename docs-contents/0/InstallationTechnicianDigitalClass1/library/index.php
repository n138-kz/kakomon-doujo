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
		$this->conn_database = parent::connectDB($this->config['internal']['database']);
	}
	public function mondais($method='get', $options=[]) {
		if (false){
		} elseif ($method == 'get') {
			$options['limit'] = $options['limit'] ?? 1;
			$options['limit'] = is_numeric($options['limit']) ? 1*$options['limit'] : 1;
			$options['limit'] = (1*$options['limit'])>0 ? 1*$options['limit'] : 1;
			try {
				if (!$this->conn_database->inTransaction()) { $this->conn_database->beginTransaction(); }
				{
					$sql = "SELECT * FROM vw_InstallationTechnicianDigitalClass1_mondaiset_ramdom_mondais LIMIT ?;";
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
		} elseif ($method == 'getbyid') {
			$options['id'] = $options['id'] ?? null;
			$options['id'] = $options['id']!='' ? $options['id'] : null;
			if (is_null($options['id'])) { return; }

			try {
				if (!$this->conn_database->inTransaction()) { $this->conn_database->beginTransaction(); }
				{
					$sql = "SELECT * FROM InstallationTechnicianDigitalClass1_mondaiset WHERE id=?;";
					$stm = $this->conn_database->prepare($sql);
					$stm->execute([$options['id']]);
					$res = $stm->fetch(\PDO::FETCH_ASSOC);
				}
				if ($this->conn_database->inTransaction()) { $this->conn_database->commit(); }
				return [
					'options' => $options,
					'content' => $res,
					'summary' => $stm->rowCount(),
				];
			} catch (\PDOException $e) {
				if ($this->conn_database->inTransaction()) { $this->conn_database->rollback(); }
				error_log('Error has occured on '.$e->getFile().'#'.$e->getLine());
				error_log('[Stack trace]'.PHP_EOL.'[0] '.$e->getMessage().PHP_EOL.'[1] '.$e->getTraceAsString());
			}

		} elseif ($method == 'count') {
			$proc = new resource_1();
			$mondais_category = $proc->mondais_category('InstallationTechnicianDigitalClass1', 'get');
			try {
				if (!$this->conn_database->inTransaction()) { $this->conn_database->beginTransaction(); }
				{
					$sql = 'SELECT * FROM vw_InstallationTechnicianDigitalClass1_mondaiset_count;';
					$stm = $this->conn_database->prepare($sql);
					$stm->execute();
					$res = $stm->fetch(\PDO::FETCH_ASSOC);
				}
				if ($this->conn_database->inTransaction()) { $this->conn_database->commit(); }
				return [
					'count' => [
						'all' => $res['count_all'],
						'category' => [
							$res['count_category_1'],
							$res['count_category_2'],
							$res['count_category_3'],
						],
					],
				];
			} catch (\PDOException $e) {
				if ($this->conn_database->inTransaction()) { $this->conn_database->rollback(); }
				error_log('Error has occured on '.$e->getFile().'#'.$e->getLine());
				error_log('[Stack trace]'.PHP_EOL.'[0] '.$e->getMessage().PHP_EOL.'[1] '.$e->getTraceAsString());
			}
		} else {
			error_log('Unknown params $1: '.$method);
		}
	}
	public function mondais_assign($method='get', $options=[]) {
		if (false){
		} elseif ($method == 'get') {
			try {
				if (!$this->conn_database->inTransaction()) { $this->conn_database->beginTransaction(); }
				{
					$sql = 'SELECT * FROM InstallationTechnicianDigitalClass1_assigns WHERE session_id = ?;';
					$stm = $this->conn_database->prepare($sql);
					$stm->execute([$_SESSION['sid']]);
					$res = $stm->fetchAll(\PDO::FETCH_ASSOC);
				}
				if ($this->conn_database->inTransaction()) { $this->conn_database->commit(); }
				return $res;
			} catch (\PDOException $e) {
				if ($this->conn_database->inTransaction()) { $this->conn_database->rollback(); }
				error_log('Error has occured on '.$e->getFile().'#'.$e->getLine());
				error_log('[Stack trace]'.PHP_EOL.'[0] '.$e->getMessage().PHP_EOL.'[1] '.$e->getTraceAsString());
			}
		} elseif ($method == 'set') {
			try {
				if (!$this->conn_database->inTransaction()) { $this->conn_database->beginTransaction(); }
				{
					$sql = 'INSERT INTO InstallationTechnicianDigitalClass1_assigns (user_id, session_id, mondai_id) VALUES (?,?,?);';
					$stm = $this->conn_database->prepare($sql);
					$stm->execute([$_SESSION['uid'], $_SESSION['sid'], $options['mondai_id']]);
					$res = $stm->fetchAll(\PDO::FETCH_ASSOC);
				}
				if ($this->conn_database->inTransaction()) { $this->conn_database->commit(); }
				return $res;
			} catch (\PDOException $e) {
				if ($this->conn_database->inTransaction()) { $this->conn_database->rollback(); }
				error_log('Error has occured on '.$e->getFile().'#'.$e->getLine());
				error_log('[Stack trace]'.PHP_EOL.'[0] '.$e->getMessage().PHP_EOL.'[1] '.$e->getTraceAsString());
			}
		} elseif ($method == 'reset') {
			try {
				if (!$this->conn_database->inTransaction()) { $this->conn_database->beginTransaction(); }
				{
					$sql = 'DELETE FROM InstallationTechnicianDigitalClass1_assigns WHERE session_id = ?';
					$stm = $this->conn_database->prepare($sql);
					$stm->execute([$_SESSION['sid']]);
					$res = $stm->fetchAll(\PDO::FETCH_ASSOC);
				}
				if ($this->conn_database->inTransaction()) { $this->conn_database->commit(); }
				return $res;
			} catch (\PDOException $e) {
				if ($this->conn_database->inTransaction()) { $this->conn_database->rollback(); }
				error_log('Error has occured on '.$e->getFile().'#'.$e->getLine());
				error_log('[Stack trace]'.PHP_EOL.'[0] '.$e->getMessage().PHP_EOL.'[1] '.$e->getTraceAsString());
			}
		} else {
			error_log('Unknown params $1: '.$method);
		}
	}
}

$proc = new resource();
$proc1 = new resource_1();
header('Content-Type: text/plain');
$proc->checkAuthn();
$proc->update_expires_at();
$proc1->setDedault_basename('InstallationTechnicianDigitalClass1');
$role = $proc1->role();
foreach ($role['role_descr'] as $k => $v) {
	define('DB_ACCESS_LEVEL_FLAG_'.$v['description'], $v['id']);
	/* * ($role['user'] & DB_ACCESS_LEVEL_FLAG_SEARCH) !== 0 * */
	/* * ($role['user'] & DB_ACCESS_LEVEL_FLAG_READ) !== 0 * */
	/* * ($role['user'] & DB_ACCESS_LEVEL_FLAG_UPDATE) !== 0 * */
	/* * ($role['user'] & DB_ACCESS_LEVEL_FLAG_REGIST) !== 0 * */
	/* * ($role['user'] & DB_ACCESS_LEVEL_FLAG_DELETE) !== 0 * */
}
if (!(($role['user'] & DB_ACCESS_LEVEL_FLAG_COMBI_READ) !== 0)) {
	http_response_code(403);
	header('Content-Type: application/json');
	$m = json_encode([
		'status' => http_response_code(),
		'error' => [
			'title' => 'Forbidden.',
			'message' => 'You are NOT having permission on this resource.',
		],
	]);
	error_log($m); die($m);
}

$requestForm = $_GET ?? [];
$requestForm['limit'] = $requestForm['limit'] ?? 1;
$requestForm['limit'] = is_numeric($requestForm['limit']) ? 1*$requestForm['limit'] : 1;
$requestForm['limit'] = (1*$requestForm['limit'])>0 ? intval(1*$requestForm['limit']) : 1;

if (false) {
} elseif (isset($_GET['what_length_mondai'])) {
	header('Content-Type: application/json');
	echo json_encode($proc->mondais('count', $requestForm));
	exit;
}

$assigned_mondais = $proc->mondais_assign('get') ?? [];
if (count($assigned_mondais)>0) {
	if (count($assigned_mondais)===$requestForm['limit']) {
		for ($i=0;$i<count($assigned_mondais);$i++) { 
			$assigned_mondais[$i] = $proc->mondais('getbyid', ['id'=>$assigned_mondais[$i]['mondai_id']])['content'];
			$assigned_mondais[$i] = array_merge($assigned_mondais[$i], json_decode($assigned_mondais[$i]['mondai'], true));
		}
		header('Content-Type: application/json');
		error_log(count($assigned_mondais).PHP_EOL.'[0]: '.PHP_EOL.json_encode($assigned_mondais[0], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
		echo json_encode($assigned_mondais);
		exit;
	}
	$proc->mondais_assign('reset');
}
$content = $proc->mondais('get', $requestForm)['content'];
foreach ($content as $k => $v) {
	$proc->mondais_assign('set', ['mondai_id'=>$v['internal_id']]);
	$content[$k] = array_merge($v, $v['mondai']);
}

header('Content-Type: application/json');
echo json_encode($content);
exit;
