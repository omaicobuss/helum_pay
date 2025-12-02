<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login com Código Único</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Login com Código Único</h2>
        <p>Insira seu e-mail para receber um código de acesso.</p>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error-message">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        <form action="handle_login_code.php" method="post">
            <div class="input-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit" class="btn">Enviar Código</button>
        </form>
        <div class="links">
            <a href="login.php">Login com senha</a>
        </div>
    </div>
</body>
</html>
