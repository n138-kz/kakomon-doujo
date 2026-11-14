<?php namespace n138;
ini_set('display_errors', 0);
ini_set('error_log', 'php://stderr');
ini_set('log_errors', 1);
session_name('sessid');
session_start();

require_once('/app/define.php');

use \n138\simple_resource;
$proc = new simple_resource();
$proc->checkAuthn();
$proc->update_expires_at();
$role = $proc->role('app');
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
error_log(__FILE__.'#'.__LINE__.PHP_EOL.var_export(['user'=>$role['user'], 'required'=>DB_ACCESS_LEVEL_FLAG_COMBI_READ],1));

if (!isset($_SERVER['REQUEST_METHOD']) || ( $_SERVER['REQUEST_METHOD'] != 'GET' )){
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode([
		'status' => http_response_code(),
        'error'=>[
            'title'=>'Method NOT Allowed',
            'message'=>'Only GET methods are allowed.',
        ],
    ]);
    exit;
}

if (!isset($_GET['q']) || $_GET['q']=='') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
		'status' => http_response_code(),
        'error'=>[
            'title'=>'Bad Request',
            'message'=>'Missing key: q',
        ],
    ]);
    exit;
}

$resourceUrl = "{$proc->config['internal']['s3-object-storage']['endpoint']}{$_GET['q']}";
$resource = file_get_contents($resourceUrl);

if ($resource === false) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode([
		'status' => http_response_code(),
        'error'=>[
            'title'=>'Object NOT Found',
            'message'=>'Request object has not found.',
        ],
    ]);
    exit;
}

{
    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->buffer($resource);
}

header('Content-Type: ' . explode(';', $mimeType)[0]);
echo $resource;