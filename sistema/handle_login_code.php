<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'db.php';
require '_mp/vendor/autoload.php';
require 'email_templates.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Verificar se o e-mail existe na base de dados
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Gerar código único
        $code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 6);
        $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Salvar código na base de dados
        $stmt = $conn->prepare("INSERT INTO login_codes (email, code, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $code, $expires_at);
        $stmt->execute();

        // Enviar e-mail com o código
        $mail = new PHPMailer(true);
        try {
            // Configurações do servidor
            $mail->isSMTP();
            $mail->Host = 'mail.helum.com.br';
            $mail->SMTPAuth = true;
            $mail->Username = 'financeiro@helum.com.br';
            $mail->Password = 'D3f1n1t1v@';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->CharSet = 'UTF-8';

            // Remetente e destinatário
            $mail->setFrom('financeiro@helum.com.br', 'Helum Pay');
            $mail->addAddress($email);

            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = 'Seu código de acesso único';
            $mail->Body    = getLoginCodeEmailBody($code);

            $mail->send();

            $_SESSION['email_for_code_login'] = $email;
            header("Location: enter_code.php");
            exit();

        } catch (Exception $e) {
            $_SESSION['error_message'] = "Erro ao enviar o e-mail: {$mail->ErrorInfo}";
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
