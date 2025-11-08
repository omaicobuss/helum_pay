<?php
session_start();
require 'db.php';

// Proteção: Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'create':
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;

        if ($name && $price > 0) {
            $stmt = $conn->prepare("INSERT INTO products (name, description, price) VALUES (?, ?, ?)");
            $stmt->bind_param("ssd", $name, $description, $price);
            $stmt->execute();
            $_SESSION['product_feedback'] = "Produto adicionado com sucesso!";
        }
        break;

    case 'update':
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;

        if ($id && $name && $price > 0) {
            $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ? WHERE id = ?");
            $stmt->bind_param("ssdi", $name, $description, $price, $id);
            $stmt->execute();
            $_SESSION['product_feedback'] = "Produto atualizado com sucesso!";
        }
        break;

    case 'delete':
        $id = $_GET['id'] ?? 0;
        if ($id) {
            // Adicionar verificação se o produto está em alguma assinatura antes de excluir
            $stmt_check = $conn->prepare("SELECT id FROM subscriptions WHERE product_id = ?");
            $stmt_check->bind_param("i", $id);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $_SESSION['product_feedback'] = "Erro: Não é possível excluir um produto que está associado a uma assinatura ativa.";
            } else {
                $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $_SESSION['product_feedback'] = "Produto excluído com sucesso!";
            }
        }
        break;
}

header("Location: dashboard_admin.php");
exit();
?>
