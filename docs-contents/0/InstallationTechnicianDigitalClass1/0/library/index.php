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
			$template_mondai = [];
			{
				$template_mondai['category'] = 0;
				$template_mondai['kaisetsu'] = [];
				$template_mondai['kaisetsu']['text'] = '';
				$template_mondai['kaisetsu']['image'] = [];
				$template_mondai['mondai']['text'] = '';
				$template_mondai['mondai']['image'] = [];
				$template_mondai['sentakushi'] = [];
				$template_mondai['sentakushi'][] = ['text'=>'', 'answer'=>false,];
				array_pop($template_mondai['sentakushi']);
				$template_mondai['kakosyutsudai'] = [];
				ksort($template_mondai);
			}
			try {
				if (!$this->conn_database->inTransaction()) { $this->conn_database->beginTransaction(); }
				{
					$sql = "SELECT * FROM InstallationTechnicianDigitalClass1_mondaiset ORDER BY openid ASC;";
					$stm = $this->conn_database->prepare($sql);
					$stm->execute();
					$res = $stm->fetchAll(\PDO::FETCH_ASSOC);
				}
				if ($this->conn_database->inTransaction()) { $this->conn_database->commit(); }
				for ($i=0; $i < count($res); $i++) {
					$res[$i]['mondai'] = json_decode($res[$i]['mondai'],1);
					ksort($res[0]['mondai']);
					$res[$i]['mondai'] = array_merge($template_mondai, $res[$i]['mondai']);
					foreach ($res[$i]['mondai'] as $k1 => $v1) {
						if (!is_array($v1)) continue;
						$res[$i]['mondai'][$k1] = array_merge($template_mondai[$k1], $res[$i]['mondai'][$k1]);
					}
				}
				return [
					'options' => $options,
					'content' => $res,
					'template' => $template_mondai,
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
					$res = $stm->fetchAll(\PDO::FETCH_ASSOC);
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

		} elseif ($method == 'update') {
			$options['id'] = $options['id'] ?? null;
			$options['id'] = $options['id']!='' ? $options['id'] : null;
			if (is_null($options['id'])) { return; }

			if (self::mondais('getbyid',['id'=>$options['id']])['summary']!==1) {
				return;
			}
			try {
				if (!$this->conn_database->inTransaction()) { $this->conn_database->beginTransaction(); }
				{
					$sql = "UPDATE InstallationTechnicianDigitalClass1_mondaiset SET openid=:item WHERE id=:id;";
					$stm = $this->conn_database->prepare($sql);
					$stm->execute([
						'item'=>$options['openid'],
						'id'=>$options['id'],
					]);
					$sql = "UPDATE InstallationTechnicianDigitalClass1_mondaiset SET deleted=:item WHERE id=:id;";
					$stm = $this->conn_database->prepare($sql);
					$stm->execute([
						'item'=>(!empty($options['deleted']) ? 1 : 0),
						'id'=>$options['id'],
					]);
					$sql = "UPDATE InstallationTechnicianDigitalClass1_mondaiset SET mondai=:item WHERE id=:id;";
					$stm = $this->conn_database->prepare($sql);
					$stm->execute([
						'item'=>json_encode($options['mondai'],JSON_UNESCAPED_UNICODE),
						'id'=>$options['id'],
					]);
				}
				if ($this->conn_database->inTransaction()) { $this->conn_database->commit(); }
				return [
					'options' => $options,
					'content' => self::mondais('getbyid',['id'=>$options['id']])['content'],
					'summary' => self::mondais('getbyid',['id'=>$options['id']])['summary'],
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
if (!(($role['user'] & DB_ACCESS_LEVEL_FLAG_UPDATE) !== 0)) {
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
if (false) {
} elseif ($_SERVER['REQUEST_METHOD']=='GET') {
	header('Content-Type: application/json');
	http_response_code(200);
	echo json_encode([
		'status'=>http_response_code(),
		'user' => [
			'permission' => [
				'role' => $role['user'],
				'role_catalog' => $role['role_descr'],
				'minimum_accesslevel' => DB_ACCESS_LEVEL_FLAG_UPDATE,
				'access_approved' => (($role['user'] & DB_ACCESS_LEVEL_FLAG_UPDATE) !== 0),
			]
		],
		'data' => [
			'mondais' => $proc->mondais('get')['content'],
			'mondais_category' => $proc->mondais_category()['content'],
			'mondais_summary' => $proc->mondais('count'),
			'mondais_template' => $proc->mondais('get')['template'],
		]
	]);
	exit;
} elseif ($_SERVER['REQUEST_METHOD']=='POST') {
	if (!(($role['user'] & DB_ACCESS_LEVEL_FLAG_COMBI_UPDATE) !== 0)) {
		http_response_code(403);
		header('Content-Type: application/json');
		$m = json_encode([
			'status' => http_response_code(),
			'user' => [
				'permission' => [
					'role' => $role['user'],
					'role_catalog' => $role['role_descr'],
					'minimum_accesslevel' => DB_ACCESS_LEVEL_FLAG_COMBI_UPDATE,
					'access_approved' => (($role['user'] & DB_ACCESS_LEVEL_FLAG_COMBI_UPDATE) !== 0),
				]
			],
			'error' => [
				'title' => 'Forbidden.',
				'message' => 'You are NOT having permission on this resource.',
			],
		]);
		error_log($m); die($m);
	}
	header('Content-Type: application/json; charset=UTF-8');
	$_POST = file_get_contents('php://input');
	$_POST = json_decode($_POST, true);
	$_REQUEST = array_merge($_REQUEST, $_POST);
	if (json_last_error() !== JSON_ERROR_NONE) {
		http_response_code(400);
		$m = json_encode([
			'status' => http_response_code(),
			'error' => [
				'title' => 'Invalid JSON.',
				'message' => 'Contains invalid JSON format.',
			],
		]);
		error_log($m); die($m);
	}

	if ($proc->mondais('getbyid', ['id' => $_POST['id']])['summary']===1) {
		error_log('uuid: '.$_POST['id']);
		error_log(__LINE__.PHP_EOL.json_encode($proc->mondais('update', $_POST)));
	}

	http_response_code(204);
}
