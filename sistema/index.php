<?php
ob_start(); // Inicia o buffer de saída
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Helum Pay</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <img src="logo.png" alt="Logo Helum Pay" style="max-width: 150px; margin-bottom: 20px;">
        <h1>HELUM PAY</h1>
        <p>Soluções para seus negócios digitais.</p>

        <?php
        if (isset($_SESSION['login_error'])) {
            echo '<div class="error-message">' . $_SESSION['login_error'] . '</div>';
            unset($_SESSION['login_error']); // Limpa a mensagem de erro da sessão
        }
        if (isset($_SESSION['login_message'])) {
            echo '<div class="success-message">' . $_SESSION['login_message'] . '</div>';
            unset($_SESSION['login_message']);
        }
        ?>

        <form action="login.php" method="POST">
            <div class="input-group">
                <label for="username">Usuário</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Entrar</button>
        </form>
        <p style="margin-top: 10px;"><a href="forgot_password.php" style="color: #1e90ff;">Esqueci minha senha</a></p>
        <p style="margin-top: 20px;">Não tem uma conta? <a href="register.php" style="color: #1e90ff;">Cadastre-se</a></p>
    </div>
</body>
</html>
