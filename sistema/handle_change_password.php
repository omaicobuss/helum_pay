
<?php
session_start();
require 'db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // 1. Validar se a nova senha e a confirmação são iguais
    if ($new_password !== $confirm_new_password) {
        $_SESSION['password_change_error'] = "A nova senha e a confirmação não coincidem.";
        header("Location: dashboard_cliente.php");
        exit();
    }

    // 2. Buscar a senha atual do usuário no banco de dados
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // 3. Verificar se a senha atual fornecida está correta
    if (password_verify($current_password, $user['password'])) {
        // 4. Se estiver correta, criar o hash da nova senha
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // 5. Atualizar a senha no banco de dados
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_password_hash, $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['password_change_success'] = "Senha alterada com sucesso!";
        } else {
            $_SESSION['password_change_error'] = "Ocorreu um erro ao alterar a senha.";
        }
        $update_stmt->close();

    } else {
        $_SESSION['password_change_error'] = "A senha atual está incorreta.";
    }

    $stmt->close();
    $conn->close();

    header("Location: dashboard_cliente.php");
    exit();

} else {
    header("Location: dashboard_cliente.php");
    exit();
}
?>
