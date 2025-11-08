<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Conexão Externa (cURL)</h1>";

$url = "https://api.mercadopago.com/v1/payment_methods";

echo "<p>Tentando conectar a: <strong>" . htmlspecialchars($url) . "</strong></p>";

// Inicializa o cURL
$ch = curl_init();

// Configura as opções do cURL
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retorna a resposta como string
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Timeout para conectar em 10 segundos
curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Timeout total da requisição em 15 segundos
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Força a verificação do certificado SSL

// Executa a requisição
$response = curl_exec($ch);

if (curl_errno($ch)) {
    // Se houver um erro no cURL
    $error_msg = curl_error($ch);
    echo "<p style='color: red; font-weight: bold;'>ERRO DE CONEXÃO cURL: " . htmlspecialchars($error_msg) . "</p>";
} else {
    // Se a conexão for bem-sucedida
    echo "<p style='color: green; font-weight: bold;'>SUCESSO! Conexão estabelecida.</p>";
    echo "<p>Resposta recebida (início):</p>";
    echo "<pre>" . htmlspecialchars(substr($response, 0, 300)) . "...</pre>";
}

// Fecha a conexão cURL
curl_close($ch);