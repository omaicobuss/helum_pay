<?php
ob_start();
session_start();

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'db.php';
require '_mp/vendor/autoload.php'; // Ensure you have PHPMailer installed via Composer
require 'email_templates.php'; // Carrega os templates de e-mail

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Validar e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['reset_error'] = "Formato de e-mail inválido.";
        header("Location: forgot_password.php");
        exit();
    }

    // Verificar se o e-mail existe no banco
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Gerar um token de redefinição de senha seguro
        $token = bin2hex(random_bytes(50));

        // Definir o tempo de expiração do token (ex: 1 hora)
        $expires = date("U") + 3600;

        // Armazenar o token e sua expiração no banco de dados
        try {
            $reset_stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)");
            if (!$reset_stmt) {
                throw new Exception("Falha na preparação da consulta: " . $conn->error);
            }
            $reset_stmt->bind_param("sss", $email, $token, $expires);
            if (!$reset_stmt->execute()) {
                throw new Exception("Falha ao salvar o token de redefinição: " . $reset_stmt->error);
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $_SESSION['reset_error'] = "Ocorreu um erro interno no servidor. Por favor, tente novamente mais tarde.";
            header("Location: forgot_password.php");
            exit();
        }

        // Criar o link de redefinição
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/sistemas/helum_pay/sistema/reset_password.php?token=" . $token;

        // Configurar e enviar o e-mail com PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Configurações do servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'mail.helum.com.br';
            $mail->SMTPAuth = true;
            $mail->Username = 'financeiro@helum.com.br';
            $mail->Password = 'D3f1n1t1v@';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->CharSet = 'UTF-8';

            // Destinatários
            $mail->setFrom('financeiro@helum.com.br', 'Helum Pay');
            $mail->addAddress($email, $user['username']);
            $mail->addBCC('financeiro@helum.com.br');

            // Conteúdo do e-mail
            $mail->isHTML(true);
            $mail->Subject = 'Redefinicao de Senha - Helum Pay';
            $mail->Body    = getPasswordResetEmailBody($user['username'], $reset_link);
            $mail->AltBody = "Para redefinir sua senha, copie e cole este link em seu navegador: " . $reset_link;

            $mail->send();
            $_SESSION['reset_message'] = 'Um e-mail com as instruções foi enviado para você.';
        } catch (Exception $e) {
            $_SESSION['reset_error'] = "Não foi possível enviar o e-mail. Mailer Error: {$mail->ErrorInfo}";
        }


    } else {
        $_SESSION['reset_error'] = 'O e-mail informado não está cadastrado em nosso sistema.';
    }

    session_write_close();
    header("Location: forgot_password.php");
    exit();

} else {
    header("Location: index.php");
    exit();
}
?>