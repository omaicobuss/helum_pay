<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Novos campos do formulário
    $user_type = $_POST['user_type'];
    $company_name = isset($_POST['company_name']) ? $_POST['company_name'] : null;
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $document = $_POST['document'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validação básica
    if (empty($user_type) || empty($full_name) || empty($email) || empty($document) || empty($username) || empty($password)) {
        $_SESSION['register_error'] = "Por favor, preencha todos os campos obrigatórios.";
        header("Location: register.php");
        exit();
    }

    // Validação para pessoa jurídica
    if ($user_type === 'juridica' && empty($company_name)) {
        $_SESSION['register_error'] = "A Razão Social é obrigatória para Pessoa Jurídica.";
        header("Location: register.php");
        exit();
    }

    // Verificar se o usuário, e-mail ou documento já existem
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? OR document = ?");
    $stmt->bind_param("sss", $username, $email, $document);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['register_error'] = "Usuário, e-mail ou documento (CPF/CNPJ) já cadastrado.";
        header("Location: register.php");
        exit();
    }
    $stmt->close();

    // Hash da senha
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Inserir novo usuário com a role 'cliente' por padrão
    // Note a mudança na query e nos parâmetros
    $stmt = $conn->prepare("INSERT INTO users (username, full_name, user_type, company_name, email, document, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'cliente')");
    $stmt->bind_param("sssssss", $username, $full_name, $user_type, $company_name, $email, $document, $password_hash);

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
