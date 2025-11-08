<?php
// Página de falha simplificada.
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Falha no Pagamento</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background-color: #fff1f2; color: #9f1239; }
        .container { max-width: 600px; margin: auto; background: #ffe4e6; padding: 20px; border-radius: 8px; border: 1px solid #fecdd3; }
        h1 { color: #be123c; }
        p { font-size: 1.1em; }
        a { color: #e11d48; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

    <div class="container">
        <h1>❌ Falha no Pagamento</h1>
        <p>Houve um problema ao processar seu pagamento e ele foi recusado.</p>
        <p>Por favor, verifique os dados inseridos e tente novamente.</p>

        <?php if (isset($_GET['payment_id'])): ?>
            <p><strong>ID da Tentativa:</strong> <?php echo htmlspecialchars($_GET['payment_id']); ?></p>
        <?php endif; ?>

        <?php if (isset($_GET['status'])): ?>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($_GET['status']); ?></p>
        <?php endif; ?>

        <p><a href="../dashboard_cliente.php">Voltar para o painel e tentar novamente</a></p>
    </div>

</body>
</html>