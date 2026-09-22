<?php
require_once('../Connect.php');

$postjson = json_decode(file_get_contents("php://input"), true);
if ($postjson) {
    $data = filter_var_array($postjson, FILTER_SANITIZE_STRIPPED);

    $query = $PDO->prepare("INSERT INTO requests_products (id_request, id_product, amount) VALUES (:id_request, :id_product, :amount)");
    $query->bindParam(':id_request', $data['id_request'], PDO::PARAM_INT);
    $query->bindParam(':id_product', $data['id_product'], PDO::PARAM_INT);
    $query->bindParam(':amount', $data['amount'], PDO::PARAM_STR);
    $query->execute();

    $created = ($PDO->lastInsertId()) ? $PDO->lastInsertId() : false;

    echo json_encode($created);
}
