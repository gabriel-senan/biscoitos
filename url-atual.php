<?php
/**
 * Página que mostra a URL pública atual do Cloudflare Tunnel
 * Acesse via: http://192.168.1.108/url-atual.php
 */

header('Content-Type: text/html; charset=utf-8');

// Pegar URL do log do cloudflared
$output = shell_exec("sudo journalctl -u cloudflared-quick -n 100 2>&1 | grep -o 'https://.*\.trycloudflare\.com' | tail -1");
$url = trim($output);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Pública - Biscoitos da Sorte</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        
        h1 {
            font-size: 32px;
            margin-bottom: 10px;
            color: #2C2C2C;
        }
        
        .emoji {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .url-box {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 12px;
            margin: 30px 0;
            word-break: break-all;
        }
        
        .url {
            font-size: 18px;
            color: #667eea;
            font-weight: 600;
            font-family: monospace;
        }
        
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin: 10px;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-secondary:hover {
            background: #f5f5f5;
        }
        
        .info {
            background: #fff3cd;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 14px;
            color: #856404;
        }
        
        .success {
            color: #28a745;
            font-weight: 600;
            margin-top: 10px;
            display: none;
        }
        
        .qrcode {
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="emoji">🥠</div>
        <h1>Biscoitos da Sorte</h1>
        <p class="subtitle">URL Pública Atual</p>
        
        <?php if ($url): ?>
            <div class="url-box">
                <div class="url" id="urlText"><?= htmlspecialchars($url) ?></div>
            </div>
            
            <div id="qrcode" class="qrcode"></div>
            
            <button class="btn" onclick="copyURL()">📋 Copiar URL</button>
            <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="btn btn-secondary">🚀 Abrir Site</a>
            
            <div class="success" id="successMsg">✅ URL copiada!</div>
            
            <div class="info">
                💡 Esta URL é temporária e muda quando o servidor reinicia. Para ter uma URL fixa, configure um domínio próprio.
            </div>
        <?php else: ?>
            <div class="url-box">
                <div class="url" style="color: #dc3545;">❌ Serviço não está rodando</div>
            </div>
            
            <div class="info">
                Verifique se o Cloudflare Tunnel está ativo:<br>
                <code>sudo systemctl status cloudflared-quick</code>
            </div>
        <?php endif; ?>
        
        <button class="btn btn-secondary" onclick="location.reload()" style="margin-top: 20px;">
            🔄 Atualizar
        </button>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        <?php if ($url): ?>
        // Gerar QR Code
        new QRCode(document.getElementById('qrcode'), {
            text: '<?= htmlspecialchars($url) ?>',
            width: 200,
            height: 200,
            colorDark: '#2C2C2C',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });
        <?php endif; ?>
        
        function copyURL() {
            const url = document.getElementById('urlText').textContent;
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(() => {
                    showSuccess();
                });
            } else {
                // Fallback
                const textarea = document.createElement('textarea');
                textarea.value = url;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                showSuccess();
            }
        }
        
        function showSuccess() {
            const msg = document.getElementById('successMsg');
            msg.style.display = 'block';
            setTimeout(() => {
                msg.style.display = 'none';
            }, 2000);
        }
        
        // Auto-refresh a cada 30 segundos
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
