<?php
/**
 * Grava uma visita e os arquivos de assinatura/anexos em disco.
 *
 * POST JSON:
 * {
 *   "id_seller": 6,
 *   "id_client": 226,
 *   "client": "Razao social",
 *   "details": "...",
 *   "representative": "...",
 *   "responsible": "...",
 *   "signature": "data:image/png;base64,...",
 *   "images": ["base64...", ...]
 * }
 *
 * As imagens sao gravadas em api/Visits/signs e api/Visits/images (caminho
 * absoluto via __DIR__), criando as pastas se ainda nao existirem.
 */
require_once('../Connect.php');

$postjson = json_decode(file_get_contents("php://input"), true);
if (!$postjson) {
    http_response_code(400);
    echo json_encode(['error' => 'Corpo JSON obrigatorio.']);
    return;
}

$data = filter_var_array($postjson, FILTER_SANITIZE_STRIPPED);
$signatureRaw = (string)($postjson['signature'] ?? '');
$imagesRaw = is_array($postjson['images'] ?? null) ? $postjson['images'] : [];

/**
 * Garante que a pasta de midia exista e seja gravavel.
 *
 * @param string $dir Caminho absoluto da pasta
 */
function visit_ensure_dir(string $dir): void
{
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}

/**
 * Converte um payload de imagem (data URL ou base64 puro) em bytes PNG.
 *
 * @param string $raw Conteudo enviado pelo app
 * @return string Bytes da imagem, ou string vazia se o decode falhar
 */
function visit_decode_image(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }
    $raw = preg_replace('#^data:image/\w+;base64,#i', '', $raw) ?? $raw;
    $bin = base64_decode($raw, true);
    return $bin === false ? '' : $bin;
}

$signsDir = __DIR__ . '/signs';
$imagesDir = __DIR__ . '/images';
visit_ensure_dir($signsDir);
visit_ensure_dir($imagesDir);

$file_name = uniqid() . '.png';
$signBytes = visit_decode_image($signatureRaw);
if ($signBytes !== '') {
    file_put_contents($signsDir . '/' . $file_name, $signBytes);
}

if (empty($data['id_client'])) {
    http_response_code(400);
    echo json_encode(['error' => 'id_client obrigatorio.']);
    return;
}

$query = $PDO->prepare("INSERT INTO visits (id_seller, id_client, client, details, signature, representative, responsible) VALUES (:id_seller, :id_client, :client, :details, :signature, :representative, :responsible)");
$query->bindParam(':id_seller', $data['id_seller'], PDO::PARAM_INT);
$query->bindParam(':id_client', $data['id_client'], PDO::PARAM_INT);
$query->bindParam(':client', $data['client'], PDO::PARAM_STR);
$query->bindParam(':details', $data['details'], PDO::PARAM_STR);
$query->bindParam(':signature', $file_name, PDO::PARAM_STR);
$representative = $data['representative'] ?? null;
$responsible = $data['responsible'] ?? null;
$query->bindParam(':representative', $representative, PDO::PARAM_STR);
$query->bindParam(':responsible', $responsible, PDO::PARAM_STR);
$query->execute();

$created = ($PDO->lastInsertId()) ? $PDO->lastInsertId() : false;

if ($created && !empty($imagesRaw)) {
    foreach ($imagesRaw as $img) {
        $imgBytes = visit_decode_image((string)$img);
        if ($imgBytes === '') {
            continue;
        }
        $img_name = uniqid() . '.png';
        file_put_contents($imagesDir . '/' . $img_name, $imgBytes);
        $img_query = $PDO->prepare("INSERT INTO visits_images (id_visit, image) VALUES (:id_visit, :image)");
        $img_query->bindParam(':id_visit', $created, PDO::PARAM_INT);
        $img_query->bindParam(':image', $img_name, PDO::PARAM_STR);
        $img_query->execute();
    }
}

echo json_encode($created);
