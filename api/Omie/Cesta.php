<?php
/**
 * Cesta de produtos de um cliente (associacoes definidas no painel admin).
 *
 * GET  api/Omie/Cesta.php?id_client=123
 * POST { "id_client": 123 }
 *
 * Retorna a lista de produtos da cesta com o valor especifico do cliente
 * (clients_products.price) e a flag required (1 exige quantidade maior
 * que zero no pedido quando o estoque do cliente estiver zerado).
 * Cada item traz `omie_codigo` (codigo_produto numerico) para o IncluirPedido.
 */
require_once('../Connect.php');
require_once('../Guard.php');

$seller = require_seller($PDO);

$postjson = json_decode(file_get_contents("php://input"), true);
$input = $postjson ?: $_GET;

$idClient = (int)($input['id_client'] ?? 0);
if ($idClient <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'id_client obrigatorio.']);
    return;
}

try {
    $sql = "SELECT products.id,
                   products.code,
                   products.omie_codigo,
                   products.title,
                   products.photo,
                   products.stock,
                   clients_products.price,
                   clients_products.required
            FROM clients_products
            INNER JOIN products ON products.id = clients_products.id_product
            WHERE clients_products.id_client = :id_client
            ORDER BY products.title ASC";
    $stmt = $PDO->prepare($sql);
    $stmt->bindValue(':id_client', $idClient, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar a cesta.']);
}
