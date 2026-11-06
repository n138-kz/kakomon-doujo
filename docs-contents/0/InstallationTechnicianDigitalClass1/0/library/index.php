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
                $sql = 'SELECT * FROM app_user_role';
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
	function mondais_category($method='get', $options=[]) {
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
}

$proc = new resource();
header('Content-Type: text/plain');
$proc->checkAuthn();
$proc->update_expires_at();

header('Content-Type: application/json');
http_response_code(200);
echo json_encode([
    'status'=>http_response_code(),
    'data' => [
        'mondais' => [
            ['id'=>$proc->generate_uuidv4(),]
        ],
    ]
]);
exit;
