<?php
require_once('../Connect.php');

$postjson = json_decode(file_get_contents("php://input"), true);
if ($postjson) {
    $data = filter_var_array($postjson, FILTER_SANITIZE_STRIPPED);

    $query = $PDO->prepare("SELECT * FROM client_previews WHERE id = :id");
    $query->bindParam(':id', $data['id'], PDO::PARAM_INT);
    $query->execute();

    $result = null;
    if ($query->rowCount() > 0) {
        $result = $query->fetch(PDO::FETCH_ASSOC);

        $equip_query = $PDO->prepare("SELECT C.*, E.name as equipment_name FROM client_preview_equipments AS C INNER JOIN equipments AS E ON C.id_equipment = E.id WHERE C.id_preview = :id_preview");
        $equip_query->bindParam(':id_preview', $data['id'], PDO::PARAM_INT);
        $equip_query->execute();
        $equipments = null;
        if ($equip_query->rowCount() > 0) {
            $equipments = $equip_query->fetchAll(PDO::FETCH_ASSOC);
        }

        $img_query = $PDO->prepare("SELECT details,image FROM client_preview_images WHERE id_preview = :id_preview");
        $img_query->bindParam(':id_preview', $data['id'], PDO::PARAM_INT);
        $img_query->bindParam(':details', $data['id'], PDO::PARAM_STR);
        $img_query->execute();
        $images = null;
        if ($img_query->rowCount() > 0) {
            $images = $img_query->fetchAll(PDO::FETCH_ASSOC);
        }

        $result['equipments'] = $equipments;
        $result['images'] = $images;
    }

    echo json_encode($result);
}
