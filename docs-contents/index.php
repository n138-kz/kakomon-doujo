<?php
if(false) {
} elseif(isset($_REQUEST['q']) && $_REQUEST['q']=='phpinfo'){
	phpinfo();
	exit();
} elseif(isset($_REQUEST['q']) && $_REQUEST['q']=='ini_get_all'){
	header('Content-Type: Application/json');
	echo json_encode(ini_get_all()).PHP_EOL;
	exit();
} elseif(isset($_REQUEST['q']) && $_REQUEST['q']=='_server'){
	header('Content-Type: Application/json');
	echo json_encode($_SERVER).PHP_EOL;
	exit();
}
echo '<!DOCTYPE html><html lang="ja"><head></head><body>';
foreach(['phpinfo', 'ini_get_all', '_server'] as $v) {
	echo '<a href="'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].explode('?', $_SERVER['REQUEST_URI'])[0].'?q='.$v.'">'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].explode('?', $_SERVER['REQUEST_URI'])[0].'?q='.$v.'</a><br>';
}
echo '</body></html>';
