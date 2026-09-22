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

    $query = $PDO->prepare("SELECT id, id_seller, id_client, client, details, signature, representative, responsible, created_at, updated_at FROM visits WHERE id_seller = :id_seller ORDER BY id DESC");
    $query->bindParam(':id_seller', $data['id_seller'], PDO::PARAM_INT);
    $query->execute();

    $result = null;
    if ($query->rowCount() > 0) {
        $visits = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach ($visits as $visit) {
            $img_query = $PDO->prepare("SELECT image FROM visits_images WHERE id_visit = :id_visit");
            $img_query->bindParam(':id_visit', $visit['id'], PDO::PARAM_INT);
            $img_query->execute();
            $images = $img_query->fetchAll(PDO::FETCH_COLUMN);
            $visit['images'] = $images;
            $result[] = $visit;
        }
    }
    echo json_encode($result);
}
