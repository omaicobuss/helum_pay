<?php
session_start();
require 'db.php';

// Proteção: Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$subscription_id = $_GET['id'] ?? 0;
if (!$subscription_id) {
    header("Location: dashboard_admin.php");
    exit();
}

// Buscar dados da assinatura, incluindo o novo campo 'notes'
$stmt = $conn->prepare("SELECT user_id, product_id, next_due_date, status, notes FROM subscriptions WHERE id = ?");
$stmt->bind_param("i", $subscription_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: dashboard_admin.php");
    exit();
}
$subscription = $result->fetch_assoc();

// Buscar todos os usuários e produtos para os dropdowns
$users = $conn->query("SELECT id, username FROM users ORDER BY username ASC")->fetch_all(MYSQLI_ASSOC);
$products = $conn->query("SELECT id, name FROM products ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Assinatura - Helum Pay</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; padding: 40px 0; }
        .edit-container { background-color: #162447; padding: 40px; border-radius: 10px; width: 100%; max-width: 600px; }
        h1 { color: #fff; border-bottom: 2px solid #1e90ff; padding-bottom: 10px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #e0e0e0; }
        select, input, textarea { padding: 10px; border-radius: 5px; border: 1px solid #1f4068; background-color: #1b2a49; color: #fff; width: 100%; box-sizing: border-box; }
        .btn-save { background-color: #1e90ff; color: #fff; border: none; padding: 12px; cursor: pointer; width: 100%; }
        .link-cancel { display: block; text-align: center; margin-top: 15px; color: #1e90ff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="edit-container">
        <h1>Editar Assinatura</h1>
        <form action="handle_subscription.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo $subscription_id; ?>">

            <div class="form-group">
                <label for="user_id">Usuário</label>
                <select id="user_id" name="user_id" required>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo ($user['id'] == $subscription['user_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="product_id">Produto</label>
                <select id="product_id" name="product_id" required>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['id']; ?>" <?php echo ($product['id'] == $subscription['product_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($product['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="next_due_date">Próximo Vencimento</label>
                <input type="date" id="next_due_date" name="next_due_date" value="<?php echo $subscription['next_due_date']; ?>" required>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="active" <?php echo ($subscription['status'] == 'active') ? 'selected' : ''; ?>>Ativa</option>
                    <option value="paid" <?php echo ($subscription['status'] == 'paid') ? 'selected' : ''; ?>>Paga</option>
                    <option value="canceled" <?php echo ($subscription['status'] == 'canceled') ? 'selected' : ''; ?>>Cancelada</option>
                </select>
            </div>

            <div class="form-group">
                <label for="notes">Notas Complementares</label>
                <textarea id="notes" name="notes" rows="4"><?php echo htmlspecialchars($subscription['notes']); ?></textarea>
            </div>

            <button type="submit" class="btn-save">Salvar Alterações</button>
            <a href="dashboard_admin.php" class="link-cancel">Cancelar</a>
        </form>
    </div>
</body>
</html>