<?php
/**
 * Guarda de autenticacao para os endpoints do app.
 *
 * Uso (depois de require '../Connect.php'):
 *   $seller = require_seller($PDO);   // encerra com 401 se o token for invalido
 *
 * O app envia o token emitido no login em:  Authorization: Bearer <token>
 * (tambem aceita header X-Api-Token).
 *
 * Nao depende da coluna sellers.status (ela existe no banco local e pode
 * nao existir em producao). Se o status vier no SELECT *, 0 ainda bloqueia.
 */

/**
 * Resolve o vendedor autenticado pelo access token da sessao (ou api_token legado).
 *
 * @param PDO $PDO Conexao ja aberta por Connect.php
 * @return array Dados do seller (id, nome, email, omie_codigo). Encerra com HTTP 401 se o token for invalido.
 */
function require_seller(PDO $PDO): array
{
    $token = bearer_token();
    if (!$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Token ausente.']);
        exit;
    }

    $seller = null;
    try {
        $stmt = $PDO->prepare(
            "SELECT s.id, s.first_name, s.last_name, s.email, s.omie_codigo
             FROM seller_sessions ss
             INNER JOIN sellers s ON s.id = ss.id_seller
             WHERE ss.access_token = :t AND ss.access_expires > NOW()
             LIMIT 1"
        );
        if ($stmt) {
            $stmt->execute([':t' => $token]);
            $seller = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        if (!$seller) {
            $legacy = $PDO->prepare(
                "SELECT id, first_name, last_name, email, omie_codigo
                 FROM sellers WHERE api_token = :t LIMIT 1"
            );
            if ($legacy) {
                $legacy->execute([':t' => $token]);
                $seller = $legacy->fetch(PDO::FETCH_ASSOC) ?: null;
            }
        }
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Falha ao validar a sessao.']);
        exit;
    }

    if (!$seller || (isset($seller['status']) && (int) $seller['status'] === 0)) {
        http_response_code(401);
        echo json_encode(['error' => 'Token invalido.']);
        exit;
    }

    return $seller;
}

/**
 * Extrai o token do header Authorization: Bearer <token> ou X-Api-Token.
 *
 * @return string|null Token limpo, ou null se nenhum header de autenticacao veio na requisicao
 */
function bearer_token(): ?string
{
    $headers = [];
    if (function_exists('getallheaders')) {
        foreach (getallheaders() as $k => $v) {
            $headers[strtolower($k)] = $v;
        }
    }
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers['authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
    }
    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $headers['authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    if (isset($_SERVER['HTTP_X_API_TOKEN'])) {
        $headers['x-api-token'] = $_SERVER['HTTP_X_API_TOKEN'];
    }

    if (!empty($headers['authorization']) && preg_match('/Bearer\s+(.+)/i', $headers['authorization'], $m)) {
        return trim($m[1]);
    }
    if (!empty($headers['x-api-token'])) {
        return trim($headers['x-api-token']);
    }
    return null;
}
