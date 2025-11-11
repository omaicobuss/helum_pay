
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Helum Pay</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h1>Crie sua Conta</h1>
        <p>Preencha os campos para se cadastrar.</p>

        <?php
        if (isset($_SESSION['register_error'])) {
            echo '<div class="error-message">' . $_SESSION['register_error'] . '</div>';
            unset($_SESSION['register_error']);
        }
        ?>

        <form action="handle_register.php" method="POST">
            <div class="input-group">
                <label>Tipo de Pessoa</label>
                <div style="display: flex; gap: 20px;">
                    <label for="user_type_fisica" style="display: flex; align-items: center; gap: 5px;"><input type="radio" id="user_type_fisica" name="user_type" value="fisica" checked onchange="toggleFields()"> Pessoa Física</label>
                    <label for="user_type_juridica" style="display: flex; align-items: center; gap: 5px;"><input type="radio" id="user_type_juridica" name="user_type" value="juridica" onchange="toggleFields()"> Pessoa Jurídica</label>
                </div>
            </div>

            <div id="juridica_fields" style="display: none;">
                <div class="input-group">
                    <label for="company_name">Razão Social</label>
                    <input type="text" id="company_name" name="company_name">
                </div>
            </div>

            <div class="input-group">
                <label id="name_label" for="name">Nome Completo</label>
                <input type="text" id="name" name="full_name" required>
            </div>
            <div class="input-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="input-group">
                <label id="document_label" for="document">CPF</label>
                <input type="text" id="document" name="document" required>
            </div>

            <div class="input-group">
                <label for="username">Usuário</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Cadastrar</button>
        </form>
        <p style="margin-top: 20px;">Já tem uma conta? <a href="index.php" style="color: #1e90ff;">Faça login</a></p>
    </div>

    <script>
        function toggleFields() {
            const userType = document.querySelector('input[name="user_type"]:checked').value;
            const juridicaFields = document.getElementById('juridica_fields');
            const nameLabel = document.getElementById('name_label');
            const documentLabel = document.getElementById('document_label');
            const companyNameInput = document.getElementById('company_name');
            const documentInput = document.getElementById('document');

            if (userType === 'juridica') {
                juridicaFields.style.display = 'block';
                nameLabel.textContent = 'Nome do Responsável';
                documentLabel.textContent = 'CNPJ';
                documentInput.placeholder = '00.000.000/0000-00';
                companyNameInput.required = true;
            } else {
                juridicaFields.style.display = 'none';
                nameLabel.textContent = 'Nome Completo';
                documentLabel.textContent = 'CPF';
                documentInput.placeholder = '000.000.000-00';
                companyNameInput.required = false;
            }
        }

        // Initialize fields on page load
        document.addEventListener('DOMContentLoaded', toggleFields);
    </script>
</body>
</html>
