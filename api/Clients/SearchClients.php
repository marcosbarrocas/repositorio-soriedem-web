<?php 

require_once('../Connect.php');

$postjson = json_decode(file_get_contents('php://input'), true);

$search = '%' .@$_GET['search']. '%';

$query = $PDO->prepare("SELECT * FROM clients WHERE contact_name LIKE '$search' OR email LIKE '$search' ORDER BY contact_name ASC");

$query->execute();

$res = $query->fetchAll(PDO::FETCH_ASSOC);

for ($i=0; $i < count($res); $i++) { 
    foreach ($res[$i] as $key => $value) {  }

    $data[] = array(
        'id' => $res[$i]['id'],
        'contact_name' => $res[$i]['contact_name'],
        'email' => $res[$i]['email']
    );
}

if(count($res) > 0){
    $result = json_encode(array('success'=>true, 'clients'=>$data));
}else{
    $result = json_encode(array('success'=>false, 'result'=>'0'));
}

echo $result;