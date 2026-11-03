<?php
$requestForm = $_GET ?? [];
$requestForm['limit'] = $requestForm['limit'] ?? 1;
$requestForm['limit'] = is_numeric($requestForm['limit']) ? $requestForm['limit'] : 1;
$testdata = [];
function generate_uuidv4() {
    // 16バイトのランダムデータを生成
    $data = random_bytes(16);

    // バージョン4（ランダムUUID）に設定するためのビット操作
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // バリアント（RFC 4122）に設定するためのビット操作
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // 32文字の16進数文字列に変換し、ハイフンで区切る
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
for ($i=0;$i<$requestForm['limit'];$i++) {
	array_push($testdata, [
		"id" => generate_uuidv4(),
		"mondai" => [
			"text" => "次の各文章の内に、それぞれの解答群の中から、最も適したものを選び、その番号を記せ。\n次の文章のうち、正しいものは、(　)である。\n".base64_encode(random_bytes(64))."\n".base64_encode(random_bytes(64))."\n".base64_encode(random_bytes(64))."\n".base64_encode(random_bytes(64))."\n".base64_encode(random_bytes(64)),
			"image" => [
				[
					"url" => "",
					"width" => null,
					"height" => null,
				],
			],
		],
		"sentakushi" => [
			["answer" => false,"text" => "".hash('md5', base64_encode(random_bytes(4)))],
			["answer" => false,"text" => "".hash('md5', base64_encode(random_bytes(4)))],
			["answer" => true ,"text" => "".hash('md5', base64_encode(random_bytes(4)))],
			["answer" => false,"text" => "".hash('md5', base64_encode(random_bytes(4)))],
		],
		"kakosyutsudai" => ["令和8年度第1回第1問(1)"],
		"category" => 1,
		"category-description" => "テストデータ",
		"kaisetsu" => "",
	]);
}
echo json_encode($testdata);
