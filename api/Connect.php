<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set('America/Sao_Paulo');

$localhost = false;

if ($localhost) {
    $host = '192.168.100.41';
    $user = 'root';
    $password = '4144';
    $db = 'databasedefault';
} else {
    $host = 'localhost';
    $user = 'soriedem_app';
    $password = 'dxmk]$&SHRLr';
    $db = 'soriedem_app';
}

try {
    $PDO = new PDO("mysql:dbname=$db; charset=utf8; host=$host", "$user", "$password");
} catch (Exception $e) {
    echo 'Erro ao conectar com o banco!!' . $e;
}
