<?php
/**
 * Lista de pedidos de um cliente (a partir da tabela local `requests`, que guarda
 * os pedidos criados pelo app — um registro por item, agrupados por request_number).
 *
 * GET  api/Omie/PedidosCliente.php?id_client=123
 * Retorna: [ { request_number, status, created_at, valorTotal }, ... ]
 */
require_once('../Connect.php');
require_once('../Guard.php');

$seller = require_seller($PDO);

$input = json_decode(file_get_contents("php://input"), true) ?: $_GET;
$idClient = (int)($input['id_client'] ?? 0);

if ($idClient <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'id_client obrigatorio.']);
    return;
}

try {
    $sql = "SELECT request_number,
                   MAX(status) AS status,
                   MAX(created_at) AS created_at,
                   SUM(item_value * current_amount) AS valorTotal
            FROM requests
            WHERE id_client = :id_client AND request_number IS NOT NULL
            GROUP BY request_number
            ORDER BY MAX(id) DESC";
    $stmt = $PDO->prepare($sql);
    $stmt->bindValue(':id_client', $idClient, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao listar pedidos do cliente.']);
}
