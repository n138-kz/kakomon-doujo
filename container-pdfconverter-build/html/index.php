<?php
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
if (file_exists(__DIR__ . '/func.php')) {
    require_once __DIR__ . '/func.php';
}

if(!isset($_SERVER['HTTP_USER_AGENT']) || !isset($_SERVER['REMOTE_ADDR'])) {
    http_response_code(400);
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode([
        'code' => 400,
        'message' => 'Bad Request(400): Missing required headers.',
    ]));
}
error_log(__LINE__);
error_log($_SERVER['REQUEST_METHOD']);
if('POST' !== $_SERVER['REQUEST_METHOD']) {
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html>';
    echo PHP_EOL.'<html lang="ja">';
    echo PHP_EOL.'<head>';
    echo PHP_EOL.'    <meta charset="utf-8" />';
    echo PHP_EOL.'    <title>n138-kz.github.io</title>';
    echo PHP_EOL.'</head>';
    echo PHP_EOL.'<body>';
    echo PHP_EOL.'    <div class="container">';
    echo PHP_EOL.'        <div class="row">';
    echo PHP_EOL.'            <div class="block-center">';
    echo PHP_EOL.'                <h2 class="text-center">PDF to Image</h2>';
    echo PHP_EOL.'                <form action="./index.php" method="POST" enctype="multipart/form-data">';
    echo PHP_EOL.'                    <input type="file" name="file" required accept="application/pdf">';
    echo PHP_EOL.'                    <input type="submit" value="変換">';
    echo PHP_EOL.'                </form>';
    echo PHP_EOL.'            </div>';
    echo PHP_EOL.'        </div>';
    echo PHP_EOL.'    </div>';
    echo PHP_EOL.'</body>';
    echo PHP_EOL.'</html>';
    exit;
}
error_log(__LINE__);

if(! isset($_POST) || ! isset($_FILES)){
    http_response_code(400);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'code'=> 400,
        'message' => 'Bad Request(400): Mismatch a request type.',
    ]);
    exit(1);
}

$uploadkey = 'file';
$workdir = sys_get_temp_dir() . '/' . bin2hex(random_bytes(32));
$workdir = sys_get_temp_dir() . '/' . str_replace('php', '', basename($_FILES[$uploadkey]['tmp_name']));
$splitdir = "{$workdir}/split";
$outputdir = "{$workdir}/png";

if (!mkdir($splitdir, 0700, true) || !mkdir($outputdir, 0700, true)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'code' => 500,
        'message' => 'Internal Server Error(500): Unable create the working directories',
    ]);
    exit(1);
}

if(! isset($_FILES[$uploadkey])){
    header('Content-Type: application/json; charset=UTF-8');
    http_response_code(400);
    echo json_encode([
        'code'=> 400,
        'message' => 'Bad Request(400): Empty Request.',
    ]);
    exit(1);
}

if(! is_uploaded_file($_FILES[$uploadkey]['tmp_name'])){
    header('Content-Type: application/json; charset=UTF-8');
    http_response_code(400);
    echo json_encode([
        'code'=> 400,
        'message' => 'Bad Request(400): File has not uploaded.',
    ]);
    exit(1);
}

$uploadfile = $_FILES[$uploadkey];
$uploadfile['error'] = ['code'=>$uploadfile['error'],'detail'=>'',];
switch($uploadfile['error']['code']){
    case UPLOAD_ERR_OK:
    $uploadfile['error']['detail']='UPLOAD_ERR_OK';break;
    case UPLOAD_ERR_INI_SIZE:
    $uploadfile['error']['detail']='UPLOAD_ERR_INI_SIZE';break;
    case UPLOAD_ERR_FORM_SIZE:
    $uploadfile['error']['detail']='UPLOAD_ERR_FORM_SIZE';break;
    case UPLOAD_ERR_PARTIAL:
    $uploadfile['error']['detail']='UPLOAD_ERR_PARTIAL';break;
    case UPLOAD_ERR_NO_FILE:
    $uploadfile['error']['detail']='UPLOAD_ERR_NO_FILE';break;
    case UPLOAD_ERR_NO_TMP_DIR:
    $uploadfile['error']['detail']='UPLOAD_ERR_NO_TMP_DIR';break;
    case UPLOAD_ERR_CANT_WRITE:
    $uploadfile['error']['detail']='UPLOAD_ERR_CANT_WRITE';break;
    case UPLOAD_ERR_EXTENSION:
    $uploadfile['error']['detail']='UPLOAD_ERR_EXTENSION';break;
    default: break;
}

if($uploadfile['error']['code']!==UPLOAD_ERR_OK){
    http_response_code(400);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'code'=> 400,
        'message' => 'Bad Request(400): Upload Fail: ' . $uploadfile['error']['detail'],
    ]);
    exit(1);
}

if($uploadfile['type']!=='application/pdf'){
    http_response_code(400);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'code'=> 400,
        'message' => 'Bad Request(400): Upload file is not pdf file.',
    ]);
    exit(1);
}

try {
    set_time_limit(300);

    // 2. qpdf decrypt & 3. pdf split (1ページずつに分割)
    $cmd = sprintf(
        'qpdf --decrypt --split-pages %s %s/page_%%03d.pdf 2>&1',
        escapeshellarg($uploadfile['tmp_name']),
        escapeshellarg($splitdir)
    );
    exec($cmd, $out, $ret);
    if ($ret !== 0 && $ret !== 3) {
        throw new \RuntimeException('PDFの復号・分割に失敗しました: ' . implode("\n", $out));
    }

    $splitPages = glob("{$splitdir}/page_*.pdf");
    if (empty($splitPages)) {
        throw new \RuntimeException('分割されたPDFページが見つかりません');
    }
    sort($splitPages, SORT_STRING);

    // 4. 1ページずつpng化（1ページごとにリソースを完全解放）
    foreach ($splitPages as $index => $pagePdf) {
        $imagick = new \Imagick();
        $imagick->setResolution(96, 96);
        $imagick->readImage($pagePdf);
        $imagick->setImageBackgroundColor('#ffffff');
        $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);

        $flattened = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        $flattened->setImageFormat('png');
        $flattened->setOption('png:compression-level', '1');
        $flattened->writeImage(sprintf('%s/_-%03d.png', $outputdir, $index + 1));

        $flattened->destroy();
        $imagick->destroy();
        @unlink($pagePdf);
    }
} catch (\ImagickException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'code'=> 500,
        'message' => "Internal Server Error(500): {$e->getMessage()}",
    ]);
    exit(1);
} catch(\Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'code'=> 500,
        'message' => "Internal Server Error(500): {$e->getMessage()}",
    ]);
    exit(1);
}

try {
    // 一時ファイルを生成
    $tmpFile = tempnam(sys_get_temp_dir(), 'zip_');
    
    $zip = new \ZipArchive();
    if ($zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
        http_response_code(500);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'code' => 500,
            'message' => "Internal Server Error(500): Failed to open zip archive",
        ]);
        exit(1);
    }

    $files = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($outputdir, \RecursiveDirectoryIterator::SKIP_DOTS),
        \RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($outputdir) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }
    $zip->close();

    // ZIP作成成功後にヘッダーを出力（エラー時はJSONを出力できるようにするため）
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="'.basename(dirname($outputdir)).'.zip"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: no-cache');
    header('Content-Length: ' . filesize($tmpFile));

    // ファイルを出力して削除
    readfile($tmpFile);
    unlink($tmpFile);
    exit;

} catch (\Exception $e) {
    if (isset($tmpFile) && file_exists($tmpFile)) {
        @unlink($tmpFile);
    }
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'code' => 500,
        'message' => "Internal Server Error(500): {$e->getMessage()}",
    ]);
    exit(1);
}

try{
    $files = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($outputdir, \FilesystemIterator::SKIP_DOTS),
        \RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach($files as $file) {
        if($file->isDir() === true) {
            rmdir($file->getPathname());
        } else {
            unlink($file->getPathname());
        }
    }
    if(! rmdir($outputdir)){
        http_response_code(500);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'code'=> 500,
            'message' => 'Internal Server Error(500): Unable cleanup the output directory',
        ]);
        exit(1);
    }
}catch(\Exception $e){
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'code'=> 500,
        'message' => "Internal Server Error(500): {$e->getMessage()}",
    ]);
    exit(1);
}

if( is_array($uploadfile)){
    echo json_encode([
        'uploadfile'=>$uploadfile,
        'outputdir'=>$outputdir,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}else{
    header('Content-Type: text/plain; charset=UTF-8');
    var_dump($uploadfile);
}
