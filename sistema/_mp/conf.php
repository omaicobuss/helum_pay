<?php

/**
 * Arquivo de configuração para as credenciais do Mercado Pago.
 *
 * Altere a constante MP_MODE para alternar entre os ambientes.
 * 'test' -> Usa as credenciais de teste (Sandbox).
 * 'production' -> Usa as credenciais de produção.
 */

// Defina o modo de operação: 'test' ou 'production'
define('MP_MODE', 'production');

$mp_credentials = [];

if (MP_MODE === 'production') {
    // --- CREDENCIAIS DE PRODUÇÃO ---
    // Substitua pelos seus valores reais de produção
    $mp_credentials = [
        'access_token'   => '****',
        'webhook_secret' => '****'
    ];
} else {
    // --- CREDENCIAIS DE TESTE (SANDBOX) ---
    $mp_credentials = [
        'access_token'   => 'APP_USR-1191976145168833-100317-0def7911700e0ec5c41d08379d375261-2603243808',
        'webhook_secret' => 'bd8192740881ea1c037d3310b0e93aa0604b01f124e843ef8d511d41161ddef9'
    ];
}
