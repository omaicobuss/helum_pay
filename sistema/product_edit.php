<?php
session_start();
require 'db.php';

// Proteção: Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$product_id = $_GET['id'] ?? 0;

if (!$product_id) {
    header("Location: dashboard_admin.php");
    exit();
}

// Buscar dados do produto
$stmt = $conn->prepare("SELECT full_name, description, price FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: dashboard_admin.php");
    exit();
}
$product = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto - Helum Pay</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; height: 100vh; }
        .edit-container { background-color: #162447; padding: 40px; border-radius: 10px; width: 100%; max-width: 600px; }
        h1 { color: #fff; border-bottom: 2px solid #1e90ff; padding-bottom: 10px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #e0e0e0; }
        input, textarea { padding: 10px; border-radius: 5px; border: 1px solid #1f4068; background-color: #1b2a49; color: #fff; width: 100%; box-sizing: border-box; }
        .btn-save { background-color: #1e90ff; color: #fff; border: none; padding: 12px; cursor: pointer; width: 100%; }
        .link-cancel { display: block; text-align: center; margin-top: 15px; color: #1e90ff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="edit-container">
        <h1>Editar Produto</h1>
        <form action="handle_product.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo $product_id; ?>">
            <div class="form-group">
                <label for="full_name">Nome do Produto</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($product['full_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Descrição</label>
                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="price">Preço</label>
                <input type="number" step="0.01" id="price" name="price" value="<?php echo $product['price']; ?>" required>
            </div>
            <button type="submit" class="btn-save">Salvar Alterações</button>
            <a href="dashboard_admin.php" class="link-cancel">Cancelar</a>
        </form>
    </div>
</body>
</html>
