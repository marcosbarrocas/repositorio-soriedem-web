<?php
require_once('../Connect.php');

@ini_set("display_errors", 1);
@ini_set("log_errors", 1);
@ini_set("error_reporting", E_ALL);

$postjson = json_decode(file_get_contents("php://input"), true);
if ($postjson) {
    $data = filter_var_array($postjson, FILTER_SANITIZE_SPECIAL_CHARS);

    $diluter = $data['diluter'] ?? 0;
    $observation = $data['observation'] ?? null;
    if (!$diluter) {
        $observation = 'NÃO TERÁ DILUIDOR';
    }

    $query = $PDO->prepare("INSERT INTO client_previews (client, sector, diluter, diluter_total, baskets, locks, registers, padlocks, installation_location, water_point, water_pressure, electric_point, need_pump, observation, id_seller, created_at) VALUES (:client, :sector, :diluter, :diluter_total, :baskets, :locks, :registers, :padlocks, :installation_location, :water_point, :water_pressure, :electric_point, :need_pump, :observation, :id_seller, NOW())");

    $id_seller = $data['id_seller'] ?? null;

    $query->bindParam(':client', $data['client'], PDO::PARAM_STR_CHAR);
    $query->bindParam(':sector', $data['sector'], PDO::PARAM_STR);
    $query->bindParam(':diluter', $diluter, PDO::PARAM_BOOL);
    $query->bindParam(':diluter_total', $data['diluter_total'], PDO::PARAM_STR);
    $query->bindParam(':baskets', $data['baskets'], PDO::PARAM_STR);
    $query->bindParam(':locks', $data['locks'], PDO::PARAM_STR);
    $query->bindParam(':registers', $data['registers'], PDO::PARAM_STR);
    $query->bindParam(':padlocks', $data['padlocks'], PDO::PARAM_STR);
    $query->bindParam(':installation_location', $data['installation_location'], PDO::PARAM_STR);
    $query->bindParam(':water_point', $data['water_point'], PDO::PARAM_STR);
    $query->bindParam(':water_pressure', $data['water_pressure'], PDO::PARAM_STR);
    $query->bindParam(':electric_point', $data['electric_point'], PDO::PARAM_STR);
    $query->bindParam(':need_pump', $data['need_pump'], PDO::PARAM_STR);
    $query->bindParam(':observation', $observation, PDO::PARAM_STR);
    $query->bindParam(':id_seller', $id_seller, PDO::PARAM_INT);
    $query->execute();

    $created = ($PDO->lastInsertId()) ? $PDO->lastInsertId() : false;

    if ($created && !empty($data['equipments'])) {
        foreach ($data['equipments'] as $eq) {
            $eq_query = $PDO->prepare("INSERT INTO client_preview_equipments (id_preview, sector, id_equipment, quantity, procedure_note, position_note, observation) VALUES (:id_preview, :sector, :id_equipment, :quantity, :procedure, :position, :observation)");
            $id_equipment = $eq['id_equipment'] ?? null;
            $eq_query->bindParam(':id_preview', $created, PDO::PARAM_INT);
            $eq_query->bindParam(':sector', $eq['sector'], PDO::PARAM_STR);
            $eq_query->bindParam(':id_equipment', $id_equipment, PDO::PARAM_INT);
            $eq_query->bindParam(':quantity', $eq['quantity'], PDO::PARAM_INT);
            $eq_query->bindParam(':procedure', $eq['procedure'], PDO::PARAM_STR);
            $eq_query->bindParam(':position', $eq['position'], PDO::PARAM_STR);
            $eq_query->bindParam(':observation', $eq['observation'], PDO::PARAM_STR);
            $eq_query->execute();
        }
    }

    if ($created && !empty($data['images'])) {
        foreach ($data['images'] as $img) {
            // Verificar se $img é um array/objeto e extrair base64 e details
            if (is_array($img)) {
                $img_base64 = $img['base64'] ?? '';
                $img_details = $img['details'] ?? '';
                
                // Remover o prefixo data:image se existir
                $img_base64_clean = preg_replace('#^data:image/\\w+;base64,#i', '', $img_base64);
                
                $img_name = uniqid() . '.png';
                file_put_contents('./images/' . $img_name, base64_decode($img_base64_clean));
                
                $img_query = $PDO->prepare("INSERT INTO client_preview_images (id_preview, details, image) VALUES (:id_preview, :details, :image)");
                $img_query->bindParam(':id_preview', $created, PDO::PARAM_INT);
                $img_query->bindParam(':details', $img_details, PDO::PARAM_STR);
                $img_query->bindParam(':image', $img_name, PDO::PARAM_STR);
                $img_query->execute();
            } else {
                // Caso seja uma string (compatibilidade com versão antiga)
                $img_base64_clean = preg_replace('#^data:image/\\w+;base64,#i', '', $img);
                $img_name = uniqid() . '.png';
                file_put_contents('./images/' . $img_name, base64_decode($img_base64_clean));
                
                $img_query = $PDO->prepare("INSERT INTO client_preview_images (id_preview, details, image) VALUES (:id_preview, :details, :image)");
                $img_query->bindParam(':id_preview', $created, PDO::PARAM_INT);
                $img_query->bindParam(':details', '', PDO::PARAM_STR); // String vazia se for formato antigo
                $img_query->bindParam(':image', $img_name, PDO::PARAM_STR);
                $img_query->execute();
            }
        }
    }

    echo json_encode($created);
}
