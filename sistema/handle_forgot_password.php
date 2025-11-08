<?php
session_start();

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'db.php';
require '_mp/vendor/autoload.php'; // Ensure you have PHPMailer installed via Composer

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

        // Armazenar o token e sua expiração no banco de dados (crie uma tabela para isso)
        // Ex: CREATE TABLE password_resets (id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(255), token VARCHAR(100), expires BIGINT);
        $reset_stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)");
        $reset_stmt->bind_param("sss", $email, $token, $expires);
        $reset_stmt->execute();

        // Criar o link de redefinição
        $reset_link = "http://localhost/helum_pay/reset_password.php?token=" . $token;

        // Configurar e enviar o e-mail com PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Configurações do servidor SMTP (ex: Gmail)
            $mail->isSMTP();
            $mail->Host = 'smtp.example.com'; // Insira seu servidor SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'your_email@example.com'; // Seu e-mail
            $mail->Password = 'your_password'; // Sua senha
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Destinatários
            $mail->setFrom('no-reply@helumpay.com', 'Helum Pay');
            $mail->addAddress($email, $user['username']);

            // Conteúdo do e-mail
            $mail->isHTML(true);
            $mail->Subject = 'Redefinicao de Senha - Helum Pay';
            $mail->Body    = "Olá, {$user['username']}.<br><br>" . 
                             "Recebemos uma solicitação para redefinir sua senha. Clique no link abaixo para criar uma nova senha:<br>" . 
                             "<a href='{$reset_link}'>{$reset_link}</a><br><br>" . 
                             "Se você não solicitou isso, ignore este e-mail.<br><br>" . 
                             "Atenciosamente,<br>Equipe Helum Pay";
            $mail->AltBody = "Para redefinir sua senha, copie e cole este link em seu navegador: {$reset_link}";

            $mail->send();
            $_SESSION['reset_message'] = 'Um e-mail com as instruções foi enviado para você.';
        } catch (Exception $e) {
            $_SESSION['reset_error'] = "Não foi possível enviar o e-mail. Mailer Error: {$mail->ErrorInfo}";
        }

    } else {
        // Para não revelar se um e-mail está ou não cadastrado, mostre sempre uma mensagem de sucesso
        $_SESSION['reset_message'] = 'Se o e-mail estiver em nosso sistema, um link de recuperação será enviado.';
    }

    header("Location: forgot_password.php");
    exit();

} else {
    header("Location: index.php");
    exit();
}
?>
