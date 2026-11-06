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
		} elseif ($method == 'count') {
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
$requestForm['limit'] = (1*$requestForm['limit'])>0 ? 1*$requestForm['limit'] : 1;

if (false) {
} elseif (isset($_GET['what_length_mondai'])) {
	header('Content-Type: application/json');
	echo json_encode($proc->mondais('count', $requestForm));
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
