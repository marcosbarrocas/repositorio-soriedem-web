<?php
require_once ('../Connect.php');

$postjson = json_decode(file_get_contents("php://input"), true);
if ($postjson) {
    $data = filter_var_array($postjson, FILTER_SANITIZE_STRIPPED);

    $query = $PDO->prepare("SELECT requests_products.id, products.title, requests_products.amount FROM requests_products INNER JOIN products ON requests_products.id_product = products.id WHERE requests_products.id_request = :id_request");
    $query->bindParam(':id_request', $data['id_request'], PDO::PARAM_INT);
    $query->execute();

    $data = null;
    if ($query->rowCount() > 0) {
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
    }
    echo json_encode($data);
}