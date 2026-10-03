<?php
/**
 * Cria um pedido de venda na Omie (IncluirPedido) e registra localmente em `requests`.
 *
 * POST JSON:
 * {
 *   "id_client": 123,            // id local do cliente (clients.id)
 *   "id_seller": 4,             // id local do vendedor (sellers.id)
 *   "seller": "Nome",           // nome do vendedor (registro local)
 *   "seller_fullname": "...",   // assinante (opcional)
 *   "signature": "data:image/png;base64,....", // opcional
 *   "latitude": "...", "longitude": "...",        // opcional
 *   "etapa": "10", "codigo_parcela": "999",       // opcional
 *   "items": [ { "id_product": 183, "quantidade": 2, "valor_unitario": 171.99 } ]
 * }
 *
 * Retorna: { success, codigo_pedido, numero_pedido } ou { success:false, error }.
 */
require_once('../Connect.php');
require_once('../Guard.php');
require_once(__DIR__ . '/../../vendor/autoload.php');

use Source\Support\Omie;

$seller = require_seller($PDO);

$postjson = json_decode(file_get_contents("php://input"), true);
if (!$postjson) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Corpo JSON obrigatorio.']);
    return;
}

$idClient = (int)($postjson['id_client'] ?? 0);
$idSeller = (int)$seller['id']; // vendedor autenticado (nao confiar no corpo)
$items = $postjson['items'] ?? [];

if ($idClient <= 0 || empty($items) || !is_array($items)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'id_client e items sao obrigatorios.']);
    return;
}

try {
    // 1) codigo do cliente na Omie
    $stmt = $PDO->prepare("SELECT code, corporate_name FROM clients WHERE id = :id");
    $stmt->execute([':id' => $idClient]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$client || empty($client['code'])) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Cliente sem codigo Omie.']);
        return;
    }
    $codigoClienteOmie = (int)$client['code'];

    // 2) codigo do vendedor na Omie (opcional)
    $codVend = null;
    $sellerName = (string)($postjson['seller'] ?? '');
    if ($idSeller > 0) {
        $s = $PDO->prepare("SELECT omie_codigo, first_name, last_name FROM sellers WHERE id = :id");
        $s->execute([':id' => $idSeller]);
        if ($seller = $s->fetch(PDO::FETCH_ASSOC)) {
            $codVend = $seller['omie_codigo'] ? (int)$seller['omie_codigo'] : null;
            if ($sellerName === '') {
                $sellerName = trim("{$seller['first_name']} {$seller['last_name']}");
            }
        }
    }

    // 3) resolve os produtos (omie_codigo) e monta os itens
    $omieItens = [];
    $localItens = [];
    $total = 0.0;
    foreach ($items as $it) {
        $idProduct = (int)($it['id_product'] ?? 0);
        $qtd = (float)($it['quantidade'] ?? 0);
        if ($idProduct <= 0 || $qtd <= 0) {
            continue;
        }
        $p = $PDO->prepare("SELECT omie_codigo, value FROM products WHERE id = :id");
        $p->execute([':id' => $idProduct]);
        $prod = $p->fetch(PDO::FETCH_ASSOC);
        if (!$prod || empty($prod['omie_codigo'])) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => "Produto {$idProduct} sem codigo Omie."]);
            return;
        }
        $valorUnit = isset($it['valor_unitario']) ? (float)$it['valor_unitario'] : (float)$prod['value'];
        $previousAmount = (int)($it['previous_amount'] ?? 0);
        $omieItens[] = [
            'codigo_produto' => (int)$prod['omie_codigo'],
            'quantidade' => $qtd,
            'valor_unitario' => $valorUnit,
        ];
        $localItens[] = [
            'id_product' => $idProduct,
            'quantidade' => $qtd,
            'valor_unitario' => $valorUnit,
            'previous_amount' => $previousAmount,
        ];
        $total += $qtd * $valorUnit;
    }

    if (empty($omieItens)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Nenhum item valido.']);
        return;
    }

    // 4) inclui o pedido na Omie
    $omie = new Omie();
    $codigoIntegracao = 'SORIEDEM-' . $idSeller . '-' . $idClient . '-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
    $cabecalho = [
        'codigo_cliente' => $codigoClienteOmie,
        'codigo_pedido_integracao' => $codigoIntegracao,
        'codigo_parcela' => (string)($postjson['codigo_parcela'] ?? '999'),
        'data_previsao' => (string)($postjson['data_previsao'] ?? date('d/m/Y')),
        'etapa' => (string)($postjson['etapa'] ?? '10'),
    ];
    $informacoesAdicionais = [
        'codigo_categoria' => (string)($postjson['codigo_categoria'] ?? '1.01.03'),
        'codigo_conta_corrente' => (int)($postjson['codigo_conta_corrente'] ?? 2384265605),
    ];
    $resp = $omie->incluirPedido($cabecalho, $omieItens, $codVend, $informacoesAdicionais);
    if ($resp === null) {
        http_response_code(502);
        echo json_encode(['success' => false, 'error' => 'Omie: ' . $omie->error()]);
        return;
    }

    $codigoPedido = $resp['codigo_pedido'] ?? null;
    $numeroPedido = $resp['numero_pedido'] ?? ($codigoPedido ? (string)$codigoPedido : null);

    // 5) registra localmente (uma linha por item, como o fluxo atual)
    $signatureFile = '';
    if (!empty($postjson['signature'])) {
        // Mesma pasta do fluxo legado (api/Requests/signs), de onde o app le a assinatura.
        $signsDir = __DIR__ . '/../Requests/signs';
        if (!is_dir($signsDir)) {
            @mkdir($signsDir, 0775, true);
        }
        $signatureFile = uniqid() . '.png';
        file_put_contents(
            $signsDir . '/' . $signatureFile,
            base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $postjson['signature']))
        );
    }

    $ins = $PDO->prepare("INSERT INTO requests
        (request_number, seller, client, id_client, id_seller, seller_fullname, signature, previous_amount, current_amount, id_product, latitude, longitude, total, total_item_value, item_value)
        VALUES (:request_number, :seller, :client, :id_client, :id_seller, :seller_fullname, :signature, :previous_amount, :current_amount, :id_product, :latitude, :longitude, :total, :total_item_value, :item_value)");

    foreach ($localItens as $li) {
        $ins->execute([
            ':request_number' => $numeroPedido,
            ':seller' => $sellerName,
            ':client' => $client['corporate_name'],
            ':id_client' => $idClient,
            ':id_seller' => $idSeller,
            ':seller_fullname' => (string)($postjson['seller_fullname'] ?? $sellerName),
            ':signature' => $signatureFile,
            ':previous_amount' => (int)$li['previous_amount'],
            ':current_amount' => (int)$li['quantidade'],
            ':id_product' => $li['id_product'],
            ':latitude' => (float)($postjson['latitude'] ?? 0),
            ':longitude' => (float)($postjson['longitude'] ?? 0),
            ':total' => (string)$total,
            ':total_item_value' => (string)($li['quantidade'] * $li['valor_unitario']),
            ':item_value' => (string)$li['valor_unitario'],
        ]);
    }

    echo json_encode([
        'success' => true,
        'codigo_pedido' => $codigoPedido,
        'numero_pedido' => $numeroPedido,
    ], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao criar pedido: ' . $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
