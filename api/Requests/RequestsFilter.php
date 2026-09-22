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

    $query = $PDO->prepare("SELECT requests.id, requests.request_number, requests.created_at, requests.status, clients.corporate_name, clients.cnpj FROM requests INNER JOIN clients on clients.id = requests.id_client  WHERE requests.id_seller = :id_seller AND requests.status = :status GROUP BY requests.request_number ORDER BY requests.id DESC");
    $query->bindParam(':id_seller', $data['id_seller'], PDO::PARAM_INT);
    $query->bindParam(':status', $data['status'], PDO::PARAM_INT);
    $query->execute();

    $result = null;
    if ($query->rowCount() > 0) {
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
    }
    echo json_encode($result);
}
