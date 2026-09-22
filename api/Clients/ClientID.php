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
    
    $query = $PDO->prepare("SELECT * FROM clients WHERE id = :id");
    $query->bindParam(':id', $data['id'], PDO::PARAM_INT);
    $query->execute();

    $result = null;
    if ($query->rowCount() > 0) {
        $result = $query->fetch(PDO::FETCH_ASSOC);
    }
    echo json_encode($result);
}