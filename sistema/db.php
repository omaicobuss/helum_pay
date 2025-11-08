<?php
// Caminho para o arquivo de configuração
$config_path = __DIR__ . '/db_config.php';

// Verifica se o arquivo de configuração existe
if (!file_exists($config_path)) {
    die("Erro crítico: O arquivo de configuração 'db_config.php' não foi encontrado. Por favor, crie-o a partir de 'db_config.example.php'.");
}

// Carrega as configurações do banco de dados
require_once $config_path;

// Cria a conexão usando as configurações do array $db_config
$conn = new mysqli(
    $db_config['servername'],
    $db_config['username'],
    $db_config['password'],
    $db_config['dbname']
);

// Verifica a conexão
if ($conn->connect_error) {
    // Em um ambiente de produção, é melhor logar o erro do que expô-lo.
    error_log("Falha na conexão com o banco de dados: " . $conn->connect_error);
    // Exibe uma mensagem genérica para o usuário.
    die("Falha na conexão com o banco de dados. Por favor, tente novamente mais tarde.");
}
?>
