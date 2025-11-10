<?php
session_start();

// Verifica se o usuário está logado e se é um cliente
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cliente') {
    header("Location: index.php");
    exit();
}

require 'db.php';

$user_id = $_SESSION['user_id'];

// Buscar assinaturas do cliente
$subscriptions_sql = "
    SELECT s.id, s.next_due_date, s.status, s.notes, p.name as product_name
    FROM subscriptions s
    JOIN products p ON s.product_id = p.id
    WHERE s.user_id = ?
    ORDER BY s.next_due_date ASC";
$stmt_subs = $conn->prepare($subscriptions_sql);
$stmt_subs->bind_param("i", $user_id);
$stmt_subs->execute();
$subscriptions_result = $stmt_subs->get_result();

// Buscar histórico de pagamentos do cliente
$payments_sql = "
    SELECT py.payment_date, py.amount, p.name as product_name
    FROM payments py
    JOIN subscriptions s ON py.subscription_id = s.id
    JOIN products p ON s.product_id = p.id
    WHERE s.user_id = ?
    ORDER BY py.payment_date DESC";
$stmt_pays = $conn->prepare($payments_sql);
$stmt_pays->bind_param("i", $user_id);
$stmt_pays->execute();
$payments_result = $stmt_pays->get_result();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Cliente - Helum Pay</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard">
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Bem-vindo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <a href="logout.php" class="logout-btn">Sair</a>
        </div>

        <div class="section">
            <h2>Meus Produtos e Serviços</h2>
            <form action="invoice.php" method="POST" id="payment-form">
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>Produto/Serviço</th>
                                <th>Próximo Vencimento</th>
                                <th>Status</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Re-executar a query para ter os dados de preço
                            $stmt_subs->execute();
                            $subscriptions_result = $stmt_subs->get_result();
                            if ($subscriptions_result->num_rows > 0): 
                                // Adicionar a busca do preço na query
                                $subscriptions_sql_price = "
                                    SELECT s.id, s.next_due_date, s.status, s.notes, p.name as product_name, p.price
                                    FROM subscriptions s
                                    JOIN products p ON s.product_id = p.id
                                    WHERE s.user_id = ?
                                    ORDER BY s.next_due_date ASC";
                                $stmt_subs_price = $conn->prepare($subscriptions_sql_price);
                                $stmt_subs_price->bind_param("i", $user_id);
                                $stmt_subs_price->execute();
                                $subscriptions_result_price = $stmt_subs_price->get_result();

                                while($row = $subscriptions_result_price->fetch_assoc()):
                                    $isPayable = in_array($row['status'], ['active', 'pending']);
                                    $status_class = 'status-' . strtolower(htmlspecialchars($row['status']));
                            ?>
                                    <tr>
                                        <td>
                                            <?php if ($isPayable): ?>
                                                <input type="checkbox" class="subscription-checkbox" name="subscription_ids[]" value="<?php echo $row['id']; ?>" data-price="<?php echo $row['price']; ?>">
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['product_name']); ?></strong>
                                            <?php if (!empty($row['notes'])): ?>
                                                <span class="notes"><?php echo htmlspecialchars($row['notes']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date("d/m/Y", strtotime($row['next_due_date'])); ?></td>
                                        <td><span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst(htmlspecialchars($row['status'])); ?></span></td>
                                        <td>R$ <?php echo number_format($row['price'], 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center;">Nenhum produto ou serviço encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="payment-summary">
                    <div class="total-selected">
                        <strong>Total Selecionado:</strong>
                        <span id="total-amount">R$ 0,00</span>
                    </div>
                    <button type="submit" id="pay-selected-btn" class="btn-pay" disabled>Pagar Selecionados</button>
                </div>
            </form>
        </div>

        <div class="accordion">
            <!-- Item do Accordion: Histórico de Pagamentos -->
            <div class="accordion-item">
                <button class="accordion-header">
                    <h2>Histórico de Pagamentos</h2>
                    <span class="accordion-icon">+</span>
                </button>
                <div class="accordion-content">
                    <table>
                        <thead>
                            <tr><th>Produto</th><th>Data do Pagamento</th><th>Valor</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($payments_result->num_rows > 0): ?>
                                <?php while($row = $payments_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                        <td><?php echo date("d/m/Y", strtotime($row['payment_date'])); ?></td>
                                        <td>R$ <?php echo number_format($row['amount'], 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3" style="text-align: center;">Nenhum pagamento encontrado.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Item do Accordion: Alterar Senha -->
            <div class="accordion-item">
                <button class="accordion-header">
                    <h2>Alterar Senha</h2>
                    <span class="accordion-icon">+</span>
                </button>
                <div class="accordion-content">
                    <?php
                    if (isset($_SESSION['password_change_error'])) {
                        echo '<div class="error-message">' . $_SESSION['password_change_error'] . '</div>';
                        unset($_SESSION['password_change_error']);
                    }
                    if (isset($_SESSION['password_change_success'])) {
                        echo '<div class="success-message">' . $_SESSION['password_change_success'] . '</div>';
                        unset($_SESSION['password_change_success']);
                    }
                    ?>
                    <form action="handle_change_password.php" method="POST">
                        <div class="input-group"><label for="current_password">Senha Atual</label><input type="password" id="current_password" name="current_password" required></div>
                        <div class="input-group"><label for="new_password">Nova Senha</label><input type="password" id="new_password" name="new_password" required></div>
                        <div class="input-group"><label for="confirm_new_password">Confirme a Nova Senha</label><input type="password" id="confirm_new_password" name="confirm_new_password" required></div>
                        <button type="submit" class="btn-login">Alterar Senha</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all');
            const subscriptionCheckboxes = document.querySelectorAll('.subscription-checkbox');
            const totalAmountSpan = document.getElementById('total-amount');
            const paySelectedBtn = document.getElementById('pay-selected-btn');
            const paymentForm = document.getElementById('payment-form');

            function updateSelection() {
                let total = 0;
                let selectedCount = 0;
                subscriptionCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        total += parseFloat(checkbox.dataset.price);
                        selectedCount++;
                    }
                });

                totalAmountSpan.textContent = 'R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                paySelectedBtn.disabled = selectedCount === 0;
            }

            selectAllCheckbox.addEventListener('change', function() {
                subscriptionCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                updateSelection();
            });

            subscriptionCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (!checkbox.checked) {
                        selectAllCheckbox.checked = false;
                    } else {
                        const allChecked = Array.from(subscriptionCheckboxes).every(c => c.checked);
                        selectAllCheckbox.checked = allChecked;
                    }
                    updateSelection();
                });
            });

            paymentForm.addEventListener('submit', function(e) {
                const selectedCount = Array.from(subscriptionCheckboxes).filter(c => c.checked).length;
                if (selectedCount === 0) {
                    e.preventDefault();
                    alert('Por favor, selecione ao menos uma fatura para pagar.');
                }
            });

            // Accordion logic
            document.querySelectorAll('.accordion-header').forEach(header => {
                header.addEventListener('click', () => {
                    const content = header.nextElementSibling;
                    header.classList.toggle('active');
                    content.style.display = content.style.display === 'block' ? 'none' : 'block';
                });
            });
        });
    </script>
</body>
</html>
<?php
$stmt_subs->close();
$stmt_pays->close();
$conn->close();
?>
