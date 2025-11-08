<?php
session_start();
require 'db.php';

// Proteção: Apenas clientes logados
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cliente') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$subscription_id = $_GET['id'] ?? 0;

if (!$subscription_id) {
    header("Location: dashboard_cliente.php");
    exit();
}

// Buscar dados da fatura, garantindo que pertence ao usuário logado
$sql = "
    SELECT 
        s.id as subscription_id, 
        s.next_due_date, 
        p.name as product_name, 
        p.description as product_description,
        p.price as product_price,
        u.full_name as user_name,
        u.cpf as user_cpf
    FROM subscriptions s
    JOIN products p ON s.product_id = p.id
    JOIN users u ON s.user_id = u.id
    WHERE s.id = ? AND s.user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $subscription_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Se não encontrou a fatura ou ela não pertence ao usuário, redireciona.
    header("Location: dashboard_cliente.php");
    exit();
}

$invoice = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura - Helum Pay</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard"> <!-- Usa a classe dashboard para o fundo correto e espaçamento -->
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <h1>HELUM PAY</h1>
                                Fatura #: <?php echo $invoice['subscription_id']; ?><br>
                                Vencimento: <?php echo date("d/m/Y", strtotime($invoice['next_due_date'])); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr><td><strong>Cobrança para:</strong><br><?php echo htmlspecialchars($invoice['user_name']); ?><br>CPF: <?php echo htmlspecialchars($invoice['user_cpf']); ?></td></tr>
                    </table>
                </td>
            </tr>
            <tr class="heading"><td>Item</td><td>Preço</td></tr>
            <tr class="item"><td><?php echo htmlspecialchars($invoice['product_name']); ?></td><td>R$ <?php echo number_format($invoice['product_price'], 2, ',', '.'); ?></td></tr>
            <tr class="total"><td></td><td><strong>Total: R$ <?php echo number_format($invoice['product_price'], 2, ',', '.'); ?></strong></td></tr>
        </table>
        <div class="payment-options-container">
            <h3>Escolha o meio de pagamento:</h3>
            
            <!-- Mercado Pago (Ativo) -->
            <a href="_mp/index.html?subscription_id=<?php echo $invoice['subscription_id']; ?>" class="payment-option active">
                <span>Mercado Pago</span>
            </a>

            <!-- Outros Gateways (Inativos) -->
            <div class="payment-option disabled">
                <span>Efí Bank</span><span class="badge">Em breve</span>
            </div>
            <div class="payment-option disabled">
                <span>Paypal</span><span class="badge">Em breve</span>
            </div>
            <div class="payment-option disabled">
                <span>Stripe</span><span class="badge">Em breve</span>
            </div>
        </div>
    </div>
</body>
</html>