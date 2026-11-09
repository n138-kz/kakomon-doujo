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
	public function role () {
		try {
			if (!$this->conn_database->inTransaction()) { $this->conn_database->beginTransaction(); }
			{
				$sql = 'SELECT * FROM app_user_role ORDER BY id';
				$stm = $this->conn_database->prepare($sql);
				$stm->execute();
				$role_descr = $res = $stm->fetchAll(\PDO::FETCH_ASSOC);

				$sql = 'SELECT * FROM InstallationTechnicianDigitalClass1_user WHERE id = ?';
				$stm = $this->conn_database->prepare($sql);
				$stm->execute([$_SESSION['uid']]);
				$res = $stm->fetchAll(\PDO::FETCH_ASSOC);
				if ($stm->rowCount()===0) {
					$sql = 'SELECT u.id, u.role FROM app_user u WHERE u.id = ?';
					$stm = $this->conn_database->prepare($sql);
					$stm->execute([$_SESSION['uid']]);
					$res = $stm->fetchAll(\PDO::FETCH_ASSOC);
					if ($stm->rowCount()===0) {
						return [
							'user'=>0,
							'role_descr' => $role_descr,
						];
					}

					$sql = 'INSERT INTO InstallationTechnicianDigitalClass1_user (id, role) SELECT u.id, u.role FROM app_user u WHERE u.id = ? RETURNING id, role;';
					$stm = $this->conn_database->prepare($sql);
					$stm->execute([$_SESSION['uid']]);
					$res = $stm->fetch(\PDO::FETCH_ASSOC);
				}
			}
			if ($this->conn_database->inTransaction()) { $this->conn_database->commit(); }
			return [
				'user' => $res[0]['role'],
				'role_descr' => $role_descr,
			];
		} catch (\PDOException $e) {
			if ($this->conn_database->inTransaction()) { $this->conn_database->rollback(); }
			error_log('Error has occured on '.$e->getFile().'#'.$e->getLine());
			error_log('[Stack trace]'.PHP_EOL.'[0] '.$e->getMessage().PHP_EOL.'[1] '.$e->getTraceAsString());
		}
	}
	public function mondais_category($method='get', $options=[]) {
		if (false){
		} elseif ($method == 'get') {
			try {
				if (!$this->conn_database->inTransaction()) { $this->conn_database->beginTransaction(); }
				{
					$sql = "SELECT * FROM InstallationTechnicianDigitalClass1_category ORDER BY id ASC;";
					$stm = $this->conn_database->prepare($sql);
					$stm->execute();
					$res = $stm->fetchAll(\PDO::FETCH_ASSOC);
				}
				if ($this->conn_database->inTransaction()) { $this->conn_database->commit(); }
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
			$mondais_category = self::mondais_category('get');
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

		} else {
			error_log('Unknown params $1: '.$method);
		}
	}
}

$proc = new resource();
header('Content-Type: text/plain');
$proc->checkAuthn();
$proc->update_expires_at();
$role = $proc->role();
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
$requestForm['limit'] = (1*$requestForm['limit'])>0 ? 1*$requestForm['limit'] : 1;

if (false) {
} elseif (isset($_GET['what_length_mondai'])) {
	header('Content-Type: application/json');
	echo json_encode($proc->mondais('count', $requestForm));
	exit;
}

if (count($proc->mondais_assign('get'))>0) {
	exit;
}
$content = $proc->mondais('get', $requestForm)['content'];
foreach ($content as $k => $v) {
	$mondai = $v['mondai'];
	$content[$k] = array_merge($v, $mondai);
}
header('Content-Type: application/json');
echo json_encode($content);
exit;
