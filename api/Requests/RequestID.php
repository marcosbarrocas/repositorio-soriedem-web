<?php
require_once('../Connect.php');

$postjson = json_decode(file_get_contents("php://input"), true);
$get = $_GET;
if ($postjson || $get) {
    $data = null;

    if ($postjson) {
        $data = filter_var_array($postjson, FILTER_SANITIZE_STRIPPED);
    } else {
        $data = filter_var_array($get, FILTER_SANITIZE_STRIPPED);
    }

    $query1 = $PDO->prepare("SELECT * FROM requests WHERE requests.request_number = :id_request");
    $query1->bindParam(':id_request', $data['id_request'], PDO::PARAM_INT);
    $query1->execute();

    $request = null;
    if ($query1->rowCount() > 0) {
        $request = $query1->fetch(PDO::FETCH_ASSOC);
    }

    $json['request'] = $request;

    $query2 = $PDO->prepare("SELECT clients.corporate_name, clients.cnpj FROM requests INNER JOIN clients ON requests.id_client = clients.id WHERE requests.id = :id_request");
    $query2->bindParam(':id_request', $data['id_request'], PDO::PARAM_INT);
    $query2->execute();

    $client = null;
    if ($query2->rowCount() > 0) {
        $client = $query2->fetch(PDO::FETCH_ASSOC);
    }

    $json['client'] = $client;

    $query3 = $PDO->prepare("SELECT sellers.first_name, sellers.last_name FROM requests INNER JOIN sellers ON requests.id_seller = sellers.id WHERE requests.id = :id_request");
    $query3->bindParam(':id_request', $data['id_request'], PDO::PARAM_INT);
    $query3->execute();

    $seller = null;
    if ($query3->rowCount() > 0) {
        $seller = $query3->fetch(PDO::FETCH_ASSOC);
    }

    $json['seller'] = $seller;

    $query4 = $PDO->prepare("SELECT products.title, products.photo, requests.previous_amount, requests.current_amount FROM requests INNER JOIN products ON products.id = requests.id_product WHERE request_number = :request_number");
    $query4->bindParam(':request_number', $data['request_number'], PDO::PARAM_INT);
    $query4->execute();

    $products = null;
    if ($query4->rowCount() > 0) {
        $products = $query4->fetchAll(PDO::FETCH_ASSOC);
    }

    $json['products'] = $products;

    echo json_encode($json);
}
