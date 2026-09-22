<?php 

require_once('../Connect.php');

$postjson = json_decode(file_get_contents('php://input'), true);

$search = '%' .@$_GET['search']. '%';

$query = $PDO->prepare("SELECT * FROM products WHERE title LIKE '$search' ORDER BY title ASC");

$query->execute();

$res = $query->fetchAll(PDO::FETCH_ASSOC);

for ($i=0; $i < count($res); $i++) { 
    foreach ($res[$i] as $key => $value) {  }

    $data[] = array(
        'id' => $res[$i]['id'],
        'title' => $res[$i]['title'],
        'photo' => $res[$i]['photo']
    );
}

if(count($res) > 0){
    $result = json_encode(array('success'=>true, 'products'=>$data));
}else{
    $result = json_encode(array('success'=>false, 'result'=>'0'));
}

echo $result;