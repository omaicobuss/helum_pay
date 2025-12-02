<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['email_for_code_login'])) {
    $email = $_SESSION['email_for_code_login'];
    $code = $_POST['code'];

    $stmt = $conn->prepare("SELECT * FROM login_codes WHERE email = ? AND code = ? AND expires_at > NOW()");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Código válido, logar o usuário
        $stmt_user = $conn->prepare("SELECT id, username, role, full_name, email_verified, status FROM users WHERE email = ?");
        $stmt_user->bind_param("s", $email);
        $stmt_user->execute();
        $user_result = $stmt_user->get_result();
        $user = $user_result->fetch_assoc();

        if ($user) {
             if ($user['status'] !== 'active') {
                $_SESSION['error_message'] = 'Sua conta está inativa. Entre em contato com o suporte.';
                header("Location: login.php");
                exit();
            }
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email_verified'] = $user['email_verified'];

            // Limpar códigos usados
            $stmt_delete = $conn->prepare("DELETE FROM login_codes WHERE email = ?");
            $stmt_delete->bind_param("s", $email);
            $stmt_delete->execute();
            unset($_SESSION['email_for_code_login']);

            // Redirecionar para o dashboard apropriado
            if ($_SESSION['role'] == 'admin') {
                header("Location: dashboard_admin.php");
            } else {
                header("Location: dashboard_cliente.php");
            }
            exit();
        } else {
            $_SESSION['error_message'] = "Erro ao encontrar dados do usuário.";
            header("Location: enter_code.php");
            exit();
        }
    } else {
        // Código inválido ou expirado
        $_SESSION['error_message'] = "Código inválido ou expirado. Tente novamente.";
        header("Location: enter_code.php");
        exit();
    }
} else {
    header("Location: login_code.php");
    exit();
}
?>
