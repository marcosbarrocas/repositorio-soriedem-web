<?php
/**
 * Detalhe de um pedido (tabela local `requests`), com itens e assinatura.
 *
 * GET api/Omie/PedidoDetalhe.php?request_number=123
 * Retorna: { request_number, status, created_at, client, signature, valorTotal,
 *            itens: [ { codigo, nome, photo, quantidade, valorUnitario, valorTotal } ] }
 */
require_once('../Connect.php');
require_once('../Guard.php');

$seller = require_seller($PDO);

$input = json_decode(file_get_contents("php://input"), true) ?: $_GET;
$requestNumber = trim((string)($input['request_number'] ?? ''));

if ($requestNumber === '') {
    http_response_code(400);
    echo json_encode(['error' => 'request_number obrigatorio.']);
    return;
}

try {
    // cabecalho (pega de qualquer linha do pedido)
    $head = $PDO->prepare(
        "SELECT r.request_number, r.status, r.created_at, r.signature, r.client
         FROM requests r WHERE r.request_number = :rn LIMIT 1"
    );
    $head->execute([':rn' => $requestNumber]);
    $header = $head->fetch(PDO::FETCH_ASSOC);

    if (!$header) {
        http_response_code(404);
        echo json_encode(['error' => 'Pedido nao encontrado.']);
        return;
    }

    // itens
    $itemsStmt = $PDO->prepare(
        "SELECT products.code AS codigo,
                products.title AS nome,
                products.photo AS photo,
                requests.current_amount AS quantidade,
                requests.item_value AS valorUnitario
         FROM requests
         INNER JOIN products ON products.id = requests.id_product
         WHERE requests.request_number = :rn"
    );
    $itemsStmt->execute([':rn' => $requestNumber]);
    $itens = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    $valorTotal = 0.0;
    foreach ($itens as &$it) {
        $it['valorTotal'] = (float)$it['valorUnitario'] * (int)$it['quantidade'];
        $valorTotal += $it['valorTotal'];
    }
    unset($it);

    echo json_encode([
        'request_number' => $header['request_number'],
        'status' => $header['status'],
        'created_at' => $header['created_at'],
        'client' => $header['client'],
        'signature' => $header['signature'],
        'valorTotal' => $valorTotal,
        'itens' => $itens,
    ], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar o pedido.']);
}
