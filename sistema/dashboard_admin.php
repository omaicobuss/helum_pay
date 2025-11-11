<?php
session_start();
require 'db.php';

// Proteção: Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Mensagens de feedback da sessão
$feedback_message = '';
if (isset($_SESSION['feedback'])) {
    $feedback_message = '<div class="success-message">' . $_SESSION['feedback'] . '</div>';
    unset($_SESSION['feedback']);
}

// Buscar dados e armazenar em arrays
$users = $conn->query("SELECT id, username, full_name, email, user_type, document, role FROM users ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
$products = $conn->query("SELECT id, name, description, price FROM products ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
$subscriptions_sql = "
    SELECT s.id, u.username, p.name as product_name, s.next_due_date, s.status, s.notes
    FROM subscriptions s
    JOIN users u ON s.user_id = u.id
    JOIN products p ON s.product_id = p.id
    ORDER BY s.id ASC";
$subscriptions = $conn->query($subscriptions_sql)->fetch_all(MYSQLI_ASSOC);
$webhook_logs_sql = "
    SELECT id, received_at, processing_status, headers, query_params, body 
    FROM webhook_logs ORDER BY id DESC LIMIT 20";
$webhook_logs = $conn->query($webhook_logs_sql)->fetch_all(MYSQLI_ASSOC);
$payments_sql = "
    SELECT
        pay.id,
        pay.payment_date,
        pay.amount,
        pay.status,
        u.username,
        p.name as product_name
    FROM payments pay
    JOIN subscriptions s ON pay.subscription_id = s.id
    JOIN users u ON s.user_id = u.id
    JOIN products p ON s.product_id = p.id
    ORDER BY pay.id DESC";
$payments = $conn->query($payments_sql)->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Admin - Helum Pay</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; align-items: flex-start; padding-top: 20px; padding-bottom: 40px; background-color: #0d1117; color: #c9d1d9; }
        .dashboard-container { width: 100%; max-width: 1200px; margin: 0 auto; background: none; box-shadow: none; padding: 0;}
        .section { background-color: #161b22; border: 1px solid #30363d; padding: 30px; border-radius: 10px; }
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; color: #f0f6fc; padding: 0 10px; }
        .dashboard-header a { color: #58a6ff; text-decoration: none; font-weight: 500; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #30363d; text-align: left; vertical-align: middle; }
        th { background-color: #161b22; font-weight: 600; color: #8b949e; text-transform: uppercase; font-size: 12px; }
        h1, h2, h3 { color: #f0f6fc; border-bottom: 1px solid #30363d; padding-bottom: 10px; margin-bottom: 20px; font-weight: 600; }
        h3 { border-bottom: 1px solid #21262d; font-size: 1.1em; }
        .btn, select, input, textarea { padding: 10px 14px; border-radius: 6px; border: 1px solid #30363d; background-color: #0d1117; color: #c9d1d9; width: 100%; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        .btn-save { background-color: #238636; cursor: pointer; border: 1px solid #2ea043; font-weight: 500; }
        .btn-notify { background-color: #c99400; color: #fff; text-decoration: none; padding: 5px 10px; border-radius: 5px; font-size: 14px; border: none; }
        .btn-edit { background-color: #388bfd; color: #fff; text-decoration: none; padding: 5px 10px; border-radius: 5px; font-size: 14px; border: none; }
        .btn-delete { background-color: #da3633; color: #fff; text-decoration: none; padding: 5px 10px; border-radius: 5px; font-size: 14px; border: none; }
        .form-group { margin-bottom: 15px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .status { padding: 5px 10px; border-radius: 15px; color: #fff; font-size: 0.9em; white-space: nowrap; }
        .status-success { background-color: #28a745; }
        .status-failure { background-color: #dc3545; }
        .status-pending { background-color: #ffc107; color: #212529; }
        .status-info { background-color: #17a2b8; }
        .details-toggle { cursor: pointer; color: #58a6ff; text-decoration: none; }
        .details-content { display: none; background-color: #010409; padding: 15px; margin-top: 10px; border-radius: 6px; max-height: 300px; overflow-y: auto; border: 1px solid #30363d; }
        pre { white-space: pre-wrap; word-wrap: break-word; margin: 0; font-family: 'SF Mono', 'Consolas', 'Liberation Mono', Menlo, monospace; font-size: 13px; }
        .tabs { border-bottom: 1px solid #30363d; margin-bottom: 20px; }
        .tab-link { background-color: transparent; border: none; color: #8b949e; padding: 10px 16px; cursor: pointer; font-size: 14px; font-weight: 500; border-bottom: 2px solid transparent; margin-bottom: -1px; }
        .tab-link.active { color: #f0f6fc; border-bottom-color: #f78166; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        /* Estilos do Modal */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #161b22; margin: 15% auto; padding: 25px; border: 1px solid #30363d; width: 80%; max-width: 500px; border-radius: 8px; position: relative; }
        .close-button { color: #8b949e; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close-button:hover, .close-button:focus { color: #c9d1d9; text-decoration: none; outline: none; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header"><h1>Painel do Administrador</h1><a href="logout.php">Sair</a></div>
        <?php echo $feedback_message; ?>

        <div class="tabs">
            <button class="tab-link active" onclick="openTab(event, 'users')">Usuários</button>
            <button class="tab-link" onclick="openTab(event, 'products')">Produtos</button>
            <button class="tab-link" onclick="openTab(event, 'subscriptions')">Assinaturas</button>
            <button class="tab-link" onclick="openTab(event, 'payments')">Pagamentos</button>
            <button class="tab-link" onclick="openTab(event, 'logs')">Logs de Webhook</button>
        </div>

        <!-- Aba de Usuários -->
        <div id="users" class="tab-content active section">
            <h2>Gerenciar Usuários</h2>
            <table>
                <thead><tr><th>ID</th><th>Usuário</th><th>Nome</th><th>E-mail</th><th>Documento</th><th>Perfil</th><th>Ações</th></tr></thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                    <tr>
                        <form action="update_role.php" method="POST" style="display: contents;">
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['document']); ?> (<?php echo htmlspecialchars(strtoupper($user['user_type'])); ?>)</td>
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <td><select name="role"><option value="cliente" <?php echo ($user['role'] == 'cliente') ? 'selected' : ''; ?>>Cliente</option><option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option></select></td>
                            <td>
                                <button type="submit" class="btn-save">Salvar Perfil</button>
                                <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="btn-edit" style="margin-left: 5px;">Editar Usuário</a>
                                <button type="button" class="btn-notify" style="margin-left: 5px;" onclick="openEmailModal('<?php echo $user['id']; ?>')">Enviar E-mail</button>
                            </td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal de Envio de E-mail -->
        <div id="emailModal" class="modal">
            <div class="modal-content">
                <span class="close-button" onclick="closeEmailModal()">&times;</span>
                <h3>Enviar E-mail para Usuário</h3>
                <form action="handle_email.php" method="GET">
                    <input type="hidden" name="action" value="send_custom">
                    <input type="hidden" id="modalUserId" name="id" value="">
                    <div class="form-group">
                        <label for="template">Modelo de E-mail:</label>
                        <select id="template" name="template" class="form-control" required>
                            <option value="new_system_welcome">Boas-vindas ao Novo Sistema</option>
                            <!-- Outros templates podem ser adicionados aqui -->
                        </select>
                    </div>
                    <button type="submit" class="btn btn-save">Enviar</button>
                </form>
            </div>
        </div>

        <!-- Aba de Produtos -->
        <div id="products" class="tab-content section">
            <h2>Gerenciar Produtos</h2>
            <table>
                <thead><tr><th>ID</th><th>Nome</th><th>Preço</th><th>Ações</th></tr></thead>
                <tbody>
                    <?php foreach($products as $product): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td><?php echo htmlspecialchars($product['full_name']); ?></td>
                        <td>R$ <?php echo number_format($product['price'], 2, ',', '.'); ?></td>
                        <td><a href="product_edit.php?id=<?php echo $product['id']; ?>" class="btn-edit">Editar</a> <a href="handle_product.php?action=delete&id=<?php echo $product['id']; ?>" class="btn-delete" onclick="return confirmDelete();">Excluir</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h3>Adicionar Novo Produto</h3>
            <form action="handle_product.php" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-group"><input type="text" name="full_name" placeholder="Nome do Produto" required></div>
                <div class="form-group"><textarea name="description" placeholder="Descrição do Produto" rows="3"></textarea></div>
                <div class="form-group"><input type="number" step="0.01" name="price" placeholder="Preço (ex: 49.90)" required></div>
                <button type="submit" class="btn btn-save">Adicionar Produto</button>
            </form>
        </div>

        <!-- Aba de Assinaturas -->
        <div id="subscriptions" class="tab-content section">
            <h2>Gerenciar Assinaturas</h2>
            <table>
                <thead><tr><th>ID</th><th>Usuário</th><th>Produto</th><th>Próx. Vencimento</th><th>Status</th><th>Notas</th><th>Ações</th></tr></thead>
                <tbody>
                    <?php foreach($subscriptions as $sub): ?>
                    <tr>
                        <td><?php echo $sub['id']; ?></td>
                        <td><?php echo htmlspecialchars($sub['username']); ?></td>
                        <td><?php echo htmlspecialchars($sub['product_name']); ?></td>
                        <td><?php echo date("d/m/Y", strtotime($sub['next_due_date'])); ?></td>
                        <td><?php echo htmlspecialchars($sub['status']); ?></td>
                        <td style="white-space: pre-wrap;"><?php echo htmlspecialchars($sub['notes']); ?></td>
                        <td>
                            <button type="button" class="btn-notify" onclick="openSubscriptionEmailModal('<?php echo $sub['id']; ?>')">Notificar</button>
                            <a href="subscription_edit.php?id=<?php echo $sub['id']; ?>" class="btn-edit">Editar</a> 
                            <a href="handle_subscription.php?action=delete&id=<?php echo $sub['id']; ?>" class="btn-delete" onclick="return confirmDelete();">Excluir</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h3>Adicionar Nova Assinatura</h3>
            <form action="handle_subscription.php" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-grid">
                    <div class="form-group"><label>Usuário</label><select name="user_id" required><?php foreach($users as $user) echo "<option value='{$user['id']}'>".htmlspecialchars($user['username'])."</option>"; ?></select></div>
                    <div class="form-group"><label>Produto</label><select name="product_id" required><?php foreach($products as $product) echo "<option value='{$product['id']}'>".htmlspecialchars($product['full_name'])."</option>"; ?></select></div>
                    <div class="form-group"><label>Data de Início</label><input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required></div>
                    <div class="form-group"><label>Próximo Vencimento</label><input type="date" name="next_due_date" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" required></div>
                    <div class="form-group"><label>Status</label><select name="status" required><option value="active">Ativa</option><option value="paid">Paga</option><option value="canceled">Cancelada</option></select></div>
                </div>
                <div class="form-group">
                    <label>Notas Complementares</label>
                    <textarea name="notes" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-save">Adicionar Assinatura</button>
            </form>
        </div>

        <!-- Modal de E-mail de Assinatura -->
        <div id="subscriptionEmailModal" class="modal">
            <div class="modal-content">
                <span class="close-button" onclick="closeSubscriptionEmailModal()">&times;</span>
                <h3>Notificar Assinatura</h3>
                <form action="handle_email.php" method="GET">
                    <input type="hidden" name="action" value="notify_subscription">
                    <input type="hidden" id="modalSubscriptionId" name="id" value="">
                    <div class="form-group">
                        <label for="sub_template">Modelo de E-mail:</label>
                        <select id="sub_template" name="template" class="form-control" required>
                            <option value="invoice_available">Fatura Disponível</option>
                            <option value="invoice_due">Lembrete de Vencimento</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-save">Enviar Notificação</button>
                </form>
            </div>
        </div>

        <!-- Aba de Pagamentos -->
        <div id="payments" class="tab-content section">
            <h2>Gerenciar Pagamentos</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuário</th>
                        <th>Produto</th>
                        <th>Data</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($payments as $payment): ?>
                    <tr>
                        <form action="handle_payment.php" method="POST" style="display: contents;">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                            <td><?php echo $payment['id']; ?></td>
                            <td><?php echo htmlspecialchars($payment['username']); ?></td>
                            <td><?php echo htmlspecialchars($payment['product_name']); ?></td>
                            <td><?php echo $payment['payment_date'] ? date("d/m/Y H:i", strtotime($payment['payment_date'])) : 'N/A'; ?></td>
                            <td>R$ <?php echo number_format($payment['amount'], 2, ',', '.'); ?></td>
                            <td><span class="status status-info"><?php echo htmlspecialchars($payment['status']); ?></span></td>
                            <td>
                                <select name="new_status" style="width: auto; margin-right: 10px;">
                                    <option value="<?php echo $payment['status']; ?>" selected><?php echo ucfirst($payment['status']); ?></option>
                                    <option value="initiated">Iniciado</option><option value="pending">Pendente</option><option value="approved">Aprovado</option><option value="rejected">Rejeitado</option><option value="cancelled">Cancelado</option><option value="refunded">Devolvido</option></select>
                                <button type="submit" class="btn-save">Salvar</button>
                                <?php if ($payment['status'] === 'approved'): ?>
                                    <button type="button" class="btn-notify" style="margin-left: 10px;" onclick="openPaymentEmailModal('<?php echo $payment['id']; ?>')">Confirmar Pagamento</button>
                                <?php endif; ?>
                            </td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal de E-mail de Pagamento -->
        <div id="paymentEmailModal" class="modal">
            <div class="modal-content">
                <span class="close-button" onclick="closePaymentEmailModal()">&times;</span>
                <h3>Confirmar Pagamento</h3>
                <form action="handle_email.php" method="GET">
                    <input type="hidden" name="action" value="confirm_payment">
                    <input type="hidden" id="modalPaymentId" name="id" value="">
                    <div class="form-group">
                        <label for="payment_template">Modelo de E-mail:</label>
                        <select id="payment_template" name="template" class="form-control" required>
                            <option value="payment_confirmation">Confirmação de Pagamento</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-save">Enviar Confirmação</button>
                </form>
            </div>
        </div>

        <!-- Aba de Logs de Webhook -->
        <div id="logs" class="tab-content section">
            <h2>Logs de Webhook (20 mais recentes) <a href="admin/webhook_logs.php" style="font-size: 14px; float: right;">Ver todos</a></h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Recebido em</th>
                        <th>Status</th>
                        <th>Detalhes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($webhook_logs)): ?>
                        <tr><td colspan="4" style="text-align: center;">Nenhum log encontrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($webhook_logs as $log): ?>
                            <tr>
                                <td><?php echo $log['id']; ?></td>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($log['received_at'])); ?></td>
                                <td>
                                    <?php
                                    $status = $log['processing_status'] ?? 'PENDING';
                                    $class = 'status-info';
                                    if (strpos($status, 'SUCCESS') !== false) $class = 'status-success';
                                    if (strpos($status, 'FAIL') !== false || strpos($status, 'ERROR') !== false) $class = 'status-failure';
                                    if (strpos($status, 'PENDING') !== false || strpos($status, 'VALID') !== false) $class = 'status-pending';
                                    ?>
                                    <span class="status <?php echo $class; ?>"><?php echo htmlspecialchars($status); ?></span>
                                </td>
                                <td>
                                    <span class="details-toggle" onclick="toggleDetails('details-log-<?php echo $log['id']; ?>')">Mostrar/Ocultar</span>
                                </td>
                            </tr>
                            <tr class="details-row">
                                <td colspan="4">
                                    <div id="details-log-<?php echo $log['id']; ?>" class="details-content">
                                        <strong>Cabeçalhos (Headers):</strong>
                                        <pre><?php echo htmlspecialchars(json_encode(json_decode($log['headers']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                        <hr>
                                        <strong>Parâmetros da URL (Query Params):</strong>
                                        <pre><?php echo htmlspecialchars(json_encode(json_decode($log['query_params']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                        <hr>
                                        <strong>Corpo (Body):</strong>
                                        <pre><?php echo htmlspecialchars(json_encode(json_decode($log['body']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
                tabcontent[i].classList.remove("active");
            }
            tablinks = document.getElementsByClassName("tab-link");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            document.getElementById(tabName).style.display = "block";
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
        document.querySelector('.tab-link.active').click(); // Abre a primeira aba por padrão

        function toggleDetails(id) {
            const element = document.getElementById(id);
            element.style.display = (element.style.display === 'block') ? 'none' : 'block';
        }
        function confirmDelete() { return confirm('Você tem certeza que deseja excluir este item?'); }

        // Funções do Modal de E-mail de Usuário
        const emailModal = document.getElementById('emailModal');
        function openEmailModal(userId) {
            document.getElementById('modalUserId').value = userId;
            emailModal.style.display = 'block';
        }
        function closeEmailModal() {
            emailModal.style.display = 'none';
        }

        // Funções do Modal de E-mail de Assinatura
        const subscriptionEmailModal = document.getElementById('subscriptionEmailModal');
        function openSubscriptionEmailModal(subscriptionId) {
            document.getElementById('modalSubscriptionId').value = subscriptionId;
            subscriptionEmailModal.style.display = 'block';
        }
        function closeSubscriptionEmailModal() {
            subscriptionEmailModal.style.display = 'none';
        }

        // Funções do Modal de E-mail de Pagamento
        const paymentEmailModal = document.getElementById('paymentEmailModal');
        function openPaymentEmailModal(paymentId) {
            document.getElementById('modalPaymentId').value = paymentId;
            paymentEmailModal.style.display = 'block';
        }
        function closePaymentEmailModal() {
            paymentEmailModal.style.display = 'none';
        }

        // Fechar modais ao clicar fora
        window.onclick = function(event) {
            if (event.target == emailModal) closeEmailModal();
            if (event.target == subscriptionEmailModal) closeSubscriptionEmailModal();
            if (event.target == paymentEmailModal) closePaymentEmailModal();
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>