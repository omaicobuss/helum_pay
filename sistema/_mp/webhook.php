<?php

/**
 * Webhook Endpoint para Notificações do Mercado Pago.
 *
 * Este script utiliza uma abordagem orientada a objetos para lidar com as notificações.
 * 1. Loga a requisição bruta.
 * 2. Responde imediatamente com HTTP 200 OK para o Mercado Pago para evitar timeouts.
 * 3. Valida a assinatura HMAC para garantir a autenticidade da notificação.
 */

// Configurações de erro para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Dependências
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/conf.php';

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;

/**
 * Classe para manipular as notificações de webhook.
 */
class WebhookHandler
{
    private mysqli $conn;
    private array $credentials;
    private array $request;
    private int $logId = 0;

    /**
     * Construtor. Captura os dados da requisição.
     * @param mysqli $dbConnection A conexão com o banco de dados.
     * @param array $mpCredentials As credenciais do Mercado Pago.
     */
    public function __construct(mysqli $dbConnection, array $mpCredentials)
    {
        $this->conn = $dbConnection;
        $this->credentials = $mpCredentials;
        $this->request = [
            'headers'     => getallheaders(),
            'queryParams' => $_GET,
            'body'        => file_get_contents('php://input')
        ];

        // Configura o SDK do Mercado Pago (será usado no próximo passo)
        MercadoPagoConfig::setAccessToken($this->credentials['access_token']);
    }

    /**
     * Método principal que orquestra o processo.
     */
    public function process(): void
    {
        $this->logRequest();
        $this->sendImmediateResponse();

        // Após responder, validamos a assinatura.
        if (!$this->isValidSignature()) {
            $this->updateLogStatus('HMAC_FAILED');
            error_log("Webhook: HMAC verification failed.");
            return; // Encerra o processamento se a assinatura for inválida.
        }

        // Se a assinatura for válida, processamos o pagamento.
        $this->processNotification();
    }

    /**
     * Processa a notificação de pagamento usando os dados do corpo da requisição.
     */
    private function processNotification(): void
    {
        $body = json_decode($this->request['body']);

        // Verifica se é uma notificação de pagamento e se temos os dados necessários
        if (!isset($body->type) || $body->type !== 'payment' || !isset($body->data->id)) {
            $this->updateLogStatus('IGNORED_NOT_PAYMENT_EVENT');
            return;
        }

        // A chamada à API está causando problemas, então vamos confiar no webhook.
        // Para uma implementação mais robusta no futuro, um job em background
        // poderia ler os logs e chamar a API de forma segura.
        // Por agora, vamos assumir que a notificação é suficiente.
        
        // O corpo da notificação de 'payment' não contém todos os detalhes.
        // A melhor prática é usar o ID para buscar os detalhes.
        // Como a chamada direta está falhando, vamos logar e parar por aqui.
        // A solução definitiva ainda é resolver o problema de conexão do servidor.
        
        // No entanto, podemos tentar uma última vez chamar a API daqui,
        // pois o ambiente do webhook pode ser diferente do da página web.
        try {
            $paymentId = $body->data->id;
            $client = new PaymentClient();
            $payment = $client->get($paymentId);

            if ($payment->status === 'approved' && !empty($payment->external_reference)) {
                $this->handleApprovedPayment($payment);
                $this->updateLogStatus('PROCESSED_SUCCESS');
            } else {
                $this->updateLogStatus("IGNORED_STATUS_{$payment->status}");
            }
        } catch (MPApiException $e) {
            $errorMessage = "WEBHOOK_API_ERROR: " . $e->getMessage();
            if ($e->getPrevious()) {
                $errorMessage .= " | cURL: " . $e->getPrevious()->getMessage();
            }
            $this->updateLogStatus($errorMessage);
            error_log($errorMessage);
        } catch (Exception $e) {
            $this->updateLogStatus('WEBHOOK_GENERAL_ERROR: ' . $e->getMessage());
            error_log('WEBHOOK_GENERAL_ERROR: ' . $e->getMessage());
        }
    }

    /**
     * Lida com um pagamento aprovado, atualizando o banco de dados.
     * @param object $payment O objeto de pagamento retornado pela API.
     */
    private function handleApprovedPayment(object $payment): void
    {
        $subscriptionId = (int)$payment->external_reference;
        
        $this->conn->begin_transaction();
        try {
            $stmtPayment = $this->conn->prepare(
                "INSERT INTO payments (subscription_id, payment_date, amount, status, mp_payment_id) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status), payment_date = VALUES(payment_date), amount = VALUES(amount)"
            );
            $paymentDate = date("Y-m-d H:i:s");
            $stmtPayment->bind_param("isdsi", $subscriptionId, $paymentDate, $payment->transaction_amount, $payment->status, $payment->id);
            $stmtPayment->execute();
            $stmtPayment->close();

            $stmtSub = $this->conn->prepare("UPDATE subscriptions SET next_due_date = DATE_ADD(next_due_date, INTERVAL 1 MONTH), status = 'paid' WHERE id = ?");
            $stmtSub->bind_param("i", $subscriptionId);
            $stmtSub->execute();
            $stmtSub->close();

            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            $this->updateLogStatus('DB_ERROR: ' . $e->getMessage());
            error_log("Webhook DB Error: " . $e->getMessage());
        }
    }

    /**
     * Valida a assinatura HMAC da requisição para garantir sua autenticidade.
     * @return bool Retorna true se a assinatura for válida, false caso contrário.
     */
    private function isValidSignature(): bool
    {
        // O cabeçalho pode vir em minúsculas ou capitalizadas
        $signatureHeader = $this->request['headers']['x-signature'] ?? $this->request['headers']['X-Signature'] ?? '';
        $requestId = $this->request['headers']['x-request-id'] ?? $this->request['headers']['X-Request-Id'] ?? '';
        $dataId = $this->request['queryParams']['data_id'] ?? '';

        if (empty($signatureHeader) || empty($dataId)) {
            return false;
        }

        // Extrai o timestamp (ts) e o hash (v1) do cabeçalho
        $parts = explode(',', $signatureHeader);
        $ts = null;
        $hash = null;

        foreach ($parts as $part) {
            $keyValue = explode('=', trim($part), 2);
            if (count($keyValue) === 2) {
                if ($keyValue[0] === "ts") $ts = $keyValue[1];
                if ($keyValue[0] === "v1") $hash = $keyValue[1];
            }
        }

        if ($ts === null || $hash === null) {
            return false;
        }

        // Cria a string "manifest" para gerar a assinatura
        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$ts};";

        // Calcula o HMAC usando a chave secreta
        $calculatedSha = hash_hmac('sha256', $manifest, $this->credentials['webhook_secret']);

        // Compara de forma segura o hash calculado com o hash recebido
        return hash_equals($calculatedSha, $hash);
    }

    /**
     * Salva a requisição completa na tabela de logs.
     */
    private function logRequest(): void
    {
        try {
            $headersJson = json_encode($this->request['headers'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $queryParamsJson = json_encode($this->request['queryParams'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $stmt = $this->conn->prepare(
                "INSERT INTO webhook_logs (headers, query_params, body) VALUES (?, ?, ?)"
            );
            $stmt->bind_param("sss", $headersJson, $queryParamsJson, $this->request['body']);
            $stmt->execute();
            $this->logId = $this->conn->insert_id;
            $stmt->close();
        } catch (Exception $e) {
            error_log("CRITICAL: Failed to write to webhook_logs table. Error: " . $e->getMessage());
        }
    }

    /**
     * Atualiza o status de processamento do log atual.
     * @param string $status O status a ser registrado.
     */
    private function updateLogStatus(string $status): void
    {
        if ($this->logId > 0) {
            try {
                $status = substr($status, 0, 255); // Garante que o status não exceda o limite do campo
                $stmt = $this->conn->prepare("UPDATE webhook_logs SET processing_status = ? WHERE id = ?");
                $stmt->bind_param("si", $status, $this->logId);
                $stmt->execute();
                $stmt->close();
            } catch (Exception $e) {
                error_log("Failed to update log status for log_id {$this->logId}. Error: " . $e->getMessage());
            }
        }
    }

    /**
     * Envia uma resposta HTTP 200 OK e encerra a conexão.
     */
    private function sendImmediateResponse(): void
    {
        ignore_user_abort(true);

        ob_start();
        header("Content-Type: text/plain");
        http_response_code(200);
        echo "OK";

        header('Connection: close');
        header('Content-Length: ' . ob_get_length());
        ob_end_flush();
        flush();
    }
}

// --- Ponto de Entrada do Script ---

// Verifica se é um acesso direto via navegador para exibir uma mensagem amigável.
if ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($_GET)) {
    http_response_code(200);
    echo "Webhook endpoint is active.";
    exit();
}

try {
    // Passa a conexão e as credenciais para o handler
    $handler = new WebhookHandler($conn, $mp_credentials);
    $handler->process();
} catch (Exception $e) {
    http_response_code(500);
    error_log("Webhook handler failed to initialize: " . $e->getMessage());
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    exit();
}

?>