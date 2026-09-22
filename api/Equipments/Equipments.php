<?php

declare(strict_types=1);
require_once('../Connect.php');

header('Content-Type: application/json; charset=utf-8');

try {
    $stmt = $PDO->prepare('SELECT id, name FROM equipments ORDER BY name');
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal Server Error',
        // Em produção, remova a linha abaixo para não vazar detalhes:
        'detail' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
