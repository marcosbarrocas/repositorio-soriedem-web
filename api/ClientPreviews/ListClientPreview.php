<?php

require_once('../Connect.php');

@ini_set("display_errors", 1);
@ini_set("log_errors", 1);
@ini_set("error_reporting", E_ALL);

// Verificar se foi passado um ID específico para buscar um único registro
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Verificar se foi solicitado listagem por vendedor
$id_seller = isset($_GET['id_seller']) ? intval($_GET['id_seller']) : null;

try {
    if ($id) {
        // Buscar um registro específico pelo ID
        $query = $PDO->prepare("
            SELECT 
                cp.*,
                'Vendedor' as seller_name
            FROM client_previews cp
            LEFT JOIN sellers s ON cp.id_seller = s.id
            WHERE cp.id = :id
        ");
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        
        $preview = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($preview) {
            // Buscar equipamentos associados
            $eq_query = $PDO->prepare("
                SELECT 
                    cpe.*,
                    e.name as equipment_name
                FROM client_preview_equipments cpe
                LEFT JOIN equipments e ON cpe.id_equipment = e.id
                WHERE cpe.id_preview = :id_preview
            ");
            $eq_query->bindParam(':id_preview', $id, PDO::PARAM_INT);
            $eq_query->execute();
            $preview['equipments'] = $eq_query->fetchAll(PDO::FETCH_ASSOC);
            
            // CORREÇÃO: Adicionar o campo 'details' na query SELECT
            $img_query = $PDO->prepare("SELECT id, details, image FROM client_preview_images WHERE id_preview = :id_preview");
            $img_query->bindParam(':id_preview', $id, PDO::PARAM_INT);
            $img_query->execute();
            $preview['images'] = $img_query->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($preview);
        } else {
            echo json_encode(['error' => 'Registro não encontrado']);
        }
        
    } else if ($id_seller) {
        // Listar todos os registros de um vendedor específico
        $query = $PDO->prepare("
            SELECT 
                cp.*,
                'Vendedor' as seller_name
            FROM client_previews cp
            LEFT JOIN sellers s ON cp.id_seller = s.id
            WHERE cp.id_seller = :id_seller
            ORDER BY cp.created_at DESC
        ");
        $query->bindParam(':id_seller', $id_seller, PDO::PARAM_INT);
        $query->execute();
        
        $previews = $query->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($previews);
        
    } else {
        // Listar todos os registros (com paginação opcional)
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
        $offset = ($page - 1) * $limit;
        
        // Contar total de registros
        $count_query = $PDO->prepare("SELECT COUNT(*) as total FROM client_previews");
        $count_query->execute();
        $total = $count_query->fetch(PDO::FETCH_ASSOC)['total'];
        $total_pages = ceil($total / $limit);
        
        // Buscar registros
        $query = $PDO->prepare("
            SELECT 
                cp.*,
                'Vendedor' as seller_name
            FROM client_previews cp
            LEFT JOIN sellers s ON cp.id_seller = s.id
            ORDER BY cp.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $query->bindParam(':limit', $limit, PDO::PARAM_INT);
        $query->bindParam(':offset', $offset, PDO::PARAM_INT);
        $query->execute();
        
        $previews = $query->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'data' => $previews,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => $total_pages
            ]
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao buscar registros: ' . $e->getMessage()]);
}