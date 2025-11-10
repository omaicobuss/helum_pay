<?php
ob_start();
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci Minha Senha - Helum Pay</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h1>Recuperar Senha</h1>
        <p>Informe seu e-mail para receber as instruções de recuperação.</p>

        <?php
        if (isset($_SESSION['reset_error'])) {
            echo '<div class="error-message">' . $_SESSION['reset_error'] . '</div>';
            unset($_SESSION['reset_error']);
        }
        if (isset($_SESSION['reset_message'])) {
            echo '<div class="success-message">' . $_SESSION['reset_message'] . '</div>';
            unset($_SESSION['reset_message']);
        }
        ?>

        <form action="handle_forgot_password.php" method="POST">
            <div class="input-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit" class="btn-login">Enviar</button>
        </form>
        <p style="margin-top: 20px;">Lembrou a senha? <a href="index.php" style="color: #1e90ff;">Faça login</a></p>
    </div>
</body>
</html>