<?php
session_start();
require 'db.php';

// Proteção: Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Valida o ID do usuário
$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$user_id) {
    $_SESSION['feedback'] = "ID de usuário inválido.";
    header("Location: dashboard_admin.php");
    exit();
}

// Busca os dados do usuário
$stmt = $conn->prepare("SELECT id, full_name, email, username, document, user_type, company_name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['feedback'] = "Usuário não encontrado.";
    header("Location: dashboard_admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário - Helum Pay</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h1>Editar Usuário: <?php echo htmlspecialchars($user['username']); ?></h1>
        
        <form action="handle_user_edit.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">

            <div class="input-group">
                <label>Tipo de Pessoa</label>
                <div style="display: flex; gap: 20px;">
                    <label style="display: flex; align-items: center; gap: 5px;">
                        <input type="radio" name="user_type" value="fisica" <?php echo ($user['user_type'] === 'fisica') ? 'checked' : ''; ?> onchange="toggleFields()"> Pessoa Física
                    </label>
                    <label style="display: flex; align-items: center; gap: 5px;">
                        <input type="radio" name="user_type" value="juridica" <?php echo ($user['user_type'] === 'juridica') ? 'checked' : ''; ?> onchange="toggleFields()"> Pessoa Jurídica
                    </label>
                </div>
            </div>

            <div id="juridica_fields" style="display: none;">
                <div class="input-group">
                    <label for="company_name">Razão Social</label>
                    <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($user['company_name'] ?? ''); ?>">
                </div>
            </div>

            <div class="input-group">
                <label id="name_label" for="full_name">Nome Completo</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>
            <div class="input-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            
            <div class="input-group">
                <label id="document_label" for="document">CPF</label>
                <input type="text" id="document" name="document" value="<?php echo htmlspecialchars($user['document']); ?>" required>
            </div>

            <div class="input-group">
                <label for="username">Usuário</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="input-group">
                <label for="password">Nova Senha (deixe em branco para não alterar)</label>
                <input type="password" id="password" name="password">
            </div>
            <button type="submit" class="btn-login">Salvar Alterações</button>
        </form>
        <p style="margin-top: 20px;"><a href="dashboard_admin.php" style="color: #1e90ff;">Voltar ao Painel</a></p>
    </div>

    <script>
        function toggleFields() {
            const userType = document.querySelector('input[name="user_type"]:checked').value;
            const juridicaFields = document.getElementById('juridica_fields');
            const nameLabel = document.getElementById('name_label');
            const documentLabel = document.getElementById('document_label');
            const companyNameInput = document.getElementById('company_name');

            if (userType === 'juridica') {
                juridicaFields.style.display = 'block';
                nameLabel.textContent = 'Nome do Responsável';
                documentLabel.textContent = 'CNPJ';
                companyNameInput.required = true;
            } else {
                juridicaFields.style.display = 'none';
                nameLabel.textContent = 'Nome Completo';
                documentLabel.textContent = 'CPF';
                companyNameInput.required = false;
            }
        }
        document.addEventListener('DOMContentLoaded', toggleFields);
    </script>
</body>
</html>
