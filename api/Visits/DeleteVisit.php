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

    $visit_query = $PDO->prepare("SELECT signature FROM visits WHERE id = :id");
    $visit_query->bindParam(':id', $data['id'], PDO::PARAM_INT);
    $visit_query->execute();

    $deleted = false;
    if ($visit_query->rowCount() > 0) {
        $visit = $visit_query->fetch(PDO::FETCH_ASSOC);
        if (!empty($visit['signature'])) {
            @unlink('./signs/' . $visit['signature']);
        }

        $img_query = $PDO->prepare("SELECT image FROM visits_images WHERE id_visit = :id_visit");
        $img_query->bindParam(':id_visit', $data['id'], PDO::PARAM_INT);
        $img_query->execute();
        $images = $img_query->fetchAll(PDO::FETCH_COLUMN);
        foreach ($images as $image) {
            @unlink('./images/' . $image);
        }

        $del_imgs = $PDO->prepare("DELETE FROM visits_images WHERE id_visit = :id_visit");
        $del_imgs->bindParam(':id_visit', $data['id'], PDO::PARAM_INT);
        $del_imgs->execute();

        $del_visit = $PDO->prepare("DELETE FROM visits WHERE id = :id");
        $del_visit->bindParam(':id', $data['id'], PDO::PARAM_INT);
        $del_visit->execute();
        $deleted = $del_visit->rowCount() > 0;
    }

    echo json_encode($deleted);
}
