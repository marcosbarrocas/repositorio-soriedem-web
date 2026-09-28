<?php
/**
 * Login do vendedor (app). Valida email/senha na tabela `sellers` e, em caso de
 * sucesso, emite um token de sessao (salvo em sellers.api_token) para o app usar
 * no header Authorization das proximas chamadas.
 *
 * POST { "email": "...", "password": "..." }
 * Retorna { password_verify: bool, token?: string, seller?: {...sem senha} }.
 */
require_once('../Connect.php');

$postjson = json_decode(file_get_contents("php://input"), true);
if (!$postjson) {
    http_response_code(400);
    echo json_encode(['password_verify' => false, 'error' => 'Corpo JSON obrigatorio.']);
    return;
}

$data = filter_var_array($postjson, FILTER_SANITIZE_STRIPPED);

$query = $PDO->prepare("SELECT * FROM sellers WHERE email = :email");
$query->bindParam(':email', $data['email'], PDO::PARAM_STR);
$query->execute();

$json = ['password_verify' => false];

if ($query->rowCount() > 0) {
    $seller = $query->fetch(PDO::FETCH_ASSOC);
    $verified = password_verify($data['password'] ?? '', $seller['password']);
    $json['password_verify'] = $verified;

    if ($verified && isset($seller['status']) && (int) $seller['status'] === 0) {
        $json['password_verify'] = false;
        $json['error'] = 'Acesso inativo.';
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        return;
    }

    if ($verified) {
        $token = bin2hex(random_bytes(24));
        $refresh = bin2hex(random_bytes(24));

        // Sessao com access (7 dias) + refresh (30 dias). Cada login cria uma
        // sessao propria, entao varios dispositivos convivem sem se derrubar.
        $sess = $PDO->prepare(
            "INSERT INTO seller_sessions (id_seller, access_token, refresh_token, access_expires, refresh_expires)
             VALUES (:id, :a, :r, DATE_ADD(NOW(), INTERVAL 7 DAY), DATE_ADD(NOW(), INTERVAL 30 DAY))"
        );
        $sess->execute([':id' => $seller['id'], ':a' => $token, ':r' => $refresh]);

        // Compat com o token legado (Guard aceita ambos).
        $upd = $PDO->prepare("UPDATE sellers SET api_token = :t WHERE id = :id");
        $upd->execute([':t' => $token, ':id' => $seller['id']]);

        unset($seller['password'], $seller['api_token']);
        $json['token'] = $token;
        $json['refresh_token'] = $refresh;
        $json['seller'] = $seller;
    }
}

echo json_encode($json, JSON_UNESCAPED_UNICODE);
