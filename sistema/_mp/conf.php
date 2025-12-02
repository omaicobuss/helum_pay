<?php

/**
 * Arquivo de configuração para as credenciais do Mercado Pago.
 *
 * As credenciais foram movidas para o arquivo config.php na raiz do sistema.
 */

// Inclui o arquivo de configuração principal
if (file_exists(__DIR__ . '/../config.php')) {
    require_once __DIR__ . '/../config.php';
} else {
    // Tratar o erro caso o arquivo de configuração não seja encontrado
    die('O arquivo de configuração principal não foi encontrado.');
}
