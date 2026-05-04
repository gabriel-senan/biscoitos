<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biscoitos da Sorte</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }
        .container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px;
            box-sizing: border-box;
            overflow: hidden;
        }
        .hero {
            text-align: center;
            flex-shrink: 0;
        }
        .card {
            flex-shrink: 0;
        }
        footer {
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero">
            <div style="font-size: 64px; margin-bottom: 12px;">🥠</div>
            
            <h1 style="font-size: 22px; line-height: 1.3; margin-bottom: 8px; font-weight: 600; letter-spacing: -0.3px;">Que mensagem o destino<br>reservou para você?</h1>
            <p style="font-size: 14px; margin-bottom: 20px; color: var(--text-light);">Descubra sua sorte com uma mensagem exclusiva</p>
            
            <a href="auth.php?action=register" class="btn btn-primary" style="margin-bottom: 10px; padding: 14px 32px; font-size: 15px; font-weight: 600;">
                Começar agora
            </a>
            
            <p style="font-size: 13px; color: #999; margin: 0;">
                Já é um membro? <a href="auth.php?action=login" style="color: var(--primary); text-decoration: none; font-weight: 600;">Entrar</a>
            </p>

            <div class="card" style="margin-top: 12px; padding: 18px;">
                <h3 style="text-align: center; margin-bottom: 16px; font-size: 16px; font-weight: 600;">Como funciona</h3>
                
                <div style="display: flex; align-items: start; margin-bottom: 16px;">
                    <div style="background: var(--primary); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0; font-weight: 700; color: white; font-size: 14px;">1</div>
                    <div>
                        <h4 style="font-size: 14px; margin-bottom: 2px; font-weight: 600;">Compre créditos</h4>
                        <p style="color: var(--text-light); font-size: 12px; line-height: 1.4; margin: 0;">R$ 5,00 = 5 créditos. Ganhe bônus!</p>
                    </div>
                </div>

                <div style="display: flex; align-items: start; margin-bottom: 16px;">
                    <div style="background: var(--primary); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0; font-weight: 700; color: white; font-size: 14px;">2</div>
                    <div>
                        <h4 style="font-size: 14px; margin-bottom: 2px; font-weight: 600;">Escolha sua categoria</h4>
                        <p style="color: var(--text-light); font-size: 12px; line-height: 1.4; margin: 0;">Amor, Dinheiro, Trabalho, Vida ou Amizades</p>
                    </div>
                </div>

                <div style="display: flex; align-items: start; margin-bottom: 18px;">
                    <div style="background: var(--primary); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px; flex-shrink: 0; font-weight: 700; color: white; font-size: 14px;">3</div>
                    <div>
                        <h4 style="font-size: 14px; margin-bottom: 2px; font-weight: 600;">Abra seu biscoito</h4>
                        <p style="color: var(--text-light); font-size: 12px; line-height: 1.4; margin: 0;">Use 1 crédito e receba sua mensagem</p>
                    </div>
                </div>

                <div style="text-align: center; padding-top: 14px; border-top: 1px solid #F0F0F0;">
                    <p style="color: var(--text-light); margin: 0; font-size: 12px;">
                        💳 Créditos não expiram • Ganhe bônus
                    </p>
                </div>
            </div>
        </div>

        <footer style="text-align: center; padding: 14px 0 0 0; color: var(--text-light); font-size: 11px; border-top: 1px solid #E5E5E5; margin-top: auto;">
            <p style="margin-bottom: 2px;">🥠 Biscoitos da Sorte</p>
            <p style="margin: 0;">© 2025 Todos os direitos reservados</p>
        </footer>
    </div>
</body>
</html>
