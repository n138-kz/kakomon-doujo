<?php namespace n138;
ini_set('display_errors', 0);
ini_set('error_log', 'php://stderr');
ini_set('log_errors', 1);
session_name('sessid');
session_start();

require_once('/app/define.php');
use \n138\local;
$proc = new local();
if ($proc->clientIsInternal($proc->clientinf_get()['REMOTE_ADDR'])) {
	if(false) {
	} elseif(isset($_REQUEST['q']) && $_REQUEST['q']=='phpinfo'){
		phpinfo();
		exit();
	} elseif(isset($_REQUEST['q']) && $_REQUEST['q']=='ini_get_all'){
		header('Content-Type: application/json');
		echo json_encode(ini_get_all()).PHP_EOL;
		exit();
	} elseif(isset($_REQUEST['q']) && $_REQUEST['q']=='_server'){
		header('Content-Type: application/json');
		echo json_encode($_SERVER).PHP_EOL;
		exit();
	}
}
echo '<!DOCTYPE html><html lang="ja"><head><meta http-equiv="refresh" content="3;./auth/">';
echo '<style>p{font-size: xx-large;}</style>';
echo '<script>';
echo 'window.addEventListener(`DOMContentLoaded`, ()=>{';
echo 'const t=(Date.now()/10**3)+(1*document.querySelector(`meta[http-equiv="refresh"]`).content.split(`;`)[0]);const d=document.createElement(`p`);document.body.appendChild(d);';
echo 'const s=setInterval(()=>{const r=Math.ceil(t-(Date.now()/10**3));d.textContent = `Redirecting in ${r}s`;if(r<=0) {clearInterval(s);location.replace(document.querySelector(`meta[http-equiv="refresh"]`).content.split(`;`)[1]);}}, 500);';
echo '});';
echo '</script>';
echo '</head><body>';
if ($proc->clientIsInternal($proc->clientinf_get()['REMOTE_ADDR'])) {
	foreach(['phpinfo', 'ini_get_all', '_server'] as $v) {
		echo '<a href="'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].explode('?', $_SERVER['REQUEST_URI'])[0].'?q='.$v.'">'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].explode('?', $_SERVER['REQUEST_URI'])[0].'?q='.$v.'</a><br>';
	}
}
echo '</body></html>';
