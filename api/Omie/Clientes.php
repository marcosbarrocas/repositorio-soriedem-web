<?php
/**
 * Lista de clientes para o app (espelho local dos clientes da Omie).
 *
 * GET  api/Omie/Clientes.php?page=1&limit=50&search=texto
 * POST { "page": 1, "limit": 50, "search": "texto" }
 *
 * Retorna: { data: [ ...clientes ], pagination: { page, limit, total, pages } }
 */
require_once('../Connect.php');
require_once('../Guard.php');

$seller = require_seller($PDO);

$postjson = json_decode(file_get_contents("php://input"), true);
$input = $postjson ?: $_GET;

$page = max(1, (int)($input['page'] ?? 1));
$limit = min(200, max(1, (int)($input['limit'] ?? 50)));
$offset = ($page - 1) * $limit;
$search = trim((string)($input['search'] ?? ''));

$where = "code IS NOT NULL";
$params = [];

// Cada vendedor vê apenas os SEUS clientes (associação vinda da Omie:
// recomendacoes.codigo_vendedor → clients.omie_codigo_vendedor).
if (!empty($seller['omie_codigo'])) {
    $where .= " AND omie_codigo_vendedor = :vendedor";
    $params[':vendedor'] = (int)$seller['omie_codigo'];
}

if ($search !== '') {
    $where .= " AND (corporate_name LIKE :s OR cnpj LIKE :s OR city LIKE :s)";
    $params[':s'] = "%{$search}%";
}

try {
    $countStmt = $PDO->prepare("SELECT COUNT(*) FROM clients WHERE {$where}");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $sql = "SELECT id, code, corporate_name, cnpj, address, number, district, city, state, phone, email, contact_name
            FROM clients WHERE {$where} ORDER BY corporate_name ASC LIMIT :limit OFFSET :offset";
    $stmt = $PDO->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'data' => $data,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => (int)ceil($total / $limit),
        ],
    ], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao listar clientes.']);
}
