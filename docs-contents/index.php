<?php
function clientIsInternal ($ipaddress='::1') {
	$is_ipv4 = filter_var($ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
	$is_ipv6 = filter_var($ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
	if (!$is_ipv4 && !$is_ipv6) {
		return;
	}

	$is_internal = false;
	if ($is_ipv4) {
		// プライベート範囲、または予約済み範囲（127.0.0.1 や 169.254.x.x を含む）に「該当する場合」は true
		if (filter_var($ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
			$is_internal = true;
		}
	}
	if ($is_ipv6) {
		// まずPHP標準のフラグでチェック（ループバックや一部の予約済みを判定）
		if (filter_var($ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
			$is_internal = true;
		} else {
			// PHPのフラグでは漏れる可能性のある ULA や リンクローカルをバイナリで厳密にチェック
			$binary_ip = inet_pton($ipaddress);
			if ($binary_ip !== false) {
				$first_byte = ord($binary_ip);

				// ULA (fc00::/7) のチェック
				$is_ula = ($first_byte & 0xfe) === 0xfc;

				// リンクローカル (fe80::/10) のチェック
				$is_link_local = ($first_byte & 0xc0) === 0x80;

				if ($is_ula || $is_link_local) {
					$is_internal = true;
				}
			}
		}
	}
	return $is_internal;
}
if (!clientIsInternal()) {
	exit;
}

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
echo '<!DOCTYPE html><html lang="ja"><head><meta http-equiv="refresh" content="3;./auth/"><style>p{font-size: xx-large;}</style><script>window.addEventListener(`DOMContentLoaded`, ()=>{const t=(Date.now()/10**3)+(1*document.querySelector(`meta[http-equiv="refresh"]`).content.split(`;`)[0]);const d=document.createElement(`p`);document.body.appendChild(d);const s=setInterval(()=>{const r=Math.ceil(t-(Date.now()/10**3));d.textContent = `Redirecting in ${r}s`;if(r<=0) {clearInterval(s);location.replace(document.querySelector(`meta[http-equiv="refresh"]`).content.split(`;`)[1]);}}, 500);});</script></head><body>';
foreach(['phpinfo', 'ini_get_all', '_server'] as $v) {
	echo '<a href="'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].explode('?', $_SERVER['REQUEST_URI'])[0].'?q='.$v.'">'.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].explode('?', $_SERVER['REQUEST_URI'])[0].'?q='.$v.'</a><br>';
}
echo '</body></html>';
