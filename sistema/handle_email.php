<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Inclui dependências
require_once 'db.php';
require_once 'config.php';
require_once '_mp/vendor/autoload.php';
require_once 'email_templates.php';

// --- Validação de Acesso ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

/**
 * Envia um e-mail configurado.
 *
 * @param array $recipient ['email' => string, 'name' => string]
 * @param string $subject
 * @param string $body
 * @param array $smtp_config
 * @return void
 * @throws Exception
 */
function sendConfiguredEmail(array $recipient, string $subject, string $body, array $smtp_config): void
{
    $mail = new PHPMailer(true);

    // Configuração do Servidor SMTP
    $mail->isSMTP();
    $mail->Host = $smtp_config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_config['username'];
    $mail->Password = $smtp_config['password'];
    $mail->SMTPSecure = $smtp_config['smtp_secure'];
    $mail->Port = $smtp_config['port'];
    $mail->CharSet = 'UTF-8';

    // Remetente e Destinatário
    $mail->setFrom($smtp_config['username'], 'Helum Pay');
    $mail->addAddress($recipient['email'], $recipient['name']);
    $mail->addBCC($smtp_config['username']); // Cópia oculta para o admin

    // Conteúdo do E-mail
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $body;

    $mail->send();
}

// --- Lógica Principal ---
$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$template = $_GET['template'] ?? '';

if ($id === 0) {
    $_SESSION['feedback'] = "Erro: ID não fornecido.";
    header("Location: dashboard_admin.php");
    exit();
}

$feedback_message = "Erro: Ação ou template inválido.";
$email_sent = false;

try {
    switch ($action) {
        case 'notify_subscription':
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
                $recipient = ['email' => $result['email'], 'name' => $result['full_name']];
                $subject = '';
                $body = '';

                if ($template === 'invoice_available') {
                    $subject = 'Fatura Disponível - Helum Pay';
                    $body = getInvoiceAvailableEmailBody($result['full_name'], $result['product_name']);
                } elseif ($template === 'invoice_due') {
                    $subject = 'Lembrete de Vencimento - Helum Pay';
                    $due_date = date('d/m/Y', strtotime($result['next_due_date']));
                    $body = getInvoiceDueEmailBody($result['full_name'], $result['product_name'], $due_date);
                }

                if ($subject && $body) {
                    sendConfiguredEmail($recipient, $subject, $body, $smtp_config);
                    $feedback_message = "E-mail de notificação enviado para {$result['email']}.";
                    $email_sent = true;
                }
            }
            break;

        case 'confirm_payment':
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
                $recipient = ['email' => $result['email'], 'name' => $result['full_name']];
                $subject = 'Confirmação de Pagamento - Helum Pay';
                $payment_date = date('d/m/Y', strtotime($result['payment_date']));
                $amount = number_format($result['amount'], 2, ',', '.');
                $body = getPaymentConfirmationEmailBody($result['full_name'], $result['product_name'], $payment_date, $amount);
                
                sendConfiguredEmail($recipient, $subject, $body, $smtp_config);
                $feedback_message = "E-mail de confirmação de pagamento enviado para {$result['email']}.";
                $email_sent = true;
            }
            break;

        case 'send_custom':
            $stmt = $conn->prepare("SELECT email, username, full_name FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user) {
                $recipient = ['email' => $user['email'], 'name' => $user['full_name']];
                $subject = '';
                $body = '';

                if ($template === 'new_system_welcome') {
                    $subject = 'Bem-vindo ao Novo Sistema Helum!';
                    $body = getNewSystemEmailBody($user['full_name'], $user['username'], $user['email']);
                    $feedback_message = "E-mail de boas-vindas enviado para {$user['email']}.";
                } elseif ($template === 'new_login_method') {
                    $subject = 'Uma nova maneira de acessar sua conta Helum Pay!';
                    $body = getNewLoginMethodEmailBody($user['full_name']);
                    $feedback_message = "E-mail sobre o novo método de login enviado para {$user['email']}.";
                }

                if ($subject && $body) {
                    sendConfiguredEmail($recipient, $subject, $body, $smtp_config);
                    $email_sent = true;
                }
            }
            break;
    }

    if (!$email_sent && empty($feedback_message)) {
        $feedback_message = "Erro: Não foi possível encontrar os dados ou o template para enviar o e-mail.";
    }

} catch (Exception $e) {
    // Usar o objeto $mail aqui pode ser problemático se a exceção ocorreu durante sua instanciação
    $feedback_message = "Erro ao enviar o e-mail: " . $e->getMessage();
}

$_SESSION['feedback'] = $feedback_message;
header("Location: dashboard_admin.php");
exit();
?>