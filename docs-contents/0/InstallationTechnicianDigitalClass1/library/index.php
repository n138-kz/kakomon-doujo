<?php namespace n138;
ini_set('display_errors', 1);
ini_set('error_log', 'php://stderr');
ini_set('log_errors', 1);
session_name('sessid');
session_start();


use \n138\resource;
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
