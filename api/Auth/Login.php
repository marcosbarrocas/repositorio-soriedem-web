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

    if ($verified) {
        $token = bin2hex(random_bytes(24));
        $upd = $PDO->prepare("UPDATE sellers SET api_token = :t WHERE id = :id");
        $upd->execute([':t' => $token, ':id' => $seller['id']]);

        unset($seller['password'], $seller['api_token']);
        $json['token'] = $token;
        $json['seller'] = $seller;
    }
}

echo json_encode($json, JSON_UNESCAPED_UNICODE);
