<?php

require_once('../Connect.php');

@ini_set("display_errors", 1);
@ini_set("log_errors", 1);
@ini_set("error_reporting", E_ALL);

$postjson = json_decode(file_get_contents("php://input"), true);

if ($postjson) {
    $data = filter_var_array($postjson, FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Verificar se o ID do registro a ser atualizado foi fornecido
    if (!isset($data['id']) || empty($data['id'])) {
        echo json_encode(['error' => 'ID do registro não fornecido']);
        exit;
    }
    
    $id = $data['id'];
    $diluter = $data['diluter'] ?? 0;
    $observation = $data['observation'] ?? null;
    
    if (!$diluter) {
        $observation = 'NÃO TERÁ DILUIDOR';
    }
    
    // Iniciar transação para garantir consistência nas operações
    $PDO->beginTransaction();
    
    try {
        // CORREÇÃO: Extrair e tratar os valores dos campos de diluidor
        $diluter_total = isset($data['diluter_total']) ? trim($data['diluter_total']) : '';
        $baskets = isset($data['baskets']) ? trim($data['baskets']) : '';
        $locks = isset($data['locks']) ? trim($data['locks']) : '';
        $registers = isset($data['registers']) ? trim($data['registers']) : '';
        $padlocks = isset($data['padlocks']) ? trim($data['padlocks']) : '';
        
        // Se diluter = 0, limpar os campos relacionados
        if (!$diluter) {
            $diluter_total = '';
            $baskets = '';
            $locks = '';
            $registers = '';
            $padlocks = '';
        }
        
        // Atualizar dados principais do preview
        $query = $PDO->prepare("UPDATE client_previews SET 
            client = :client, 
            sector = :sector, 
            diluter = :diluter, 
            diluter_total = :diluter_total,
            baskets = :baskets, 
            locks = :locks, 
            registers = :registers, 
            padlocks = :padlocks, 
            installation_location = :installation_location, 
            water_point = :water_point, 
            water_pressure = :water_pressure, 
            electric_point = :electric_point, 
            need_pump = :need_pump, 
            observation = :observation, 
            id_seller = :id_seller, 
            updated_at = NOW() 
            WHERE id = :id");
        $id_seller = $data['id_seller'] ?? null;
        
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->bindParam(':client', $data['client'], PDO::PARAM_STR);
        $query->bindParam(':sector', $data['sector'], PDO::PARAM_STR);
        $query->bindParam(':diluter', $diluter, PDO::PARAM_BOOL);
        $query->bindParam(':diluter_total', $diluter_total, PDO::PARAM_STR);
        $query->bindParam(':baskets', $baskets, PDO::PARAM_STR);
        $query->bindParam(':locks', $locks, PDO::PARAM_STR);
        $query->bindParam(':registers', $registers, PDO::PARAM_STR);
        $query->bindParam(':padlocks', $padlocks, PDO::PARAM_STR);
        $query->bindParam(':installation_location', $data['installation_location'], PDO::PARAM_STR);
        $query->bindParam(':water_point', $data['water_point'], PDO::PARAM_STR);
        $query->bindParam(':water_pressure', $data['water_pressure'], PDO::PARAM_STR);
        $query->bindParam(':electric_point', $data['electric_point'], PDO::PARAM_STR);
        $query->bindParam(':need_pump', $data['need_pump'], PDO::PARAM_STR);
        $query->bindParam(':observation', $observation, PDO::PARAM_STR);
        $query->bindParam(':id_seller', $id_seller, PDO::PARAM_INT);
        
        $query->execute();

        // Remover equipamentos existentes antes de inserir os novos
        if (isset($data['equipments'])) {
            $delete_equipments = $PDO->prepare("DELETE FROM client_preview_equipments WHERE id_preview = :id_preview");
            $delete_equipments->bindParam(':id_preview', $id, PDO::PARAM_INT);
            $delete_equipments->execute();

            // Inserir novos equipamentos
            if (!empty($data['equipments'])) {
                foreach ($data['equipments'] as $eq) {
                    $eq_query = $PDO->prepare("INSERT INTO client_preview_equipments (id_preview, sector, id_equipment, quantity, procedure_note, position_note, observation) VALUES (:id_preview, :sector, :id_equipment, :quantity, :procedure, :position, :observation)");
                    $id_equipment = $eq['id_equipment'] ?? null;
                    $eq_query->bindParam(':id_preview', $id, PDO::PARAM_INT);
                    $eq_query->bindParam(':sector', $eq['sector'], PDO::PARAM_STR);
                    $eq_query->bindParam(':id_equipment', $id_equipment, PDO::PARAM_INT);
                    $eq_query->bindParam(':quantity', $eq['quantity'], PDO::PARAM_INT);
                    $eq_query->bindParam(':procedure', $eq['procedure'], PDO::PARAM_STR);
                    $eq_query->bindParam(':position', $eq['position'], PDO::PARAM_STR);
                    $eq_query->bindParam(':observation', $eq['observation'], PDO::PARAM_STR);
                    $eq_query->execute();
                }
            }
        }

        // Processar novas imagens (se fornecidas)
        if (isset($data['new_images']) && !empty($data['new_images'])) {
            foreach ($data['new_images'] as $img) {
                // Verificar se $img é array e extrair base64 e details
                if (is_array($img)) {
                    $img_base64 = $img['base64'] ?? '';
                    $img_details = $img['details'] ?? '';
                    
                    // Remover o prefixo data:image se existir
                    $img_base64_clean = preg_replace('#^data:image/\\w+;base64,#i', '', $img_base64);
                    
                    $img_name = uniqid() . '.png';
                    file_put_contents('./images/' . $img_name, base64_decode($img_base64_clean));
                    
                    $img_query = $PDO->prepare("INSERT INTO client_preview_images (id_preview, details, image) VALUES (:id_preview, :details, :image)");
                    $img_query->bindParam(':id_preview', $id, PDO::PARAM_INT);
                    $img_query->bindParam(':details', $img_details, PDO::PARAM_STR);
                    $img_query->bindParam(':image', $img_name, PDO::PARAM_STR);
                    $img_query->execute();
                }
            }
        }

        // Processar imagens existentes para atualizar o campo details
        if (isset($data['existing_images']) && !empty($data['existing_images'])) {
            foreach ($data['existing_images'] as $img) {
                if (is_array($img) && isset($img['id'])) {
                    $img_id = $img['id'];
                    $img_details = $img['details'] ?? '';
                    
                    $update_img_query = $PDO->prepare("UPDATE client_preview_images SET details = :details WHERE id = :img_id AND id_preview = :id_preview");
                    $update_img_query->bindParam(':details', $img_details, PDO::PARAM_STR);
                    $update_img_query->bindParam(':img_id', $img_id, PDO::PARAM_INT);
                    $update_img_query->bindParam(':id_preview', $id, PDO::PARAM_INT);
                    $update_img_query->execute();
                }
            }
        }

        // Processar imagens para remoção (se fornecidas)
        if (isset($data['images_to_remove']) && !empty($data['images_to_remove'])) {
            foreach ($data['images_to_remove'] as $img_id) {
                // Primeiro buscar o nome do arquivo para deletar fisicamente
                $select_img = $PDO->prepare("SELECT image FROM client_preview_images WHERE id = :img_id AND id_preview = :id_preview");
                $select_img->bindParam(':img_id', $img_id, PDO::PARAM_INT);
                $select_img->bindParam(':id_preview', $id, PDO::PARAM_INT);
                $select_img->execute();
                
                if ($img_data = $select_img->fetch(PDO::FETCH_ASSOC)) {
                    // Deletar arquivo físico
                    $file_path = './images/' . $img_data['image'];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
                
                // Deletar registro do banco
                $delete_img = $PDO->prepare("DELETE FROM client_preview_images WHERE id = :img_id AND id_preview = :id_preview");
                $delete_img->bindParam(':img_id', $img_id, PDO::PARAM_INT);
                $delete_img->bindParam(':id_preview', $id, PDO::PARAM_INT);
                $delete_img->execute();
            }
        }

        $PDO->commit();
        echo json_encode(['success' => true, 'id' => $id]);
        
    } catch (Exception $e) {
        $PDO->rollBack();
        echo json_encode(['error' => 'Erro ao atualizar registro: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Nenhum dado recebido']);
}