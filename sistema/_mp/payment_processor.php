<?php

require_once __DIR__ . '/vendor/autoload.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;

/**
 * Classe para processar e atualizar o status de pagamentos do Mercado Pago.
 */
class PaymentProcessor
{
    private mysqli $conn;

    /**
     * @param mysqli $dbConnection
     * @param array $mpCredentials
     */
    public function __construct(mysqli $dbConnection, array $mpCredentials)
    {
        $this->conn = $dbConnection;

        // Define um timeout de 5 segundos (5000 milissegundos) para todas as requisições da API.
        // Se a API não responder a tempo, o SDK gerará uma exceção.
        MercadoPagoConfig::setConnectionTimeout(5000);
        MercadoPagoConfig::setAccessToken($mpCredentials['access_token']);
    }

    /**
     * Busca um pagamento na API do MP e atualiza o banco de dados.
     *
     * @param int $paymentId O ID do pagamento do Mercado Pago.
     * @return object|null O objeto de pagamento da API ou null em caso de falha.
     */
    public function syncPaymentStatus(int $paymentId): ?object
    {
        try {
            $client = new PaymentClient();
            $payment = $client->get($paymentId);

            if (!$payment) {
                return null;
            }

            // Se o pagamento foi aprovado e tem nossa referência da assinatura
            if ($payment->status === 'approved' && !empty($payment->external_reference)) {
                $this->handleApprovedPayment($payment);
            } else {
                // Para outros status (pending, rejected, etc.), apenas atualizamos o status na tabela de pagamentos.
                $this->updatePaymentRecordStatus($payment);
            }

            return $payment;

        } catch (MPApiException $e) {
            // Log detalhado para erros da API, incluindo possíveis erros de cURL
            $errorMessage = "PaymentProcessor MPApiException: " . $e->getMessage();
            if ($e->getPrevious()) {
                $errorMessage .= " | Causa anterior (cURL): " . $e->getPrevious()->getMessage();
            }
            error_log($errorMessage);
            return null;
        } catch (Exception $e) {
            error_log("PaymentProcessor General Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lida com um pagamento aprovado, atualizando o banco de dados de forma transacional.
     * @param object $payment O objeto de pagamento retornado pela API.
     */
    private function handleApprovedPayment(object $payment): void
    {
        $subscriptionId = (int)$payment->external_reference;
        
        $this->conn->begin_transaction();
        try {
            // Atualiza ou insere o registro na tabela de pagamentos
            $stmtPayment = $this->conn->prepare(
                "INSERT INTO payments (subscription_id, payment_date, amount, status, mp_payment_id) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status), payment_date = VALUES(payment_date), amount = VALUES(amount)"
            );
            $paymentDate = date("Y-m-d H:i:s");
            $stmtPayment->bind_param("isdsi", $subscriptionId, $paymentDate, $payment->transaction_amount, $payment->status, $payment->id);
            $stmtPayment->execute();
            $stmtPayment->close();

            // Atualiza a data do próximo vencimento da assinatura
            $stmtSub = $this->conn->prepare("UPDATE subscriptions SET next_due_date = DATE_ADD(next_due_date, INTERVAL 1 MONTH), status = 'paid' WHERE id = ?");
            $stmtSub->bind_param("i", $subscriptionId);
            $stmtSub->execute();
            $stmtSub->close();

            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("PaymentProcessor DB Error: " . $e->getMessage());
        }
    }

    private function updatePaymentRecordStatus(object $payment): void
    {
        $stmt = $this->conn->prepare("UPDATE payments SET status = ? WHERE mp_payment_id = ?");
        $stmt->bind_param("si", $payment->status, $payment->id);
        $stmt->execute();
        $stmt->close();
    }
}