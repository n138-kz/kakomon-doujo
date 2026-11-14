<?php namespace n138;
ini_set('display_errors', 1);
ini_set('error_log', 'php://stderr');
ini_set('log_errors', 1);
session_name('sessid');
session_start();

require_once('/app/define.php');

use \n138\resource_1;
$proc = new resource_1();
header('Content-Type: text/plain');
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

$role = $proc->role('app');
$content = [];
$content['user'] = [];
foreach (['app', 'InstallationTechnicianDigitalClass1'] as $v) {
	$content['user'][$v] = $proc->role($v);
}
$content['data'][] = [
	'name'=>'工事担任者 デジタル1級',
	'id'=>'InstallationTechnicianDigitalClass1',
	'content'=>[
		'category' => $proc->mondais_category('InstallationTechnicianDigitalClass1')['content'],
		'category_selected' => 3,
	],
];

header('Content-Type: application/json');
echo json_encode($content);
exit;
