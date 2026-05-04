<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/pix.php';
require_once '../includes/creditos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || !isset($_POST['pacote_creditos'])) {
    header('Location: comprar_creditos.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$creditos = (int)$_POST['pacote_creditos'];
$bonus = (int)$_POST['pacote_bonus'];
$valor = (float)$_POST['pacote_valor'];
$total_creditos = $creditos + $bonus;

// Gerar código PIX
$txid = 'CRED' . strtoupper(substr(uniqid(), -8));
$codigoPix = gerarCodigoPix($valor, PIX_CHAVE, PIX_BENEFICIARIO, PIX_CIDADE, $txid);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - Biscoitos da Sorte</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="header">
        <a href="comprar_creditos.php" style="font-size: 24px; text-decoration: none; color: var(--text-dark);">←</a>
        <div class="logo">Pagamento</div>
        <a href="perfil.php" style="font-size: 24px; text-decoration: none; color: var(--text-dark);">
            Perfil
        </a>
    </div>

    <div class="container" style="padding-top: 40px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <h1 style="font-size: 26px; margin-bottom: 8px; font-weight: 600;">Quase lá!</h1>
            <p style="color: var(--text-light); font-size: 15px;">
                Confirme o pagamento para receber seus créditos.
            </p>
        </div>

        <div class="card">
            <div style="text-align: center; margin-bottom: 14px;">
                <div style="background: linear-gradient(135deg, #FF8C42 0%, #FFB366 100%); padding: 4px 10px; border-radius: 6px; color: white; font-size: 10px; font-weight: 700; display: inline-block; letter-spacing: 0.05em; text-transform: uppercase;">
                    Resumo
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px; justify-content: center;">
                <div style="font-size: 60px; font-weight: 700; color: var(--primary);">$</div>
                <div style="text-align: left;">
                    <h3 style="margin-bottom: 4px; font-size: 17px; font-weight: 600;"><?= $total_creditos ?> Créditos</h3>
                    <p style="font-size: 13px; color: var(--text-light);">
                        <?= $creditos ?> créditos
                        <?php if ($bonus > 0): ?>
                            + <?= $bonus ?> bônus
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div style="background: var(--bg-light); padding: 18px; border-radius: 12px; text-align: center;">
                <div style="font-size: 32px; font-weight: 700; color: var(--primary);">R$ <?= number_format($valor, 2, ',', '.') ?></div>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 18px; font-size: 17px; font-weight: 600; text-align: center;">Pagamento via PIX</h3>

            <div id="pixArea" style="text-align: center;">
                <!-- QR Code -->
                <div style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; display: inline-block; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <div id="qrcode" style="width: 200px; height: 200px; margin: 0 auto;"></div>
                </div>

                <p style="font-size: 14px; color: var(--text-light); margin-bottom: 16px;">
                    Escaneie o QR Code com o app do seu banco
                </p>

                <!-- Código Copia e Cola -->
                <div style="background: var(--bg-light); padding: 16px; border-radius: 12px; margin-bottom: 16px;">
                    <p style="font-size: 12px; color: var(--text-light); margin-bottom: 8px; font-weight: 600;">
                        OU COPIE O CÓDIGO PIX
                    </p>
                    <div style="background: white; padding: 12px; border-radius: 8px; border: 2px dashed #E5E5E5; word-break: break-all; font-family: monospace; font-size: 11px; color: var(--text-dark); margin-bottom: 12px;" id="pixCode">
                        <?= $codigoPix ?>
                    </div>
                    <button type="button" onclick="copyPixCode()" class="btn btn-secondary" style="width: 100%; padding: 12px;">
                        Copiar código PIX
                    </button>
                </div>

                <div style="background: #E8F5E9; padding: 12px; border-radius: 8px; margin-bottom: 16px;">
                    <p style="font-size: 13px; color: #2E7D32; margin: 0;">
                        Aguardando pagamento...
                    </p>
                </div>

                <form action="confirmar_compra_creditos.php" method="POST" id="paymentForm">
                    <input type="hidden" name="creditos" value="<?= $creditos ?>">
                    <input type="hidden" name="bonus" value="<?= $bonus ?>">
                    <input type="hidden" name="valor" value="<?= $valor ?>">
                    <input type="hidden" name="txid" value="<?= $txid ?>">
                    
                    <button type="submit" class="btn btn-primary" style="margin-bottom: 12px;">
                        Já fiz o pagamento
                    </button>
                </form>

                <div style="text-align: center; margin-top: 16px; font-size: 12px; color: var(--text-light);">
                    Pagamento 100% seguro
                </div>
            </div>
        </div>
    </div>

    <!-- Navegação Mobile -->
    <nav class="mobile-nav">
        <a href="categorias.php" class="mobile-nav-item">
            <span>Sortes</span>
        </a>
        <a href="comprar_creditos.php" class="mobile-nav-item">
            <span>Créditos</span>
        </a>
        <a href="perfil.php" class="mobile-nav-item">
            <span>Perfil</span>
        </a>
    </nav>

    <!-- QR Code Library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

    <script>
        // Gerar QR Code
        const pixCode = document.getElementById('pixCode').textContent.trim();
        new QRCode(document.getElementById('qrcode'), {
            text: pixCode,
            width: 200,
            height: 200,
            colorDark: '#2C2C2C',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });

        // Copiar código PIX
        function copyPixCode() {
            const code = document.getElementById('pixCode').textContent.trim();
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(code).then(() => {
                    showCopySuccess();
                }).catch(() => {
                    fallbackCopy(code);
                });
            } else {
                fallbackCopy(code);
            }
        }

        function fallbackCopy(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                document.execCommand('copy');
                showCopySuccess();
            } catch (err) {
                alert('Erro ao copiar. Por favor, copie manualmente.');
            }
            
            document.body.removeChild(textarea);
        }

        function showCopySuccess() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Código copiado!';
            btn.style.background = '#7ED321';
            btn.style.color = 'white';
            btn.style.borderColor = '#7ED321';
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = 'white';
                btn.style.color = 'var(--text-dark)';
                btn.style.borderColor = '#E5E5E5';
            }, 2000);
        }
    </script>
</body>
</html>
