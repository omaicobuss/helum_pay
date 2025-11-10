<?php
ob_start();
session_start();
require 'db.php';

$token_valid = false;
$error_message = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verificar se o token existe e não expirou
    $stmt = $conn->prepare("SELECT email, expires FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $reset = $result->fetch_assoc();
        if ($reset['expires'] >= date("U")) {
            $token_valid = true;
            $_SESSION['reset_token'] = $token; // Armazena o token na sessão
            $_SESSION['reset_email'] = $reset['email'];
        } else {
            $error_message = "Este link de redefinição de senha expirou.";
        }
    } else {
        $error_message = "Link de redefinição de senha inválido.";
    }
} else {
    $error_message = "Nenhum token de redefinição fornecido.";
}

// Lógica para processar o formulário de nova senha
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['reset_token'])) {
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if ($password !== $password_confirm) {
        $error_message = "As senhas não coincidem.";
    } else {
        // Hash da nova senha
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Iniciar uma transação para garantir a atomicidade
        $conn->begin_transaction();

        try {
            // Atualizar a senha do usuário no banco de dados
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            if (!$update_stmt) throw new Exception("Erro ao preparar a atualização de senha.");
            $update_stmt->bind_param("ss", $password_hash, $_SESSION['reset_email']);
            if (!$update_stmt->execute()) throw new Exception("Erro ao executar a atualização de senha.");
            $update_stmt->close();

            // Deletar o token de redefinição para que não seja usado novamente
            $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            if (!$delete_stmt) throw new Exception("Erro ao preparar a exclusão do token.");
            $delete_stmt->bind_param("s", $_SESSION['reset_email']);
            if (!$delete_stmt->execute()) throw new Exception("Erro ao executar a exclusão do token.");
            $delete_stmt->close();

            // Se tudo deu certo, comitar a transação
            $conn->commit();

            // Limpar a sessão e redirecionar para o login com mensagem de sucesso
            unset($_SESSION['reset_token'], $_SESSION['reset_email']);
            $_SESSION['login_message'] = "Sua senha foi redefinida com sucesso! Faça o login.";
            header("Location: index.php");
            exit();

        } catch (Exception $e) {
            // Se algo deu errado, reverter a transação
            $conn->rollback();
            error_log("Erro na transação de redefinição de senha: " . $e->getMessage());
            $error_message = "Ocorreu um erro crítico ao atualizar sua senha. Por favor, tente novamente.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - Helum Pay</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h1>Crie uma Nova Senha</h1>

        <?php if ($token_valid): ?>
            <p>Por favor, insira e confirme sua nova senha.</p>
            <?php if ($error_message): ?>
                <div class="error-message"><?= $error_message ?></div>
            <?php endif; ?>

            <form action="reset_password.php?token=<?= htmlspecialchars($token) ?>" method="POST">
                <div class="input-group">
                    <label for="password">Nova Senha</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="input-group">
                    <label for="password_confirm">Confirme a Nova Senha</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>
                <button type="submit" class="btn-login">Redefinir Senha</button>
            </form>

        <?php else: ?>
            <div class="error-message"><?= $error_message ?></div>
            <p><a href="forgot_password.php" style="color: #1e90ff;">Tente novamente</a></p>
        <?php endif; ?>

    </div>
</body>
</html>