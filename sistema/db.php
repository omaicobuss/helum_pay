<?php
// Configurações do banco de dados
//$servername = "localhost"; // Servidor do banco de dados
//$username = "helumc87_pay";       // Usuário do banco de dados (padrão do XAMPP)
//$password = "D3f1n1t1v@";           // Senha do banco de dados (padrão do XAMPP é vazio)
//$dbname = "helumc87_pay";   // Nome do banco de dados que você criou

// Configurações do banco de dados
$servername = "localhost"; // Servidor do banco de dados
$username = "root";       // Usuário do banco de dados (padrão do XAMPP)
$password = "";           // Senha do banco de dados (padrão do XAMPP é vazio)
$dbname = "helum_pay";   // Nome do banco de dados que você criou

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}
?>
