<?php
session_start();
require 'db.php';

// Proteção: Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'update_status') {
    $payment_id = $_POST['payment_id'] ?? 0;
    $new_status = $_POST['new_status'] ?? '';

    // Lista de status permitidos para segurança
    $allowed_statuses = ['initiated', 'pending', 'approved', 'rejected', 'in_process', 'authorized', 'refunded', 'charged_back', 'cancelled'];

    if ($payment_id && in_array($new_status, $allowed_statuses)) {
        try {
            $stmt = $conn->prepare("UPDATE payments SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $payment_id);
            $stmt->execute();
            $_SESSION['feedback'] = "Status do pagamento ID #{$payment_id} atualizado para '{$new_status}' com sucesso!";
        } catch (Exception $e) {
            $_SESSION['feedback'] = "Erro ao atualizar o pagamento: " . $e->getMessage();
        }
    } else {
        $_SESSION['feedback'] = "Erro: Dados inválidos para atualização do pagamento.";
    }
}

header("Location: dashboard_admin.php");
exit();
?>