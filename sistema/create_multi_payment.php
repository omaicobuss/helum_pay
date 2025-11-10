<?php
session_start();
require 'db.php';
require '_mp/vendor/autoload.php';
require '_mp/conf.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;

// Proteção: Apenas clientes logados
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cliente') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$subscription_ids = $_POST['subscription_ids'] ?? [];

if (empty($subscription_ids)) {
    $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Nenhuma fatura foi selecionada.'];
    header("Location: dashboard_cliente.php");
    exit();
}

// Configura o Access Token do Mercado Pago
MercadoPagoConfig::setAccessToken($mp_credentials['access_token']);

// Garante que todos os IDs são inteiros para segurança
$safe_ids = array_map('intval', $subscription_ids);
$placeholders = implode(',', array_fill(0, count($safe_ids), '?')); // Cria ?,?,?

// Busca os dados das assinaturas selecionadas, garantindo que pertencem ao usuário
$sql = "
    SELECT s.id, p.price, p.name
    FROM subscriptions s
    JOIN products p ON s.product_id = p.id
    WHERE s.user_id = ? AND s.id IN ($placeholders) AND s.status IN ('active', 'pending')
";

$types = 'i' . str_repeat('i', count($safe_ids));
$params = array_merge([$user_id], $safe_ids);

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== count($safe_ids)) {
    $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Uma ou mais faturas selecionadas são inválidas ou não podem ser pagas.'];
    header("Location: dashboard_cliente.php");
    exit();
}

$total_amount = 0;
$item_names = [];
$verified_ids = [];
while ($row = $result->fetch_assoc()) {
    $total_amount += (float)$row['price'];
    $item_names[] = $row['name'];
    $verified_ids[] = $row['id'];
}
$stmt->close();

// Cria a preferência de pagamento no Mercado Pago
try {
    $client = new PreferenceClient();
    $preference = $client->create([
        "items" => [
            [
                "title" => "Pagamento de " . count($verified_ids) . " faturas",
                "description" => "Itens: " . implode(', ', $item_names),
                "quantity" => 1,
                "unit_price" => $total_amount
            ]
        ],
        "back_urls" => [
            "success" => "https://www.helum.com.br/sistemas/helum_pay/sistema/dashboard_cliente.php?payment_status=success",
            "failure" => "https://www.helum.com.br/sistemas/helum_pay/sistema/dashboard_cliente.php?payment_status=failure",
            "pending" => "https://www.helum.com.br/sistemas/helum_pay/sistema/dashboard_cliente.php?payment_status=pending"
        ],
        "auto_return" => "approved",
        "external_reference" => implode(',', $verified_ids)
    ]);

    // Registra a tentativa de pagamento para cada fatura
    $current_date = date("Y-m-d H:i:s");
    $stmt_log = $conn->prepare(
        "INSERT INTO payments (subscription_id, amount, status, mp_preference_id, payment_date) VALUES (?, ?, 'initiated', ?, ?)"
    );
    
    $conn->begin_transaction();
    foreach ($verified_ids as $id) {
        // Precisamos do preço individual aqui
        $individual_price_sql = "SELECT p.price FROM products p JOIN subscriptions s ON p.id = s.product_id WHERE s.id = ?";
        $stmt_price = $conn->prepare($individual_price_sql);
        $stmt_price->bind_param("i", $id);
        $stmt_price->execute();
        $price_result = $stmt_price->get_result()->fetch_assoc();
        $individual_price = $price_result['price'];
        $stmt_price->close();

        $stmt_log->bind_param("idss", $id, $individual_price, $preference->id, $current_date);
        $stmt_log->execute();
    }
    $conn->commit();
    $stmt_log->close();

    // Redireciona o usuário para o checkout do Mercado Pago
    header("Location: " . $preference->init_point);
    exit();

} catch (MPApiException $e) {
    error_log("MPApiException in create_multi_payment.php: " . $e->getMessage());
    $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Erro de API ao criar a preferência de pagamento.'];
    header("Location: dashboard_cliente.php");
    exit();
} catch (Exception $e) {
    error_log("Exception in create_multi_payment.php: " . $e->getMessage());
    if ($conn->in_transaction) {
        $conn->rollback();
    }
    $_SESSION['feedback'] = ['type' => 'error', 'message' => 'Erro interno ao processar o pagamento.'];
    header("Location: dashboard_cliente.php");
    exit();
} finally {
    $conn->close();
}
?>
