<?php
require_once ('../Connect.php');

$postjson = json_decode(file_get_contents("php://input"), true);
$get = $_GET;
if ($postjson || $get) {
    $data = null;

    if ($postjson) {
        $data = filter_var_array($postjson, FILTER_SANITIZE_STRIPPED);
    } else {
        $data = filter_var_array($get, FILTER_SANITIZE_STRIPPED);
    }

    $query = $PDO->prepare("SELECT clients.corporate_name, products.id, products.title, products.photo, clients_products.price FROM clients INNER JOIN clients_products ON clients_products.id_client = clients.id INNER JOIN products ON products.id = clients_products.id_product WHERE clients.id = :id_client;");
    $query->bindParam(':id_client', $data['id_client'], PDO::PARAM_INT);
    $query->execute();

    $result = null;
    if ($query->rowCount() > 0) {
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
    }
    echo json_encode($result);
}