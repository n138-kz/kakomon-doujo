<?php namespace n138;
ini_set('display_errors', 1);
ini_set('error_log', 'php://stderr');
ini_set('log_errors', 1);
session_name('sessid');
session_start();

require_once('/app/define.php');

use \n138\simple_resource;
$proc = new simple_resource();
header('Content-Type: text/plain');
$proc->checkAuthn();
$proc->update_expires_at();

header('Content-Type: application/json');
$content = [];
$content[] = [
	'name'=>'工事担任者 デジタル1級',
	'id'=>'InstallationTechnicianDigitalClass1',
	'content'=>[
		[
			'name'=>'問題数',
			'value'=>5,
		],
	]
];

echo json_encode($content);
exit;
