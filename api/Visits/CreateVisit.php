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
        $query = $PDO->prepare("INSERT INTO visits (id_seller, id_client, client, details, signature, representative, responsible) VALUES (:id_seller, :id_client, :client, :details, :signature, :representative, :responsible)");
        $query->bindParam(':id_seller', $data['id_seller'], PDO::PARAM_INT);
        $query->bindParam(':id_client', $data['id_client'], PDO::PARAM_INT);
        $query->bindParam(':client', $data['client'], PDO::PARAM_STR_CHAR);
        $query->bindParam(':details', $data['details'], PDO::PARAM_STR);
        $query->bindParam(':signature', $file_name, PDO::PARAM_STR);
        $representative = $data['representative'] ?? null;
        $responsible = $data['responsible'] ?? null;
        $query->bindParam(':representative', $representative, PDO::PARAM_STR);
        $query->bindParam(':responsible', $responsible, PDO::PARAM_STR);
        $query->execute();
    }

    $created = ($PDO->lastInsertId()) ? $PDO->lastInsertId() : false;

    if ($created && !empty($data['images'])) {
        foreach ($data['images'] as $img) {
            $img_name = uniqid() . '.png';
            file_put_contents('./images/' . $img_name, base64_decode(preg_replace('#^data:image/\\w+;base64,#i', '', $img)));
            $img_query = $PDO->prepare("INSERT INTO visits_images (id_visit, image) VALUES (:id_visit, :image)");
            $img_query->bindParam(':id_visit', $created, PDO::PARAM_INT);
            $img_query->bindParam(':image', $img_name, PDO::PARAM_STR);
            $img_query->execute();
        }
    }

    echo json_encode($created);
}
