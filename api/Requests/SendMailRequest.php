<?php
require_once('../PHPMailer/PHPMailer.php');
require_once('../PHPMailer/SMTP.php');
require_once('../PHPMailer/Exception.php');
require_once('../Connect.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

$postjson = json_decode(file_get_contents("php://input"), true);
if ($postjson) {
    $data = filter_var_array($postjson, FILTER_SANITIZE_STRIPPED);

    $query = $PDO->prepare("SELECT requests.id, requests.request_number, requests.total, requests.current_amount, requests.created_at, products.title, products.photo, clients_products.price, sellers.first_name, sellers.last_name, clients.corporate_name, clients.cnpj FROM requests INNER JOIN products ON products.id = requests.id_product INNER JOIN clients_products ON clients_products.id_client = requests.id_client INNER JOIN sellers ON sellers.id = requests.id_seller INNER JOIN clients ON clients.id = requests.id_client WHERE requests.request_number = :request_number");
    $query->bindParam(':request_number', $data['request_number'], PDO::PARAM_INT);
    $query->execute();

    $results = null;
    if ($query->rowCount() > 0) {
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
    }

    foreach ($results as $result) {
        $id[] = $result['id'];
        $request_number[] = $result['request_number'];
        $total[] = $result['total'];
        $current_amount[] = $result['current_amount'];
        $date[] = $result['created_at'];
        $price[] = $result['price'];
        $title[] = $result['title'];
        $seller[] = "{$result['first_name']} {$result['last_name']}";
        $client[] = $result['corporate_name'];
        $cnpj[] = $result['cnpj'];
    }

    $id = array_unique($id);
    $request_number = array_unique($request_number);
    $total = array_unique($total);
    $total = number_format(intval($total[0]), 2, ',', '');
    $date = array_unique($date);
    $date = (new DateTime($date[0]))->format('d/m/Y');
    $current_amount = array_unique($current_amount);
    $price = array_unique($price);
    $title = array_unique($title);
    $seller = array_unique($seller);
    $client = array_unique($client);
    $cnpj = array_unique($cnpj);

    echo json_encode($date);

    try {
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host = 'soriedem.com.br';
        $mail->SMTPAuth = true;
        $mail->isHTML(true);
        $mail->Username = 'ti@soriedem.com.br';
        $mail->Password = 'MartjDeveloper';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = '465';

        $mail->setFrom('ti@soriedem.com.br');
        $mail->addAddress('martjdeveloper@gmail.com');
        $mail->Subject = 'Assunto Teste';
        $mail->Body =
            "<div style='width: 60%; background-color: #306192; padding: 20px; margin: auto; border-radius: 20px; margin-bottom: 20px;'>
            <h3 style='color: yellow; text-align: center'>PEDIDO: #{$request_number[0]}</h3>
            <h1 style='color: white'>{$client[0]}</h1>
            <h3 style='color: white'>CNPJ: {$cnpj[0]}</h3>
            <h3 style='color: white'>DATA: {$date}</h3>
            <h3 style='color: white'>Vendedor: {$seller[0]}</h3>
            <h3 style='color: white'>Valor Total: {$total}</h3>
            <div style='width: 100%;'>
            <div style='width: fit-content; margin: auto;'>
                <a href='http://www.localhost/admin/requests/request/{$id[0]}' style='color: white; font-weight: bold; text-decoration: none;'>VER DETALHES</a>
            </div>
            </div>
        </div>";

        if ($mail->send()) {
            echo 'E-mail enviado com sucesso!';
        } else {
            echo 'E-mail não enviado.';
        }
    } catch (Exception $e) {
        echo "Erro ao enviar mensagem: {$mail->ErrorInfo}";
    }
}
