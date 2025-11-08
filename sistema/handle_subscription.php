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
        $user_id = $_POST['user_id'] ?? 0;
        $product_id = $_POST['product_id'] ?? 0;
        $start_date = $_POST['start_date'] ?? '';
        $next_due_date = $_POST['next_due_date'] ?? '';
        $status = $_POST['status'] ?? '';
        $notes = $_POST['notes'] ?? '';

        if ($user_id && $product_id && $start_date && $next_due_date && $status) {
            $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, product_id, start_date, next_due_date, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissss", $user_id, $product_id, $start_date, $next_due_date, $status, $notes);
            $stmt->execute();
            $_SESSION['feedback'] = "Assinatura adicionada com sucesso!";
        }
        break;

    case 'update':
        $id = $_POST['id'] ?? 0;
        $user_id = $_POST['user_id'] ?? 0;
        $product_id = $_POST['product_id'] ?? 0;
        $next_due_date = $_POST['next_due_date'] ?? '';
        $status = $_POST['status'] ?? '';
        $notes = $_POST['notes'] ?? '';

        if ($id && $user_id && $product_id && $next_due_date && $status) {
            $stmt = $conn->prepare("UPDATE subscriptions SET user_id = ?, product_id = ?, next_due_date = ?, status = ?, notes = ? WHERE id = ?");
            $stmt->bind_param("iisssi", $user_id, $product_id, $next_due_date, $status, $notes, $id);
            $stmt->execute();
            $_SESSION['feedback'] = "Assinatura atualizada com sucesso!";
        }
        break;

    case 'delete':
        $id = $_GET['id'] ?? 0;
        if ($id) {
            // Primeiro, excluir pagamentos associados para evitar erro de chave estrangeira
            $stmt_del_payments = $conn->prepare("DELETE FROM payments WHERE subscription_id = ?");
            $stmt_del_payments->bind_param("i", $id);
            $stmt_del_payments->execute();

            // Agora, excluir a assinatura
            $stmt = $conn->prepare("DELETE FROM subscriptions WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $_SESSION['feedback'] = "Assinatura e pagamentos associados foram excluídos com sucesso!";
        }
        break;
}

header("Location: dashboard_admin.php");
exit();
?>