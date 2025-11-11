<?php
session_start();
require 'db.php';

// Proteção: Apenas clientes logados
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cliente') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$invoice_items = [];
$total_amount = 0;
$user_info = null;
$subscription_ids_for_form = [];

// Lógica para Múltiplas Faturas (via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscription_ids']) && is_array($_POST['subscription_ids'])) {
    $subscription_ids = array_map('intval', $_POST['subscription_ids']);
    if (empty($subscription_ids)) {
        header("Location: dashboard_cliente.php");
        exit();
    }
    $placeholders = implode(',', array_fill(0, count($subscription_ids), '?'));
    
    $sql = "
        SELECT s.id, s.next_due_date, p.name as product_name, p.price, u.full_name, u.document, u.user_type
        FROM subscriptions s
        JOIN products p ON s.product_id = p.id
        JOIN users u ON s.user_id = u.id
        WHERE s.user_id = ? AND s.id IN ($placeholders)
    ";
    $types = 'i' . str_repeat('i', count($subscription_ids));
    $params = array_merge([$user_id], $subscription_ids);

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $invoice_items[] = $row;
        $total_amount += (float)$row['price'];
        if (!$user_info) {
            $user_info = ['name' => $row['full_name'], 'document' => $row['document'], 'user_type' => $row['user_type']];
        }
    }
    $subscription_ids_for_form = $subscription_ids;
    $stmt->close();

// Lógica para Fatura Única (via GET)
} elseif (isset($_GET['id'])) {
    $subscription_id = (int)$_GET['id'];
    $sql = "
        SELECT s.id, s.next_due_date, p.full_name as product_name, p.price, u.full_name, u.document, u.user_type
        FROM subscriptions s
        JOIN products p ON s.product_id = p.id
        JOIN users u ON s.user_id = u.id
        WHERE s.id = ? AND s.user_id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $subscription_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $invoice_items[] = $row;
        $total_amount = (float)$row['price'];
        $user_info = ['name' => $row['full_name'], 'document' => $row['document'], 'user_type' => $row['user_type']];
        $subscription_ids_for_form[] = $subscription_id;
    }
    $stmt->close();
}

// Se nenhum item foi encontrado, redireciona
if (empty($invoice_items)) {
    header("Location: dashboard_cliente.php");
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisão da Fatura - Helum Pay</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard">
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <h1>HELUM PAY</h1>
                                Fatura #: <?php echo implode(', ', $subscription_ids_for_form); ?><br>
                                Data de Pagamento: <?php echo date("d/m/Y"); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr><td><strong>Cobrança para:</strong><br><?php echo htmlspecialchars($user_info['name']); ?><br><?php echo ($user_info['user_type'] === 'juridica' ? 'CNPJ' : 'CPF'); ?>: <?php echo htmlspecialchars($user_info['document']); ?></td></tr>
                    </table>
                </td>
            </tr>
            <tr class="heading"><td>Item</td><td>Preço</td></tr>
            <?php foreach ($invoice_items as $item): ?>
                <tr class="item">
                    <td><?php echo htmlspecialchars($item['product_name']); ?> (Ref: #<?php echo $item['id']; ?>)</td>
                    <td>R$ <?php echo number_format($item['price'], 2, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="total"><td></td><td><strong>Total: R$ <?php echo number_format($total_amount, 2, ',', '.'); ?></strong></td></tr>
        </table>
        
        <div class="payment-options-container">
            <h3>Confirmar e pagar com:</h3>
            <form action="create_multi_payment.php" method="POST" style="text-align: center; margin-bottom: 10px;">
                <?php foreach ($subscription_ids_for_form as $id): ?>
                    <input type="hidden" name="subscription_ids[]" value="<?php echo $id; ?>">
                <?php endforeach; ?>
                <button type="submit" class="payment-option active" style="border:none; width: auto; display: inline-block; cursor: pointer;">
                    <span>Pagar com Mercado Pago</span>
                </button>
            </form>

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