<?php
$code = @$_GET['code'];


//$query = $pdo->prepare("INSERT INTO code SET redirect_code = $code");

//$query->execute();

$result = json_encode(array('success' => true, 'code' => $code));

echo $result;
