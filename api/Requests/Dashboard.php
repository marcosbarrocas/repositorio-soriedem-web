<?php
require_once('../Connect.php');

$postjson = json_decode(file_get_contents("php://input"), true);
$json = array();

try {
    if ($postjson) {
        $data = filter_var_array($postjson, FILTER_SANITIZE_STRIPPED);

        $today = date('Y-m-d H:i:s');
        $startOfDay = date('Y-m-d 00:00:00');

        $startOfWeek = date('Y-m-d', strtotime('last Sunday'));
        $startOfMonth = date('Y-m-01');

        $query = $PDO->prepare("SELECT request_number, COUNT(*) as count FROM requests WHERE id_seller = :id_seller AND created_at >= :startOfDay AND created_at <= :today GROUP BY request_number");
        $query->bindParam(':id_seller', $data['id_seller'], PDO::PARAM_INT);
        $query->bindParam(':startOfDay', $startOfDay, PDO::PARAM_STR);
        $query->bindParam(':today', $today, PDO::PARAM_STR);
        $query->execute();

        $todayRequests = $query->fetchAll(PDO::FETCH_ASSOC);

        $query = $PDO->prepare("SELECT request_number, COUNT(*) as count FROM requests WHERE id_seller = :id_seller AND created_at >= :startOfWeek AND created_at <= :today GROUP BY request_number");
        $query->bindParam(':id_seller', $data['id_seller'], PDO::PARAM_INT);
        $query->bindParam(':startOfWeek', $startOfWeek, PDO::PARAM_STR);
        $query->bindParam(':today', $today, PDO::PARAM_STR);
        $query->execute();

        $weekRequests = $query->fetchAll(PDO::FETCH_ASSOC);

        $query = $PDO->prepare("SELECT request_number, COUNT(*) as count FROM requests WHERE id_seller = :id_seller AND created_at >= :startOfMonth AND created_at <= :today GROUP BY request_number");
        $query->bindParam(':id_seller', $data['id_seller'], PDO::PARAM_INT);
        $query->bindParam(':startOfMonth', $startOfMonth, PDO::PARAM_STR);
        $query->bindParam(':today', $today, PDO::PARAM_STR);
        $query->execute();

        $monthRequests = $query->fetchAll(PDO::FETCH_ASSOC);

        $json['today'] = empty($todayRequests) ? 0 : count($todayRequests);
        $json['week'] = empty($weekRequests) ? 0 : count($weekRequests);
        $json['month'] = empty($monthRequests) ? 0 : count($monthRequests);
    }
} catch (PDOException $e) {
    http_response_code(500); // Define o código de resposta 500 (Internal Server Error)
    $json['error'] = $e->getMessage(); // Retorna a mensagem de erro
}

echo json_encode($json);
