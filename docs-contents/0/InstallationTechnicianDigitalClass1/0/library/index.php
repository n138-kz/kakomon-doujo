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
