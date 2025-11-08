<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $login_success = false;

        // 1. Tenta verificar a senha com o hash existente
        if (password_verify($password, $user['password'])) {
            $login_success = true;
        } 
        // 2. Se falhar, verifica se é um dos usuários iniciais com a senha padrão
        else if (
            ($user['username'] === 'admin' && $password === 'admin123') ||
            ($user['username'] === 'cliente' && $password === 'cliente123')
        ) {
            // 3. Se for, cria um novo hash e atualiza o banco
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_hash, $user['id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            $login_success = true;
        }

        if ($login_success) {
            // Inicia a sessão
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user['role'];

            // Redireciona com base no perfil
            if ($user['role'] == 'admin') {
                header("Location: dashboard_admin.php");
            } else {
                header("Location: dashboard_cliente.php");
            }
            exit();
        }
    }

    // Se chegou até aqui, o login falhou
    $_SESSION['login_error'] = "Usuário ou senha inválidos.";
    header("Location: index.php");
    exit();

    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
?>
