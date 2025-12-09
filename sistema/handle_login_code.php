<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'db.php';
require '_mp/vendor/autoload.php';
require 'email_templates.php';

require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Verificar se o e-mail existe na base de dados
    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Gerar código único
        $code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 6);
        $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Salvar código na base de dados (Usa INSERT ... ON DUPLICATE KEY UPDATE para criar ou atualizar)
        $stmt = $conn->prepare("
            INSERT INTO login_codes (email, code, expires_at) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE code = VALUES(code), expires_at = VALUES(expires_at)
        ");
        $stmt->bind_param("sss", $email, $code, $expires_at);
        $stmt->execute();

        // Enviar e-mail com o código
        $mail = new PHPMailer(true);
        try {
            // Configurações do servidor a partir de config.php
            $mail->isSMTP();
            $mail->Host = $smtp_config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_config['username'];
            $mail->Password = $smtp_config['password'];
            $mail->SMTPSecure = $smtp_config['smtp_secure'];
            $mail->Port = $smtp_config['port'];
            $mail->CharSet = 'UTF-8';

            // Remetente e destinatário
            $mail->setFrom($smtp_config['username'], 'Helum Pay');
            $mail->addAddress($email, $user['full_name'] ?? '');

            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = 'Seu código de acesso único';
            $mail->Body    = getLoginCodeEmailBody($code);

            $mail->send();

            $_SESSION['email_for_code_login'] = $email;
            header("Location: enter_code.php");
            exit();

        } catch (Exception $e) {
            // Log do erro para depuração em vez de expor ao usuário
            error_log("Erro ao enviar e-mail de código de login: " . $mail->ErrorInfo);
            $_SESSION['error_message'] = "Ocorreu um erro ao enviar o código de acesso. Por favor, tente novamente.";
            header("Location: login_code.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "E-mail não encontrado.";
        header("Location: login_code.php");
        exit();
    }
}
?>
