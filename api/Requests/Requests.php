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

    $query = $PDO->prepare("SELECT request_number, client, id_client, status, MAX(created_at) AS created_at
        FROM requests
        WHERE id_seller = :id_seller
        GROUP BY request_number, client, id_client, status
        ORDER BY MAX(id) DESC");
    $query->bindParam(':id_seller', $data['id_seller'], PDO::PARAM_INT);
    $query->execute();

    $result = null;
    if ($query->rowCount() > 0) {
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
    }
    echo json_encode($result);
}
