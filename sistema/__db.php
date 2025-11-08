<?php
// Configurações do banco de dados
$servername = "localhost"; // Servidor do banco de dados
$username = "helumc87_pay";       // Usuário do banco de dados (padrão do XAMPP)
$password = "D3f1n1t1v@";           // Senha do banco de dados (padrão do XAMPP é vazio)
$dbname = "helumc87_pay";   // Nome do banco de dados que você criou

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    // Em produção, é melhor logar o erro e exibir uma mensagem amigável
    // do que interromper o script com die().
    error_log("Database Connection Error: " . $conn->connect_error);
    // A verificação 'isset($conn)' nos outros scripts vai falhar, tratando o erro.
}
?>
