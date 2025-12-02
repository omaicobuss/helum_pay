<?php
require_once 'db.php';

$sql = "CREATE TABLE IF NOT EXISTS login_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabela 'login_codes' criada com sucesso ou já existente.";
} else {
    echo "Erro ao criar a tabela 'login_codes': " . $conn->error;
}

$conn->close();
?>
