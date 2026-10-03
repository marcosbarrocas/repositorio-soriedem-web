<?php
/**
 * Detalhe de um pedido (tabela local `requests`), com itens, assinatura e
 * dados do cliente (fantasia, razão social, CNPJ e endereço).
 *
 * GET api/Omie/PedidoDetalhe.php?request_number=123
 * Retorna: { request_number, status, created_at, client, corporate_name,
 *            trade_name, cnpj, address, number, district, city, state,
 *            signature, valorTotal,
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
    // cabecalho (pega de qualquer linha do pedido) + cadastro do cliente
    $head = $PDO->prepare(
        "SELECT r.request_number, r.status, r.created_at, r.signature, r.client,
                c.corporate_name, c.contact_name AS trade_name, c.cnpj,
                c.address, c.number, c.district, c.city, c.state
         FROM requests r
         LEFT JOIN clients c ON c.id = r.id_client
         WHERE r.request_number = :rn
         LIMIT 1"
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
        'corporate_name' => $header['corporate_name'] ?? $header['client'],
        'trade_name' => $header['trade_name'] ?? null,
        'cnpj' => $header['cnpj'] ?? null,
        'address' => $header['address'] ?? null,
        'number' => $header['number'] ?? null,
        'district' => $header['district'] ?? null,
        'city' => $header['city'] ?? null,
        'state' => $header['state'] ?? null,
        'signature' => $header['signature'],
        'valorTotal' => $valorTotal,
        'itens' => $itens,
    ], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar o pedido.']);
}
