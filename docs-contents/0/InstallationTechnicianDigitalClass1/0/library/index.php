<?php namespace n138;
ini_set('display_errors', 1);
ini_set('error_log', 'php://stderr');
ini_set('log_errors', 1);
session_name('sessid');
session_start();

require_once('/app/define.php');
use function imagecreatefromstring;
use function imagepalettetotruecolor;
use function imagealphablending;
use function imagesavealpha;
use function imagewebp;
use function imagedestroy;
use function ob_start;
use function ob_get_clean;

use \n138\resource;
class resource extends local {
	private $conn_database = null;
	public function __construct () {
		parent::init();
		$this->config = parent::config('get');
		$this->conn_database = parent::connectDB($this->config['internal']['database']);
	}
	public function mondais($method='get', $options=[]) {
		if (false){
		} elseif ($method == 'get') {
			$template_mondai = [];
			{
				$template_mondai['category'] = 0;
				$template_mondai['kaisetsu'] = [];
				$template_mondai['kaisetsu']['text'] = '';
				$template_mondai['kaisetsu']['image'] = [];
				$template_mondai['mondai']['text'] = '';
				$template_mondai['mondai']['image'] = [];
				$template_mondai['sentakushi'] = [];
				$template_mondai['sentakushi'][] = ['text'=>'', 'answer'=>false,];
				array_pop($template_mondai['sentakushi']);
				$template_mondai['kakosyutsudai'] = [];
				ksort($template_mondai);
			}
			try {
				if (!$this->conn_database->inTransaction()) { $this->conn_database->beginTransaction(); }
				{
					$sql = "SELECT * FROM InstallationTechnicianDigitalClass1_mondaiset ORDER BY openid ASC;";
					$stm = $this->conn_database->prepare($sql);
					$stm->execute();
					$res = $stm->fetchAll(\PDO::FETCH_ASSOC);
				}
				if ($this->conn_database->inTransaction()) { $this->conn_database->commit(); }
				for ($i=0; $i < count($res); $i++) {
					$res[$i]['mondai'] = json_decode($res[$i]['mondai'],1);
					ksort($res[0]['mondai']);
					$res[$i]['mondai'] = array_merge($template_mondai, $res[$i]['mondai']);
					foreach ($res[$i]['mondai'] as $k1 => $v1) {
						if (!is_array($v1)) continue;
						$res[$i]['mondai'][$k1] = array_merge($template_mondai[$k1], $res[$i]['mondai'][$k1]);
					}
				}
				return [
					'options' => $options,
					'content' => $res,
					'template' => $template_mondai,
				];
			} catch (\PDOException $e) {
				if ($this->conn_database->inTransaction()) { $this->conn_database->rollback(); }
				error_log('Error has occured on '.$e->getFile().'#'.$e->getLine());
				error_log('[Stack trace]'.PHP_EOL.'[0] '.$e->getMessage().PHP_EOL.'[1] '.$e->getTraceAsString());
			}
		} elseif ($method == 'getbyid') {
			$options['id'] = $options['id'] ?? null;
			$options['id'] = $options['id']!='' ? $options['id'] : null;
			if (is_null($options['id'])) { return; }

			try {
				if (!$this->conn_database->inTransaction()) { $this->conn_database->beginTransaction(); }
				{
					$sql = "SELECT * FROM InstallationTechnicianDigitalClass1_mondaiset WHERE id=?;";
					$stm = $this->conn_database->prepare($sql);
					$stm->execute([$options['id']]);
					$res = $stm->fetch(\PDO::FETCH_ASSOC);
				}
				if ($this->conn_database->inTransaction()) { $this->conn_database->commit(); }
				return [
					'options' => $options,
					'content' => $res,
					'summary' => $stm->rowCount(),
				];
			} catch (\PDOException $e) {
				if ($this->conn_database->inTransaction()) { $this->conn_database->rollback(); }
				error_log('Error has occured on '.$e->getFile().'#'.$e->getLine());
				error_log('[Stack trace]'.PHP_EOL.'[0] '.$e->getMessage().PHP_EOL.'[1] '.$e->getTraceAsString());
			}

		} elseif ($method == 'update') {
			$options['id'] = $options['id'] ?? null;
			$options['id'] = $options['id']!='' ? $options['id'] : null;
			if (is_null($options['id'])) { return; }

			if (self::mondais('getbyid',['id'=>$options['id']])['summary']!==1) {
				return;
			}
			try {
				if (!$this->conn_database->inTransaction()) { $this->conn_database->beginTransaction(); }
				{
					$sql = "UPDATE InstallationTechnicianDigitalClass1_mondaiset SET openid=:item WHERE id=:id;";
					$stm = $this->conn_database->prepare($sql);
					$stm->execute([
						'item'=>$options['openid'],
						'id'=>$options['id'],
					]);
					$sql = "UPDATE InstallationTechnicianDigitalClass1_mondaiset SET deleted=:item WHERE id=:id;";
					$stm = $this->conn_database->prepare($sql);
					$stm->execute([
						'item'=>(!empty($options['deleted']) ? 1 : 0),
						'id'=>$options['id'],
					]);
					$sql = "UPDATE InstallationTechnicianDigitalClass1_mondaiset SET mondai=:item WHERE id=:id;";
					$stm = $this->conn_database->prepare($sql);
					$stm->execute([
						'item'=>json_encode($options['mondai'],JSON_UNESCAPED_UNICODE),
						'id'=>$options['id'],
					]);
				}
				if ($this->conn_database->inTransaction()) { $this->conn_database->commit(); }
				return [
					'options' => $options,
					'content' => self::mondais('getbyid',['id'=>$options['id']])['content'],
					'summary' => self::mondais('getbyid',['id'=>$options['id']])['summary'],
				];
			} catch (\PDOException $e) {
				if ($this->conn_database->inTransaction()) { $this->conn_database->rollback(); }
				error_log('Error has occured on '.$e->getFile().'#'.$e->getLine());
				error_log('[Stack trace]'.PHP_EOL.'[0] '.$e->getMessage().PHP_EOL.'[1] '.$e->getTraceAsString());
			}
		} elseif ($method == 'count') {
			$proc = new resource_1();
			$mondais_category = $proc->mondais_category('InstallationTechnicianDigitalClass1', 'get');
			try {
				if (!$this->conn_database->inTransaction()) { $this->conn_database->beginTransaction(); }
				{
					$sql = 'SELECT * FROM vw_InstallationTechnicianDigitalClass1_mondaiset_count;';
					$stm = $this->conn_database->prepare($sql);
					$stm->execute();
					$res = $stm->fetch(\PDO::FETCH_ASSOC);
				}
				if ($this->conn_database->inTransaction()) { $this->conn_database->commit(); }
				return [
					'count' => [
						'all' => $res['count_all'],
						'category' => [
							$res['count_category_1'],
							$res['count_category_2'],
							$res['count_category_3'],
						],
					],
				];
			} catch (\PDOException $e) {
				if ($this->conn_database->inTransaction()) { $this->conn_database->rollback(); }
				error_log('Error has occured on '.$e->getFile().'#'.$e->getLine());
				error_log('[Stack trace]'.PHP_EOL.'[0] '.$e->getMessage().PHP_EOL.'[1] '.$e->getTraceAsString());
			}
		} else {
			error_log('Unknown params $1: '.$method);
		}
	}
	public function refreshS3Table($table_basename=null, $s3ObjectList=null) {
		if (is_null($table_basename)) {
			error_log("var \$table_basename is NULL");
			return;
			/* * class local に持っていったときは以下を残して、ここと上2行消す * */
			if (is_null($this->table_basename)) {
				error_log("var \$table_basename is NULL");
				return;
			}
			$table_basename = $this->table_basename;
		}
		if (is_null($s3ObjectList)) {
			error_log(__FILE__.'#'.__LINE__.': '.'s3ObjectList is NULL');
			return;
		}
		if (!is_array($s3ObjectList)) {
			error_log(__FILE__.'#'.__LINE__.': '.'s3ObjectList is not List(Array)');
			return;
		}
		try {
			if (!$this->conn_database->inTransaction()) { $this->conn_database->beginTransaction(); }
			if ($this->conn_database->inTransaction()) {
				$sql = "DELETE FROM {$table_basename}_s3storage_key";
				$stm = $this->conn_database->prepare($sql);
				$stm->execute();

				$sql = "INSERT INTO {$table_basename}_s3storage_key (key, bucket, objecturl, proxiedobjecturl, etag) VALUES (?,?,?,?,?)";
				$stm = $this->conn_database->prepare($sql);
				foreach ($s3ObjectList as $k1 => $v1) {
					$stm->execute([
						$v1['Key'],
						mb_strtolower($table_basename),
						$this->config['internal']['s3-object-storage']['endpoint'].'/'.mb_strtolower($table_basename).'/'.$v1['Key'],
						str_replace(
							$this->config['internal']['s3-object-storage']['endpoint']
							, parent::clientinf_get()['REQUEST_SCHEME'].'://'.parent::clientinf_get()['HTTP_HOST'].'/@lib/object/?q='
							, $this->config['internal']['s3-object-storage']['endpoint'].'/'.mb_strtolower($table_basename).'/'.$v1['Key']
						),
						trim($v1['ETag'], '"'),
					]);
				}
				$this->conn_database->commit();
			}
			return;
		} catch (\PDOException $e) {
			if ($this->conn_database->inTransaction()) { $this->conn_database->rollback(); }
			error_log('Error has occured on '.$e->getFile().'#'.$e->getLine());
			error_log('[Stack trace]'.PHP_EOL.'[0] '.$e->getMessage().PHP_EOL.'[1] '.$e->getTraceAsString());
		}
	}
}

$proc = new resource();
$proc1 = new resource_1();
header('Content-Type: text/plain');
$proc->checkAuthn();
$proc->update_expires_at();
$proc1->setDedault_basename('InstallationTechnicianDigitalClass1');
$role = $proc1->role();
foreach ($role['role_descr'] as $k => $v) {
	define('DB_ACCESS_LEVEL_FLAG_'.$v['description'], $v['id']);
	/* * ($role['user'] & DB_ACCESS_LEVEL_FLAG_SEARCH) !== 0 * */
	/* * ($role['user'] & DB_ACCESS_LEVEL_FLAG_READ) !== 0 * */
	/* * ($role['user'] & DB_ACCESS_LEVEL_FLAG_UPDATE) !== 0 * */
	/* * ($role['user'] & DB_ACCESS_LEVEL_FLAG_REGIST) !== 0 * */
	/* * ($role['user'] & DB_ACCESS_LEVEL_FLAG_DELETE) !== 0 * */
}
if (!(($role['user'] & DB_ACCESS_LEVEL_FLAG_UPDATE) !== 0)) {
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
if (false) {
} elseif ($_SERVER['REQUEST_METHOD']=='GET') {
	header('Content-Type: application/json');
	http_response_code(200);
	if (false) {
	} elseif (isset($_GET['byid'])) {
		$data = [
			'mondais' => $proc->mondais('get')['content'],
			'mondais_category' => $proc1->mondais_category()['content'],
			'mondais_summary' => $proc->mondais('count'),
			'mondais_template' => $proc->mondais('get')['template'],
			'index' => null,
		];
		for ($i=0;$i<count($data['mondais']);$i++) {
			if ($_GET['byid']===$data['mondais'][$i]['id']||$_GET['byid']===$data['mondais'][$i]['openid']) {
				$data['index'] = $i;
			}
		}
		echo json_encode([
			'status'=>http_response_code(),
			'user' => [
				'permission' => [
					'role' => $role['user'],
					'role_catalog' => $role['role_descr'],
					'minimum_accesslevel' => DB_ACCESS_LEVEL_FLAG_UPDATE,
					'access_approved' => (($role['user'] & DB_ACCESS_LEVEL_FLAG_UPDATE) !== 0),
				]
			],
			'data' => $data,
		]);
	} else {
		echo json_encode([
			'status'=>http_response_code(),
			'user' => [
				'permission' => [
					'role' => $role['user'],
					'role_catalog' => $role['role_descr'],
					'minimum_accesslevel' => DB_ACCESS_LEVEL_FLAG_UPDATE,
					'access_approved' => (($role['user'] & DB_ACCESS_LEVEL_FLAG_UPDATE) !== 0),
				]
			],
			'data' => [
				'mondais' => $proc->mondais('get')['content'],
				'mondais_category' => $proc1->mondais_category()['content'],
				'mondais_summary' => $proc->mondais('count'),
				'mondais_template' => $proc->mondais('get')['template'],
			]
		]);
	}
	exit;
} elseif ($_SERVER['REQUEST_METHOD']=='POST') {
	if (!(($role['user'] & DB_ACCESS_LEVEL_FLAG_COMBI_UPDATE) !== 0)) {
		http_response_code(403);
		header('Content-Type: application/json');
		$m = json_encode([
			'status' => http_response_code(),
			'user' => [
				'permission' => [
					'role' => $role['user'],
					'role_catalog' => $role['role_descr'],
					'minimum_accesslevel' => DB_ACCESS_LEVEL_FLAG_COMBI_UPDATE,
					'access_approved' => (($role['user'] & DB_ACCESS_LEVEL_FLAG_COMBI_UPDATE) !== 0),
				]
			],
			'error' => [
				'title' => 'Forbidden.',
				'message' => 'You are NOT having permission on this resource.',
			],
		]);
		error_log($m); die($m);
	}
	if (false) {
	} elseif (explode(';', $_SERVER['CONTENT_TYPE'])[0]==='application/json') {
		header('Content-Type: application/json; charset=UTF-8');
		$_POST = file_get_contents('php://input');
		$_POST = json_decode($_POST, true);
		$_REQUEST = array_merge($_REQUEST, $_POST);
		if (json_last_error() !== JSON_ERROR_NONE) {
			http_response_code(400);
			$m = json_encode([
				'status' => http_response_code(),
				'error' => [
					'title' => 'Invalid JSON.',
					'message' => 'Contains invalid JSON format.',
				],
			]);
			error_log($m); die($m);
		}

		if ($proc->mondais('getbyid', ['id' => $_POST['id']])['summary']===1) {
			error_log('uuid: '.$_POST['id']);
			error_log(__FILE__.'#'.__LINE__.PHP_EOL.json_encode($proc->mondais('update', $_POST)));
		}
	} elseif (explode(';', $_SERVER['CONTENT_TYPE'])[0]==='multipart/form-data') {
		error_log(__FILE__.'#'.__LINE__.PHP_EOL.json_encode($_POST, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
		error_log(__FILE__.'#'.__LINE__.PHP_EOL.json_encode($_FILES, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
		if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
			error_log(__FILE__.'#'.__LINE__.PHP_EOL.json_encode([
				'upload_max_filesize' => ini_get('upload_max_filesize'),
				'post_max_size'       => ini_get('post_max_size'),
			], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
			http_response_code(400);
			$m = json_encode([
				'status' => http_response_code(),
				'error' => [
					'title' => 'File upload error.',
					'message' => 'File has not uploaded: '.$proc1->fileUploadErrorCodeToMessage($_FILES['image']['error']).'',
				],
			]);
			error_log($m); die($m);
		}

		if (! is_uploaded_file($_FILES['image']['tmp_name'])) {
			http_response_code(400);
			$m = json_encode([
				'status' => http_response_code(),
				'error' => [
					'title' => 'File upload error.',
					'message' => 'File has not uploaded.',
				],
			]);
			error_log($m); die($m);
		}

		$mimeType = $proc1->getMIMEType($_FILES['image']['tmp_name']);
		if (strpos($mimeType, 'image/') !== 0) {
			http_response_code(400);
			$m = json_encode([
				'status' => http_response_code(),
				'error' => [
					'title' => 'File upload error.',
					'message' => 'File has image only accept: '.$mimeType,
				],
			]);
			error_log($m); die($m);
		}

		error_log(__FILE__.'#'.__LINE__.': '.'MIME-type: '.$mimeType);
		$mimeType = explode(';', $mimeType)[0];
		$allowedMimeTypes = [
			'image/jpeg'    => 'jpg',
			'image/pjpeg'   => 'jpg', // レガシー対応も含めて 'jpg' に統一
			'image/png'     => 'png',
			'image/x-png'   => 'png', // レガシー対応
			'image/gif'     => 'gif',
			'image/bmp'     => 'bmp',
			'image/webp'    => 'webp',
			'image/svg+xml' => 'svg',
		];
		if (!isset($allowedMimeTypes[$mimeType])) {
			http_response_code(400);
			$m = json_encode([
				'status' => http_response_code(),
				'error' => [
					'title' => 'File upload error.',
					'message' => 'File has invalid MIME type: '.$mimeType,
				],
			]);
			error_log($m); die($m);
		}

		switch ($allowedMimeTypes[$mimeType]) {
			case 'jpg':
				$image = imagecreatefromjpeg($_FILES['image']['tmp_name']);
				break;
			case 'png':
				$image = imagecreatefrompng($_FILES['image']['tmp_name']);
				// PNGの透過（アルファチャンネル）を維持する場合
				imagealphablending($image, true);
				imagesavealpha($image, true);
				break;
			case 'bmp':
				$image = imagecreatefrombmp($_FILES['image']['tmp_name']);
				break;
		}

		$s3Client = $proc->s3_connect('connect');
		$s3Bucket =  mb_strtolower('InstallationTechnicianDigitalClass1');
		$s3Key = hash_file('md5', $_FILES['image']['tmp_name']);
		if (!$s3Client->doesBucketExistV2($s3Bucket)) {
			$s3Client->createBucket(['Bucket' => $s3Bucket]);
			$s3Client->putBucketVersioning([
				'Bucket'     => $s3Bucket,
				'VersioningConfiguration' => [
					'Status' => 'Enabled',
				],
			]);
			$policy = json_encode([
				'Version' => '2012-10-17',
				'Statement' => [
					[
						'Effect'    => 'Allow',
						'Principal' => ['AWS' => ['*']],
						'Action'    => ['s3:GetObject'],
						'Resource'  => ["arn:aws:s3:::{$s3Bucket}/*"],
					],
				],
			], JSON_UNESCAPED_SLASHES);
			$s3Client->putBucketPolicy([
				'Bucket' => $s3Bucket,
				'Policy' => $policy,
			]);
		}

		$s3result = null;
		switch ($allowedMimeTypes[$mimeType]) {
			case 'jpg':
			case 'png':
			case 'bmp':
				imagepalettetotruecolor($image);
				ob_start();
				imagewebp($image, null, 80);
				$webpBinary = ob_get_clean();
				$mimeType = 'image/webp';

				$s3result = $s3Client->putObject([
					'Bucket'      => $s3Bucket,
					'Key'         => $s3Key,
					'Body'        => $webpBinary,
					'ContentType' => $mimeType,
				]);
				break;
			default:
				$sourcePath = $_FILES['image']['tmp_name'];
				$s3result = $s3Client->putObject([
					'Bucket'      => $s3Bucket,
					'Key'         => $s3Key,
					'SourceFile'  => $sourcePath,
					'ContentType' => $mimeType,
				]);
		}
		$s3ObjectURL = str_replace($proc->config['internal']['s3-object-storage']['endpoint'], "{$proc->clientinf_get()['REQUEST_SCHEME']}://{$proc->clientinf_get()['HTTP_HOST']}/@lib/object/?q=", $s3result->get('ObjectURL'));
		error_log('Upload Completed. URL: ' . $s3ObjectURL);
		error_log(__FILE__.'#'.__LINE__.PHP_EOL.json_encode(
			$s3Client->listObjectsV2([
				'Bucket' => $s3Bucket,
			])->get('Contents')
		, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
		$proc->refreshS3Table('InstallationTechnicianDigitalClass1', $s3Client->listObjectsV2([
			'Bucket' => $s3Bucket,
		])->get('Contents'));

		{
			$m = json_encode([
				'status' => http_response_code(),
				'data' => [
					'url' => $s3ObjectURL,
				],
			]);
			error_log($m); die($m);
		}
	} else {
		http_response_code(400);
		$m = json_encode([
			'status' => http_response_code(),
			'error' => [
				'title' => 'Unknown Content-Type',
				'message' => 'Unknown Content-Type: '.explode(';', $_SERVER['CONTENT_TYPE'])[0],
			],
		]);
		error_log($m); die($m);
	}

	http_response_code(204);
}
