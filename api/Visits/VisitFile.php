<?php
/**
 * Entrega um arquivo de assinatura ou anexo de visita.
 *
 * GET api/Visits/VisitFile.php?type=sign&file=abc.png
 * GET api/Visits/VisitFile.php?type=image&file=abc.png
 *
 * type: "sign" (pasta signs) ou "image" (pasta images)
 * file: apenas o nome do arquivo, sem caminho
 */
require_once('../Connect.php');

$type = strtolower(trim((string)($_GET['type'] ?? '')));
$file = basename((string)($_GET['file'] ?? ''));

if ($file === '' || !preg_match('/^[A-Za-z0-9._-]+\.(png|jpe?g|webp)$/i', $file)) {
    http_response_code(400);
    echo json_encode(['error' => 'Arquivo invalido.']);
    return;
}

$dir = $type === 'sign' ? __DIR__ . '/signs' : ($type === 'image' ? __DIR__ . '/images' : '');
if ($dir === '') {
    http_response_code(400);
    echo json_encode(['error' => 'type deve ser sign ou image.']);
    return;
}

$path = $dir . DIRECTORY_SEPARATOR . $file;
if (!is_file($path)) {
    http_response_code(404);
    echo json_encode(['error' => 'Arquivo nao encontrado.']);
    return;
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mime = [
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'webp' => 'image/webp',
][$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=86400');
readfile($path);
