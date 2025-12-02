<?php
// Exemplo de configuração do sistema.
// Copie este arquivo para 'config.php' e preencha com suas credenciais.

// Configuração do banco de dados
$db_config = [
    'servername' => 'localhost',
    'username' => 'seu_usuario',
    'password' => 'sua_senha',
    'dbname' => 'seu_banco_de_dados'
];

// Configuração do SMTP
$smtp_config = [
    'host' => 'seu_servidor_smtp',
    'username' => 'seu_usuario_smtp',
    'password' => 'sua_senha_smtp',
    'smtp_secure' => 'tls', // ou 'ssl'
    'port' => 587, // ou 465 para ssl
];
?>