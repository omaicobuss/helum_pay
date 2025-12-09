<?php

function getNewSystemEmailBody($fullName, $username, $email) {
    $login_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/index.php';
    $forgot_password_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/forgot_password.php';
    $logo_url = 'https://helum.com.br/sistemas/helum_pay/sistema/logo.png';

    return '
        <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="' . $logo_url . '" alt="Logo Helum" style="max-width: 150px;">
            </div>
            <h2>Atualização Importante sobre seu Acesso à Helum</h2>
            <p>Olá, ' . htmlspecialchars($fullName) . ',</p>
            <p>Estamos felizes em anunciar que migramos para um novo sistema de gestão, desenvolvido internamente pela <strong>HELUM</strong>. Nosso objetivo é simplificar seu acesso e a administração dos serviços que você contratou conosco.</p>
            <p>Este novo portal oferece uma visão clara do status dos seus serviços e facilita a comunicação e o gerenciamento de suas assinaturas.</p>
            
            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

            <h3>Acesse o Novo Sistema</h3>
            <p>Seu nome de usuário é: <strong>' . htmlspecialchars($username) . '</strong></p>
            
            <p><strong>Se você já possui sua senha:</strong></p>
            <p style="text-align: center;">
                <a href="' . $login_url . '" style="background-color: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;">Acessar o Sistema</a>
            </p>

            <p><strong>Se você esqueceu ou ainda não tem uma senha:</strong></p>
            <p>Crie uma nova senha clicando no botão abaixo. Você será solicitado a informar seu e-mail de cadastro para iniciar o processo de recuperação.</p>
            <p>Seu e-mail de cadastro é: <strong>' . htmlspecialchars($email) . '</strong></p>
            <p style="text-align: center;">
                <a href="' . $forgot_password_url . '" style="background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;">Recuperar Senha</a>
            </p>

            <br>
            <p>Atenciosamente,</p>
            <p><strong>Equipe Helum</strong></p>
        </div>
    ';
}

function getInvoiceAvailableEmailBody($fullName, $productName) {
    $login_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/index.php';
    $logo_url = 'https://helum.com.br/sistemas/helum_pay/sistema/logo.png';

    return '
        <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="' . $logo_url . '" alt="Logo Helum" style="max-width: 150px;">
            </div>
            <h2>Fatura Disponível - Helum Pay</h2>
            <p>Olá, ' . htmlspecialchars($fullName) . ',</p>
            <p>A fatura referente à sua assinatura do produto <strong>' . htmlspecialchars($productName) . '</strong> já está disponível para pagamento.</p>
            <p>Acesse seu painel para visualizar os detalhes e efetuar o pagamento.</p>
            <p style="text-align: center;">
                <a href="' . $login_url . '" style="background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;">Acessar Painel</a>
            </p>
            <br>
            <p>Atenciosamente,</p>
            <p><strong>Equipe Helum</strong></p>
        </div>
    ';
}

function getInvoiceDueEmailBody($fullName, $productName, $dueDate) {
    $login_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/index.php';
    $logo_url = 'https://helum.com.br/sistemas/helum_pay/sistema/logo.png';

    return '
        <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="' . $logo_url . '" alt="Logo Helum" style="max-width: 150px;">
            </div>
            <h2>Lembrete de Vencimento - Helum Pay</h2>
            <p>Olá, ' . htmlspecialchars($fullName) . ',</p>
            <p>Este é um lembrete de que sua fatura do produto <strong>' . htmlspecialchars($productName) . '</strong> vence em breve.</p>
            <p><strong>Data de Vencimento:</strong> ' . htmlspecialchars($dueDate) . '</p>
            <p>Para evitar a interrupção do seu serviço, por favor, realize o pagamento acessando seu painel.</p>
            <p style="text-align: center;">
                <a href="' . $login_url . '" style="background-color: #ffc107; color: #333; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;">Acessar Painel</a>
            </p>
            <br>
            <p>Atenciosamente,</p>
            <p><strong>Equipe Helum</strong></p>
        </div>
    ';
}

function getPaymentConfirmationEmailBody($fullName, $productName, $paymentDate, $amount) {
    $logo_url = 'https://helum.com.br/sistemas/helum_pay/sistema/logo.png';

    return '
        <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="' . $logo_url . '" alt="Logo Helum" style="max-width: 150px;">
            </div>
            <h2>Confirmação de Pagamento - Helum Pay</h2>
            <p>Olá, ' . htmlspecialchars($fullName) . ',</p>
            <p>Seu pagamento para o produto <strong>' . htmlspecialchars($productName) . '</strong> foi confirmado com sucesso!</p>
            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
            <p><strong>Detalhes do Pagamento:</strong></p>
            <p><strong>Data:</strong> ' . htmlspecialchars($paymentDate) . '<br>
            <strong>Valor:</strong> R$ ' . htmlspecialchars($amount) . '</p>
            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
            <p>Agradecemos a sua preferência.</p>
            <br>
            <p>Atenciosamente,</p>
            <p><strong>Equipe Helum</strong></p>
        </div>
    ';
}

function getPasswordResetEmailBody($username, $reset_link) {
    $logo_url = 'https://helum.com.br/sistemas/helum_pay/sistema/logo.png';

    return '
        <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="' . $logo_url . '" alt="Logo Helum" style="max-width: 150px;">
            </div>
            <h2>Redefinição de Senha - Helum Pay</h2>
            <p>Olá, ' . htmlspecialchars($username) . ',</p>
            <p>Recebemos uma solicitação para redefinir sua senha. Clique no botão abaixo para criar uma nova senha. O link é válido por 1 hora.</p>
            <p style="text-align: center; margin: 30px 0;">
                <a href="' . $reset_link . '" style="background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;">Redefinir Senha</a>
            </p>
            <p>Se você não solicitou esta alteração, por favor, ignore este e-mail.</p>
            <br>
            <p>Atenciosamente,</p>
            <p><strong>Equipe Helum</strong></p>
        </div>
    ';
}

function getLoginCodeEmailBody($code) {
    $logo_url = 'https://helum.com.br/sistemas/helum_pay/sistema/logo.png';

    return '
        <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="' . $logo_url . '" alt="Logo Helum" style="max-width: 150px;">
            </div>
            <h2>Seu Código de Acesso Único - Helum Pay</h2>
            <p>Olá,</p>
            <p>Use o código abaixo para fazer login em sua conta. Este código é válido por 15 minutos.</p>
            <p style="text-align: center; font-size: 24px; letter-spacing: 5px; margin: 30px 0; font-weight: bold;">
                ' . htmlspecialchars($code) . '
            </p>
            <p>Se você não solicitou este código, por favor, ignore este e-mail.</p>
            <br>
            <p>Atenciosamente,</p>
            <p><strong>Equipe Helum</strong></p>
        </div>
    ';
}

function getNewLoginMethodEmailBody($fullName) {
    $login_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/login_code.php';
    $logo_url = 'https://helum.com.br/sistemas/helum_pay/sistema/logo.png';

    return '
        <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="' . $logo_url . '" alt="Logo Helum" style="max-width: 150px;">
            </div>
            <h2>Uma nova maneira de acessar sua conta Helum Pay!</h2>
            <p>Olá, ' . htmlspecialchars($fullName) . ',</p>
            <p>Temos uma ótima notícia! Agora você pode acessar sua conta de forma ainda mais simples e segura, utilizando um <strong>código de uso único</strong> enviado diretamente para o seu e-mail.</p>
            
            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

            <h3>Como funciona o login com código:</h3>
            <ol style="padding-left: 20px;">
                <li>Acesse nossa página de login e clique na opção <strong>"Acessar com um código único no seu e-mail"</strong>.</li>
                <li>Digite o seu endereço de e-mail de cadastro.</li>
                <li>Você receberá um e-mail com um código de acesso exclusivo.</li>
                <li>Insira esse código na tela de login para acessar sua conta.</li>
            </ol>
            
            <p>É rápido, fácil e seguro!</p>

            <p>Convidamos você a experimentar esta nova funcionalidade hoje mesmo.</p>
            <p style="text-align: center;">
                <a href="' . $login_url . '" style="background-color: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;">Testar Agora</a>
            </p>

            <br>
            <p>Atenciosamente,</p>
            <p><strong>Equipe Helum</strong></p>
        </div>
    ';
}

?>
