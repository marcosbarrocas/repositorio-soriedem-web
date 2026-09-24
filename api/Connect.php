<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set('America/Sao_Paulo');

// Config especifica do ambiente (fora do git), se existir: define CONF_DB_*.
$localConfig = __DIR__ . '/../source/Boot/config.local.php';
if (is_file($localConfig)) {
    require_once $localConfig;
}

$localhost = getenv('SORIEDEM_LOCAL') === '1';

if (defined('CONF_DB_HOST')) {
    $host = CONF_DB_HOST;
    $user = CONF_DB_USER;
    $password = CONF_DB_PASS;
    $db = CONF_DB_NAME;
} elseif ($localhost) {
    $host = '127.0.0.1';
    $user = 'soriedem_app';
    $password = getenv('DB_PASS') ?: 'localdev';
    $db = 'soriedem_app';
} else {
    // Servidores reais definem as credenciais em config.local.php.
    $host = getenv('DB_HOST') ?: 'localhost';
    $user = getenv('DB_USER') ?: 'soriedem_app';
    $password = getenv('DB_PASS') ?: '';
    $db = getenv('DB_NAME') ?: 'soriedem_app';
}

try {
    $PDO = new PDO("mysql:dbname=$db; charset=utf8; host=$host", "$user", "$password");
} catch (Exception $e) {
    echo 'Erro ao conectar com o banco!!' . $e;
}
