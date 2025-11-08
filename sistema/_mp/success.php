<?php
// Página de sucesso simplificada. Apenas exibe a mensagem para o usuário.
// O processamento real do pagamento é feito pelo webhook.
$payment_id = $_GET['payment_id'] ?? 'Não informado';
$status = $_GET['status'] ?? 'Não informado';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Aprovado!</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background-color: #f0fdf4; color: #166534; }
        .container { max-width: 600px; margin: auto; background: #dcfce7; padding: 20px; border-radius: 8px; border: 1px solid #86efac; }
        h1 { color: #15803d; }
        p { font-size: 1.1em; }
        a { color: #16a34a; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

    <div class="container">
        <h1>🎉 Pagamento Aprovado!</h1>
        <p>Obrigado pela sua compra! Seu pedido foi processado com sucesso.</p>
        
        <p>Você receberá a confirmação em breve. O ID da sua transação é: <strong><?php echo htmlspecialchars($payment_id); ?></strong></p>

        <p><a href="../dashboard_cliente.php">Voltar para o painel</a></p>
    </div>

</body>
</html>