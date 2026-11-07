<?php namespace n138;
ini_set('display_errors', 1);
ini_set('error_log', 'php://stderr');
ini_set('log_errors', 1);
session_name('sessid');
session_start();

require_once('/app/define.php');

use \n138\simple_resource;
$proc = new simple_resource();
$proc->checkAuthn();
$proc->update_expires_at();
http_response_code(204);
exit;
