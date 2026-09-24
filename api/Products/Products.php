<?php
require_once('../Connect.php');

$postjson = json_decode(file_get_contents("php://input"), true);
// Somente produtos da Omie (com codigo Omie); os antigos ficam de fora da lista.
$query = $PDO->query("SELECT * FROM products WHERE omie_codigo IS NOT NULL AND omie_codigo <> '' ORDER BY title");

$data = null;
if ($query->rowCount() > 0) {
    $data = $query->fetchAll(PDO::FETCH_ASSOC);


    foreach ($data as &$row) {
        foreach ($row as $key => $value) {
            if ($value === 1 || $value === '1') {
                $row[$key] = true;
            } elseif ($value === 0 || $value === '0') {
                $row[$key] = false;
            }
        }
    }
    unset($row);
} else {
    $data = [];
}
echo json_encode($data);
