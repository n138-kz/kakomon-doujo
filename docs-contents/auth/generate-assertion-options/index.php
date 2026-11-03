<?php
ini_set('display_errors', 1);
ini_set('error_log', 'php://stderr');
ini_set('log_errors', 1);
session_name('sessid');
session_start();

require_once('/app/define.php');
header('Content-Type: application/json; charset=utf-8');

// 1. リプレイ攻撃防止用の暗号学的に安全なランダムチャレンジ（32バイト）を生成
$challengeBytes = random_bytes(32);

// 2. セッションにチャレンジを保存（後の検証フェーズ /api/verify-assertion で突合するため）
$_SESSION['webauthn_challenge'] = bin2hex($challengeBytes);

// 3. チャレンジを Base64URL 形式にエンコード
function base64url_encode(string $data): string {
	return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// ユーザー登録済みパスキーIDを取得する例（DB等から参照）
// ※ ユーザーIDが判明している場合は該当ユーザーの credential.id 一覧をセット
$allowCredentials = [];
/* 
// 例: DBから取得した登録済みCredential IDがある場合
$allowCredentials[] = [
	'type' => 'public-key',
	'id' => base64url_encode($userCredentialIdFromDb),
	'transports' => ['internal', 'hybrid']
];
*/

// 4. WebAuthnPublicKeyCredentialRequestOptions のレスポンスデータを構造化
$options = [
	// Base64URLエンコードしたチャレンジ文字列（JS側でUint8Arrayに変換）
	'challenge' => base64url_encode($challengeBytes),

	// タイムアウト時間（ミリ秒）
	'timeout' => 60000,

	// 認証要求を実行するドメイン（必要に応じて環境に合わせて変更）
	'rpId' => $_SERVER['HTTP_HOST'],

	// 生体認証等のユーザー検証要求 ("preferred" | "required" | "discouraged")
	'userVerification' => 'preferred',

	// 登録済みパスキーのフィルタリング（指定がなければ空配列で全パスキー対象）
	'allowCredentials' => $allowCredentials
];

// JSON形式でレスポンス返却
echo json_encode($options, JSON_UNESCAPED_SLASHES);
