<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $cpf = $_POST['cpf'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validação básica
    if (empty($full_name) || empty($email) || empty($cpf) || empty($username) || empty($password)) {
        $_SESSION['register_error'] = "Por favor, preencha todos os campos.";
        header("Location: register.php");
        exit();
    }

    // Verificar se o usuário, e-mail ou CPF já existem
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? OR cpf = ?");
    $stmt->bind_param("sss", $username, $email, $cpf);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['register_error'] = "Usuário, e-mail ou CPF já cadastrado.";
        header("Location: register.php");
        exit();
    }
    $stmt->close();

    // Hash da senha
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Inserir novo usuário com a role 'cliente' por padrão
    $stmt = $conn->prepare("INSERT INTO users (username, full_name, email, cpf, password, role) VALUES (?, ?, ?, ?, ?, 'cliente')");
    $stmt->bind_param("sssss", $username, $full_name, $email, $cpf, $password_hash);

    if ($stmt->execute()) {
        // Mensagem de sucesso para a página de login
        $_SESSION['login_message'] = "Cadastro realizado com sucesso! Faça o login.";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['register_error'] = "Ocorreu um erro ao criar a conta.";
        header("Location: register.php");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: register.php");
    exit();
}
?>
