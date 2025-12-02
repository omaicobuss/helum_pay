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
    <style>
        .back-link {
            display: inline-block;
            margin-top: 15px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="logo.png" alt="Logo Helum Pay" style="max-width: 150px; margin-bottom: 20px;">
        <h1>HELUM PAY</h1>
        <p>Soluções para seus negócios digitais.</p>

        <?php
        // Exibe mensagens de erro ou sucesso da sessão
        if (isset($_SESSION['login_error'])) {
            echo '<div class="error-message">' . $_SESSION['login_error'] . '</div>';
            unset($_SESSION['login_error']);
        }
        if (isset($_SESSION['login_message'])) {
            echo '<div class="success-message">' . $_SESSION['login_message'] . '</div>';
            unset($_SESSION['login_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <!-- Opções Iniciais de Login -->
        <div id="initial-options">
            <button id="show-password-login" class="btn-login" style="margin-bottom: 10px;">Acessar com login e senha</button>
            <button id="show-code-login" class="btn-login">Acessar com um código único no seu e-mail</button>
            <p style="margin-top: 20px;">Não tem uma conta? <a href="register.php">Cadastre-se</a></p>
        </div>

        <!-- Formulário de Login com Senha (Oculto) -->
        <div id="password-login-form" style="display: none;">
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
            <p style="margin-top: 10px;"><a href="forgot_password.php">Esqueci minha senha</a></p>
            <a class="back-link">← Voltar</a>
        </div>

        <!-- Formulário de Login com Código (Oculto) -->
        <div id="code-login-form" style="display: none;">
            <h2>Login com Código Único</h2>
            <p>Insira seu e-mail para receber um código de acesso.</p>
            <form action="handle_login_code.php" method="post">
                <div class="input-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <button type="submit" class="btn-login">Enviar Código</button>
            </form>
            <a class="back-link">← Voltar</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const initialOptions = document.getElementById('initial-options');
            const passwordForm = document.getElementById('password-login-form');
            const codeForm = document.getElementById('code-login-form');

            const showPasswordBtn = document.getElementById('show-password-login');
            const showCodeBtn = document.getElementById('show-code-login');
            const backLinks = document.querySelectorAll('.back-link');

            showPasswordBtn.addEventListener('click', function() {
                initialOptions.style.display = 'none';
                passwordForm.style.display = 'block';
            });

            showCodeBtn.addEventListener('click', function() {
                initialOptions.style.display = 'none';
                codeForm.style.display = 'block';
            });

            backLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    passwordForm.style.display = 'none';
                    codeForm.style.display = 'none';
                    initialOptions.style.display = 'block';
                });
            });
        });
    </script>
</body>
</html>
