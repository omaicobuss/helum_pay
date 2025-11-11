<?php
session_start();
require 'db.php';

// Proteção: Apenas administradores e método POST
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

// Coleta e validação dos dados do formulário
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$user_type = $_POST['user_type'] ?? 'fisica';
$company_name = ($user_type === 'juridica') ? ($_POST['company_name'] ?? null) : null;
$full_name = $_POST['full_name'] ?? '';
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$document = $_POST['document'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Validações
if (!$user_id) {
    $_SESSION['feedback'] = "Erro: ID de usuário inválido.";
    header("Location: dashboard_admin.php");
    exit();
}
if (empty($full_name) || empty($email) || empty($document) || empty($username)) {
    $_SESSION['feedback'] = "Erro: Preencha todos os campos obrigatórios.";
    header("Location: user_edit.php?id=" . $user_id);
    exit();
}
if ($user_type === 'juridica' && empty($company_name)) {
    $_SESSION['feedback'] = "Erro: A Razão Social é obrigatória para Pessoa Jurídica.";
    header("Location: user_edit.php?id=" . $user_id);
    exit();
}

// Verificar se o usuário ou e-mail já existem (excluindo o usuário atual)
$stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ? OR document = ?) AND id != ?");
$stmt->bind_param("sssi", $username, $email, $document, $user_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $_SESSION['feedback'] = "Erro: Usuário, e-mail ou documento já está em uso por outra conta.";
    header("Location: user_edit.php?id=" . $user_id);
    exit();
}
$stmt->close();

// Monta a query de atualização
$sql = "UPDATE users SET full_name = ?, email = ?, username = ?, document = ?, user_type = ?, company_name = ?";
$types = "ssssss";
$params = [$full_name, $email, $username, $document, $user_type, $company_name];

// Se uma nova senha foi fornecida, adiciona à query
if (!empty($password)) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $sql .= ", password = ?";
    $types .= "s";
    $params[] = $password_hash;
}

$sql .= " WHERE id = ?";
$types .= "i";
$params[] = $user_id;

// Executa a atualização
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $_SESSION['feedback'] = "Usuário #" . $user_id . " atualizado com sucesso!";
} else {
    $_SESSION['feedback'] = "Erro ao atualizar o usuário: " . $stmt->error;
}

$stmt->close();
$conn->close();

header("Location: dashboard_admin.php");
exit();
?>
