<?php
/**
 * Guarda de autenticacao para os endpoints do app.
 *
 * Uso (depois de require '../Connect.php'):
 *   $seller = require_seller($PDO);   // encerra com 401 se o token for invalido
 *
 * O app envia o token emitido no login em:  Authorization: Bearer <token>
 * (tambem aceita header X-Api-Token).
 */

/**
 * @param PDO $PDO
 * @return array dados do seller autenticado (sem senha)
 */
function require_seller(PDO $PDO): array
{
    $token = bearer_token();
    if (!$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Token ausente.']);
        exit;
    }

    // Sessao nova (access_token com validade). Junta com sellers para os dados.
    $stmt = $PDO->prepare(
        "SELECT s.id, s.first_name, s.last_name, s.email, s.omie_codigo, s.status
         FROM seller_sessions ss
         INNER JOIN sellers s ON s.id = ss.id_seller
         WHERE ss.access_token = :t AND ss.access_expires > NOW()
         LIMIT 1"
    );
    $stmt->execute([':t' => $token]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fallback para o token legado (sellers.api_token) durante a transicao.
    if (!$seller) {
        $legacy = $PDO->prepare("SELECT id, first_name, last_name, email, omie_codigo, status FROM sellers WHERE api_token = :t LIMIT 1");
        $legacy->execute([':t' => $token]);
        $seller = $legacy->fetch(PDO::FETCH_ASSOC);
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
 * @return string|null
 */
function bearer_token(): ?string
{
    $headers = [];
    if (function_exists('getallheaders')) {
        foreach (getallheaders() as $k => $v) {
            $headers[strtolower($k)] = $v;
        }
    }
    // fallback via $_SERVER (o servidor embutido nem sempre expoe getallheaders)
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers['authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
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
