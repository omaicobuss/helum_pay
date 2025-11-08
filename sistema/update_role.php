
<?php
session_start();

// Proteção: Apenas administradores podem acessar esta funcionalidade
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validação dos dados recebidos
    if (isset($_POST['user_id'], $_POST['role'])) {
        $user_id = $_POST['user_id'];
        $role = $_POST['role'];

        // Garante que a role seja apenas 'admin' ou 'cliente'
        if ($role === 'admin' || $role === 'cliente') {
            
            // Não permitir que o admin remova seu próprio status de admin
            if ($user_id == $_SESSION['user_id'] && $role === 'cliente') {
                // Idealmente, aqui teria uma mensagem de erro, mas por simplicidade vamos apenas redirecionar
                header("Location: dashboard_admin.php");
                exit();
            }

            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $role, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['update_success'] = "Perfil do usuário atualizado com sucesso!";
            }

            $stmt->close();
        }
    }
}

$conn->close();

// Redireciona de volta para o dashboard do admin
header("Location: dashboard_admin.php");
exit();
?>
