<?php
/**
 * Renova o token de acesso do vendedor a partir do refresh token.
 *
 * POST { "refresh_token": "..." }
 * Retorna { token: <novo access>, refresh_token: <mesmo>, seller: {...} }
 * ou 401 se o refresh for invalido/expirado (app deve mandar pro login).
 */
require_once('../Connect.php');

$postjson = json_decode(file_get_contents("php://input"), true) ?: [];
$refresh = trim((string)($postjson['refresh_token'] ?? ''));

if ($refresh === '') {
    http_response_code(400);
    echo json_encode(['error' => 'refresh_token obrigatorio.']);
    return;
}

$stmt = $PDO->prepare(
    "SELECT ss.id, ss.id_seller, s.first_name, s.last_name, s.email, s.omie_codigo
     FROM seller_sessions ss
     INNER JOIN sellers s ON s.id = ss.id_seller
     WHERE ss.refresh_token = :r AND ss.refresh_expires > NOW()
     LIMIT 1"
);
$stmt->execute([':r' => $refresh]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    http_response_code(401);
    echo json_encode(['error' => 'Refresh invalido ou expirado.']);
    return;
}

$newAccess = bin2hex(random_bytes(24));
$upd = $PDO->prepare(
    "UPDATE seller_sessions
     SET access_token = :a, access_expires = DATE_ADD(NOW(), INTERVAL 7 DAY)
     WHERE id = :id"
);
$upd->execute([':a' => $newAccess, ':id' => $session['id']]);

// mantem o token legado em sincronia
$PDO->prepare("UPDATE sellers SET api_token = :t WHERE id = :id")
    ->execute([':t' => $newAccess, ':id' => $session['id_seller']]);

echo json_encode([
    'token' => $newAccess,
    'refresh_token' => $refresh,
    'seller' => [
        'id' => $session['id_seller'],
        'first_name' => $session['first_name'],
        'last_name' => $session['last_name'],
        'email' => $session['email'],
        'omie_codigo' => $session['omie_codigo'],
    ],
], JSON_UNESCAPED_UNICODE);
