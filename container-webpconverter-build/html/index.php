<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Please use POST.']);
    exit;
}

// ファイルがアップロードされているか確認
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Image file is required in the "image" field.']);
    exit;
}

$tmpPath = $_FILES['image']['tmp_name'];

try {
    // Imagick を使用して画像を読み込み（HEIC、JPEG、PNGなどに対応）
    $image = new Imagick();
    $image->readImage($tmpPath);

    // アニメーション画像の場合は先頭フレームに絞る
    if ($image->getNumberImages() > 1) {
        $image = $image->coalesceImages()->getImage();
    }

    // 出力フォーマットを WebP に指定
    $image->setImageFormat('webp');

    // 画質の指定（オプション: 0〜100、デフォルト80）
    $quality = isset($_POST['quality']) ? (int)$_POST['quality'] : 80;
    $quality = max(0, min(100, $quality));
    $image->setImageCompressionQuality($quality);

    // メタデータを削除して軽量化
    $image->stripImage();

    // 出力ヘッダーを設定して返却
    header('Content-Type: image/webp');
    header('Content-Disposition: inline; filename="converted.webp"');

    echo $image->getImageBlob();

    $image->clear();
    $image->destroy();
    exit;

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Failed to process image. Invalid or unsupported format.']);
    exit;
}