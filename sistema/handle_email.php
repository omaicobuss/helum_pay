<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'db.php';
require '_mp/vendor/autoload.php'; // PHPMailer autoloader

// Proteção: Apenas administradores
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if (!$id) {
    $_SESSION['feedback'] = "Erro: ID não fornecido.";
    header("Location: dashboard_admin.php");
    exit();
}

$mail = new PHPMailer(true);

try {
    // --- CONFIGURAÇÃO DO SERVIDOR DE E-MAIL (SMTP) ---
    // IMPORTANTE: Substitua com suas credenciais de e-mail reais.
    $mail->isSMTP();
    $mail->Host = 'mail.helum.com.br'; // Insira seu servidor SMTP
    $mail->SMTPAuth = true;
    $mail->Username = 'financeiro@helum.com.br'; // Seu e-mail
    $mail->Password = 'D3f1n1t1v@'; // Sua senha
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;
    $mail->CharSet = 'UTF-8';

    // Remetente
    $mail->setFrom('financeiro@helum.com.br', 'Helum Pay');

    $email_sent = false;

    if ($action === 'notify_due') {
        // Buscar dados da assinatura
        $sql = "SELECT u.email, u.username, p.name as product_name, s.next_due_date 
                FROM subscriptions s 
                JOIN users u ON s.user_id = u.id 
                JOIN products p ON s.product_id = p.id 
                WHERE s.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            $mail->addAddress($result['email'], $result['username']);
            $mail->isHTML(true);
            $mail->Subject = 'Lembrete de Vencimento - Helum Pay';
            $due_date = date('d/m/Y', strtotime($result['next_due_date']));
            $mail->Body    = "Olá, {$result['username']}.<br><br>" .
                             "Este é um lembrete de que sua assinatura do produto <strong>{$result['product_name']}</strong> está com o vencimento próximo ou vencida.<br>" .
                             "Data de Vencimento: <strong>{$due_date}</strong><br><br>" .
                             "Por favor, acesse seu painel para efetuar o pagamento e manter seu serviço ativo.<br><br>" .
                             "Atenciosamente,<br>Equipe Helum Pay";
            $mail->send();
            $email_sent = true;
            $_SESSION['feedback'] = "E-mail de lembrete de vencimento enviado para {$result['email']}.";
        }

    } elseif ($action === 'confirm_payment') {
        // Buscar dados do pagamento
        $sql = "SELECT u.email, u.username, p.name as product_name, pay.payment_date, pay.amount 
                FROM payments pay
                JOIN subscriptions s ON pay.subscription_id = s.id
                JOIN users u ON s.user_id = u.id 
                JOIN products p ON s.product_id = p.id 
                WHERE pay.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            $mail->addAddress($result['email'], $result['username']);
            $mail->isHTML(true);
            $mail->Subject = 'Confirmação de Pagamento - Helum Pay';
            $payment_date = date('d/m/Y', strtotime($result['payment_date']));
            $amount = number_format($result['amount'], 2, ',', '.');
            $mail->Body    = "Olá, {$result['username']}.<br><br>" .
                             "Seu pagamento para o produto <strong>{$result['product_name']}</strong> foi confirmado com sucesso!<br><br>" .
                             "<strong>Detalhes do Pagamento:</strong><br>" .
                             "Data: {$payment_date}<br>" .
                             "Valor: R$ {$amount}<br><br>" .
                             "Agradecemos a sua preferência.<br><br>" .
                             "Atenciosamente,<br>Equipe Helum Pay";
            $mail->send();
            $email_sent = true;
            $_SESSION['feedback'] = "E-mail de confirmação de pagamento enviado para {$result['email']}.";
        }
    }

    if (!$email_sent) {
        $_SESSION['feedback'] = "Erro: Não foi possível encontrar os dados para enviar o e-mail.";
    }

} catch (Exception $e) {
    $_SESSION['feedback'] = "Erro ao enviar o e-mail: {$mail->ErrorInfo}";
}

header("Location: dashboard_admin.php");
exit();
?>