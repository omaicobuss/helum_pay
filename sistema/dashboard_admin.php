<?php
session_start();
require_once 'db.php';

// Proteção de Rota: Apenas administradores podem acessar.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// --- Recuperação de Dados ---
// Centraliza a busca de dados no início do script.

// Busca todos os usuários
$users = $conn->query("SELECT id, username, full_name, email, user_type, document, role FROM users ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);

// Busca todos os produtos
$products = $conn->query("SELECT id, name, description, price FROM products ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);

// Busca todas as assinaturas com detalhes do usuário e produto
$subscriptions_sql = "
    SELECT s.id, u.username, p.name as product_name, s.next_due_date, s.status, s.notes
    FROM subscriptions s
    JOIN users u ON s.user_id = u.id
    JOIN products p ON s.product_id = p.id
    ORDER BY s.id ASC";
$subscriptions = $conn->query($subscriptions_sql)->fetch_all(MYSQLI_ASSOC);

// Busca os últimos 20 logs de webhook
$webhook_logs_sql = "
    SELECT id, received_at, processing_status, headers, query_params, body 
    FROM webhook_logs ORDER BY id DESC LIMIT 20";
$webhook_logs = $conn->query($webhook_logs_sql)->fetch_all(MYSQLI_ASSOC);

// Busca todos os pagamentos com detalhes
$payments_sql = "
    SELECT pay.id, pay.payment_date, pay.amount, pay.status, u.username, p.name as product_name
    FROM payments pay
    JOIN subscriptions s ON pay.subscription_id = s.id
    JOIN users u ON s.user_id = u.id
    JOIN products p ON s.product_id = p.id
    ORDER BY pay.id DESC";
$payments = $conn->query($payments_sql)->fetch_all(MYSQLI_ASSOC);

// Mensagem de feedback (ex: após uma operação)
$feedback_message = '';
if (isset($_SESSION['feedback'])) {
    $feedback_message = '<div class="success-message">' . htmlspecialchars($_SESSION['feedback']) . '</div>';
    unset($_SESSION['feedback']);
}
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
        /* 
         * Nota de Refatoração:
         * Estes estilos são específicos para esta página. Para melhor manutenção,
         * eles poderiam ser movidos para um arquivo CSS separado, como 'dashboard_styles.css',
         * e incluídos no <head>. Por enquanto, foram mantidos aqui para garantir
         * que o layout não seja quebrado.
         */
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
        <div class="dashboard-header">
            <h1>Painel do Administrador</h1>
            <a href="logout.php">Sair</a>
        </div>

        <?php echo $feedback_message; ?>

        <div class="tabs">
            <button class="tab-link active" onclick="openTab(event, 'users')">Usuários</button>
            <button class="tab-link" onclick="openTab(event, 'products')">Produtos</button>
            <button class="tab-link" onclick="openTab(event, 'subscriptions')">Assinaturas</button>
            <button class="tab-link" onclick="openTab(event, 'payments')">Pagamentos</button>
            <button class="tab-link" onclick="openTab(event, 'logs')">Logs de Webhook</button>
        </div>

        <!-- Inclusão das Abas -->
        <?php include '_admin_tabs/users.php'; ?>
        <?php include '_admin_tabs/products.php'; ?>
        <?php include '_admin_tabs/subscriptions.php'; ?>
        <?php include '_admin_tabs/payments.php'; ?>
        <?php include '_admin_tabs/logs.php'; ?>
    </div>

    <!-- Inclusão dos Modais -->
    <?php include '_admin_modals/email_modal.php'; ?>
    <?php include '_admin_modals/subscription_email_modal.php'; ?>
    <?php include '_admin_modals/payment_email_modal.php'; ?>

    <!-- Inclusão do JavaScript -->
    <script src="js/dashboard_admin.js" defer></script>
</body>
</html>
<?php
// Fecha a conexão com o banco de dados
$conn->close();
?>
