<?php
require_once('../Connect.php');

@ini_set("display_errors", 1);
@ini_set("log_errors", 1);
@ini_set("error_reporting", E_ALL);

$postjson = json_decode(file_get_contents("php://input"), true);
if ($postjson) {
    $data = filter_var_array($postjson, FILTER_SANITIZE_STRIPPED);

    $file_name = uniqid() . '.png';

    file_put_contents('./signs/' . $file_name, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $data['signature'])));

    if ($data['id_client']) {
        $query = $PDO->prepare("INSERT INTO requests (request_number, seller, client, id_client, id_seller, seller_fullname, signature, previous_amount, current_amount, id_product, latitude, longitude, total, total_item_value, item_value) VALUES (:request_number, :seller, :client, :id_client, :id_seller, :seller_fullname, :signature, :previous_amount, :current_amount, :id_product, :latitude, :longitude, :total, :total_item_value, :item_value)");
        $query->bindParam(':request_number', $data['request_number'], PDO::PARAM_INT);
        $query->bindParam(':seller', $data['seller'], PDO::PARAM_STR_CHAR);
        $query->bindParam(':client', $data['client'], PDO::PARAM_STR_CHAR);
        $query->bindParam(':id_client', $data['id_client'], PDO::PARAM_INT);
        $query->bindParam(':id_seller', $data['id_seller'], PDO::PARAM_INT);
        $query->bindParam(':seller_fullname', $data['seller_fullname'], PDO::PARAM_STR);
        $query->bindParam(':signature', $file_name, PDO::PARAM_STR);
        $query->bindParam(':previous_amount', $data['previous_amount'], PDO::PARAM_INT);
        $query->bindParam(':current_amount', $data['current_amount'], PDO::PARAM_INT);
        $query->bindParam(':id_product', $data['id_product'], PDO::PARAM_INT);
        $query->bindParam(':latitude', $data['latitude'], PDO::PARAM_STR);
        $query->bindParam(':longitude', $data['longitude'], PDO::PARAM_STR);
        $query->bindParam(':total', $data['total'], PDO::PARAM_STR);
        $query->bindParam(':total_item_value', $data['total_item_value'], PDO::PARAM_STR);
        $query->bindParam(':item_value', $data['item_value'], PDO::PARAM_STR);
        $query->execute();
    }

    $created = ($PDO->lastInsertId()) ? $PDO->lastInsertId() : false;

    echo json_encode($created);
}
