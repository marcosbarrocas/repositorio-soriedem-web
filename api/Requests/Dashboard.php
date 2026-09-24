<?php
require_once('../Connect.php');

$postjson = json_decode(file_get_contents("php://input"), true);
$json = array();

try {
    if ($postjson) {
        $data = filter_var_array($postjson, FILTER_SANITIZE_STRIPPED);

        // Comparacoes de data feitas no MySQL para evitar divergencia de fuso
        // entre o PHP (America/Sao_Paulo) e o created_at gravado pelo banco.
        $query = $PDO->prepare("SELECT request_number, COUNT(*) as count FROM requests WHERE id_seller = :id_seller AND created_at >= CURDATE() AND created_at <= NOW() GROUP BY request_number");
        $query->bindParam(':id_seller', $data['id_seller'], PDO::PARAM_INT);
        $query->execute();

        $todayRequests = $query->fetchAll(PDO::FETCH_ASSOC);

        $query = $PDO->prepare("SELECT request_number, COUNT(*) as count FROM requests WHERE id_seller = :id_seller AND created_at >= DATE_SUB(CURDATE(), INTERVAL DAYOFWEEK(CURDATE()) - 1 DAY) AND created_at <= NOW() GROUP BY request_number");
        $query->bindParam(':id_seller', $data['id_seller'], PDO::PARAM_INT);
        $query->execute();

        $weekRequests = $query->fetchAll(PDO::FETCH_ASSOC);

        $query = $PDO->prepare("SELECT request_number, COUNT(*) as count FROM requests WHERE id_seller = :id_seller AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND created_at <= NOW() GROUP BY request_number");
        $query->bindParam(':id_seller', $data['id_seller'], PDO::PARAM_INT);
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
