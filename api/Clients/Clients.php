<?php
require_once ('../Connect.php');

$postjson = json_decode(file_get_contents("php://input"), true);
if ($postjson) {
    $data = filter_var_array($postjson, FILTER_SANITIZE_STRIPPED);

    $query = $PDO->prepare("SELECT * FROM clients WHERE id_seller = :id_seller");
    $query->bindParam(':id_seller', $data['id_seller'], PDO::PARAM_INT);
    $query->execute();

    $data = null;
    if ($query->rowCount() > 0) {
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
    }
    echo json_encode($data);
}