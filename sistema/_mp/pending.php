<?php
// Página de pendência simplificada.
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Pendente</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background-color: #fffbeb; color: #b45309; }
        .container { max-width: 600px; margin: auto; background: #fef3c7; padding: 20px; border-radius: 8px; border: 1px solid #fde68a; }
        h1 { color: #d97706; }
        p { font-size: 1.1em; }
        a { color: #f59e0b; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

    <div class="container">
        <h1>⏳ Pagamento Pendente</h1>
        <p>Seu pagamento está aguardando confirmação.</p>
        <p>Se você escolheu pagar com boleto, lembre-se de efetuar o pagamento para concluir a compra. Assim que for confirmado, seu pedido será processado.</p>

        <?php if (isset($_GET['payment_id'])): ?>
            <p><strong>ID do Pagamento:</strong> <?php echo htmlspecialchars($_GET['payment_id']); ?></p>
        <?php endif; ?>

        <?php if (isset($_GET['status'])): ?>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($_GET['status']); ?></p>
        <?php endif; ?>

        <p><a href="../dashboard_cliente.php">Voltar para o painel</a></p>
    </div>

</body>
</html>