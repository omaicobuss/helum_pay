<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/../db.php'; // Inclui a conexão com o banco
require_once __DIR__ . '/conf.php'; // Inclui as credenciais do MP

// SDK do Mercado Pago
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;

// Configura o Access Token a partir do arquivo de configuração
MercadoPagoConfig::setAccessToken($mp_credentials['access_token']);

// Pega o ID da assinatura da URL
$subscription_id = $_GET['subscription_id'] ?? 0;

if (!$subscription_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID da assinatura não fornecido.']);
    exit;
}

// Busca os dados do produto associado à assinatura
$stmt = $conn->prepare(
    "SELECT p.name, p.price FROM products p JOIN subscriptions s ON p.id = s.product_id WHERE s.id = ?"
);
$stmt->bind_param("i", $subscription_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    http_response_code(404);
    echo json_encode(['error' => 'Produto não encontrado para esta assinatura.']);
    exit;
}

try {
    $client = new PreferenceClient();
    $request = [
        "items" => [
            ["title" => $product['name'], "quantity" => 1, "unit_price" => (float)$product['price']]
        ],
        "back_urls" => [
            "success" => "https://www.helum.com.br/helum_pay/_mp/success.php",
            "failure" => "https://www.helum.com.br/helum_pay/_mp/failure.php",
            "pending" => "https://www.helum.com.br/helum_pay/_mp/pending.php"
        ],
        "auto_return" => "approved",
        "external_reference" => $subscription_id
    ];

    $preference = $client->create($request);

    // Registra a tentativa de pagamento no banco de dados
    $current_date = date("Y-m-d H:i:s");
    $stmt_log = $conn->prepare(
        "INSERT INTO payments (subscription_id, amount, status, mp_preference_id, payment_date) VALUES (?, ?, 'initiated', ?, ?)"
    );
    $stmt_log->bind_param("idss", $subscription_id, $product['price'], $preference->id, $current_date);
    $stmt_log->execute();
    $stmt_log->close();

    header('Content-Type: application/json');
    echo json_encode(['id' => $preference->id]);

} catch (MPApiException $e) {
    http_response_code(500);
    error_log("MPApiException in main.php: " . $e->getMessage());
    echo json_encode(['error' => 'Erro de API ao criar preferência.']);
    echo "Status code: " . $e->getApiResponse()->getStatusCode() . "\n";
    echo "Content: ";
    var_dump($e->getApiResponse()->getContent());
    echo "\n";
} catch (Exception $e) {
    http_response_code(500);
    error_log("Exception in main.php: " . $e->getMessage());
    echo json_encode(['error' => 'Erro interno ao processar pagamento.']);
    echo $e->getMessage();
}