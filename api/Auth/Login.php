<?php
require_once ('../Connect.php');

$postjson = json_decode(file_get_contents("php://input"), true);
if ($postjson) {
    $data = filter_var_array($postjson, FILTER_SANITIZE_STRIPPED);

    $query = $PDO->prepare("SELECT * FROM sellers WHERE email = :email");
    $query->bindParam(':email', $data['email'], PDO::PARAM_STR);
    $query->execute();

    $seller = null;
    if ($query->rowCount() > 0) {
        $seller = $query->fetch(PDO::FETCH_ASSOC);
        $json['password_verify'] = password_verify($data['password'], $seller['password']);
    } else {
        $seller = 'No result!';
    }

    $json['seller'] = $seller;

    echo json_encode($json);
}