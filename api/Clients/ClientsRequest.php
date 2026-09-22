<?php
require_once ('../Connect.php');

$postjson = json_decode(file_get_contents("php://input"), true);
if ($postjson) {
    $data = filter_var_array($postjson, FILTER_SANITIZE_STRIPPED);

    $query = $PDO->prepare("SELECT (SELECT R1.request_number FROM requests AS R1 WHERE R1.id_client = C.id ORDER BY R1.id DESC LIMIT 1) AS request_number, (SELECT R1.status FROM requests AS R1 WHERE R1.id_client = C.id ORDER BY R1.id DESC LIMIT 1) AS status, (SELECT R1.created_at FROM requests AS R1 WHERE R1.id_client = C.id ORDER BY R1.id DESC LIMIT 1) AS created_at, C.corporate_name, C.cnpj FROM clients AS C INNER JOIN requests AS R ON R.id_client = C.id WHERE R.id_seller = :id_seller GROUP BY C.id");
    $query->bindParam(':id_seller', $data['id_seller'], PDO::PARAM_INT);
    $query->execute();

    $data = null;
    if ($query->rowCount() > 0) {
        $data = $query->fetchAll(PDO::FETCH_ASSOC);
    }
    echo json_encode($data);
}