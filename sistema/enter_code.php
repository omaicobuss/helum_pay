<?php
session_start();
if (!isset($_SESSION['email_for_code_login'])) {
    header("Location: login_code.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Insira o Código de Acesso</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Insira o Código de Acesso</h2>
        <p>Um código de 6 dígitos foi enviado para o e-mail: <strong><?php echo htmlspecialchars($_SESSION['email_for_code_login']); ?></strong></p>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error-message">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
        <form action="handle_enter_code.php" method="post">
            <div class="input-group">
                <label for="code">Código de Acesso</label>
                <input type="text" id="code" name="code" maxlength="6" required>
            </div>
            <button type="submit" class="btn">Verificar Código</button>
        </form>
        <div class="links">
            <a href="login_code.php">Reenviar código</a>
        </div>
    </div>
</body>
</html>
