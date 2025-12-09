<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'db.php';
require '_mp/vendor/autoload.php'; // PHPMailer autoloader
require 'email_templates.php'; // Carrega os templates de e-mail

// Proteção: Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;
$template = $_GET['template'] ?? '';

if (!$id) {
    $_SESSION['feedback'] = "Erro: ID não fornecido.";
    header("Location: dashboard_admin.php");
    exit();
}

$mail = new PHPMailer(true);

try {
    // --- CONFIGURAÇÃO DO SERVIDOR DE E-MAIL (SMTP) ---
    $mail->isSMTP();
    $mail->Host = $smtp_config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_config['username'];
    $mail->Password = $smtp_config['password'];
    $mail->SMTPSecure = $smtp_config['smtp_secure'];
    $mail->Port = $smtp_config['port'];
    $mail->CharSet = 'UTF-8';

    // Remetente
    $mail->setFrom($smtp_config['username'], 'Helum Pay');
    $mail->addBCC($smtp_config['username']);

    $email_sent = false;

    if ($action === 'notify_subscription') {
        $sql = "SELECT u.email, u.full_name, p.name as product_name, s.next_due_date 
                FROM subscriptions s 
                JOIN users u ON s.user_id = u.id 
                JOIN products p ON s.product_id = p.id 
                WHERE s.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            $mail->addAddress($result['email'], $result['full_name']);
            $mail->isHTML(true);

            if ($template === 'invoice_available') {
                $mail->Subject = 'Fatura Disponível - Helum Pay';
                $mail->Body    = getInvoiceAvailableEmailBody($result['full_name'], $result['product_name']);
            } elseif ($template === 'invoice_due') {
                $mail->Subject = 'Lembrete de Vencimento - Helum Pay';
                $due_date = date('d/m/Y', strtotime($result['next_due_date']));
                $mail->Body    = getInvoiceDueEmailBody($result['full_name'], $result['product_name'], $due_date);
            } else {
                $_SESSION['feedback'] = "Erro: Template de notificação inválido.";
                header("Location: dashboard_admin.php");
                exit();
            }
            
            $mail->send();
            $email_sent = true;
            $_SESSION['feedback'] = "E-mail de notificação enviado para {$result['email']}.";
        }

    } elseif ($action === 'confirm_payment') {
        $sql = "SELECT u.email, u.full_name, p.name as product_name, pay.payment_date, pay.amount 
                FROM payments pay
                JOIN subscriptions s ON pay.subscription_id = s.id
                JOIN users u ON s.user_id = u.id 
                JOIN products p ON s.product_id = p.id 
                WHERE pay.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result && $template === 'payment_confirmation') {
            $mail->addAddress($result['email'], $result['full_name']);
            $mail->isHTML(true);
            $mail->Subject = 'Confirmação de Pagamento - Helum Pay';
            $payment_date = date('d/m/Y', strtotime($result['payment_date']));
            $amount = number_format($result['amount'], 2, ',', '.');
            $mail->Body    = getPaymentConfirmationEmailBody($result['full_name'], $result['product_name'], $payment_date, $amount);
            
            $mail->send();
            $email_sent = true;
            $_SESSION['feedback'] = "E-mail de confirmação de pagamento enviado para {$result['email']}.";
        }
    } elseif ($action === 'send_custom') {
        // ... (código existente para e-mails de usuário)
        $stmt = $conn->prepare("SELECT email, username, full_name FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && $template === 'new_system_welcome') {
            $mail->addAddress($user['email'], $user['full_name']);
            $mail->isHTML(true);
            $mail->Subject = 'Bem-vindo ao Novo Sistema Helum!';
            $mail->Body    = getNewSystemEmailBody($user['full_name'], $user['username'], $user['email']);
            
            $mail->send();
            $email_sent = true;
            $_SESSION['feedback'] = "E-mail enviado com sucesso para {$user['email']}.";
        } elseif ($user && $template === 'new_login_method') {
            $mail->addAddress($user['email'], $user['full_name']);
            $mail->isHTML(true);
            $mail->Subject = 'Uma nova maneira de acessar sua conta Helum Pay!';
            $mail->Body    = getNewLoginMethodEmailBody($user['full_name']);
            
            $mail->send();
            $email_sent = true;
            $_SESSION['feedback'] = "E-mail sobre o novo método de login enviado com sucesso para {$user['email']}.";
        }
    }

    if (!$email_sent) {
        $_SESSION['feedback'] = "Erro: Não foi possível encontrar os dados ou o template para enviar o e-mail.";
    }

} catch (Exception $e) {
    $_SESSION['feedback'] = "Erro ao enviar o e-mail: {$mail->ErrorInfo}";
}

header("Location: dashboard_admin.php");
exit();
?>
