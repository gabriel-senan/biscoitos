<?php
require_once '../config/database.php';
require_once '../includes/security.php';
require_once '../includes/security_headers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header('Location: categorias.php');
    exit();
}

$sorte_id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario_id'];

// Verificar se a sorte pertence ao usuário
$stmt = $pdo->prepare("
    SELECT s.*, c.nome as categoria_nome, c.icone, c.cor 
    FROM sortes s 
    JOIN categorias c ON s.categoria_id = c.id
    JOIN pedidos p ON p.sorte_id = s.id
    WHERE s.id = ? AND p.usuario_id = ?
");
$stmt->execute([$sorte_id, $usuario_id]);
$sorte = $stmt->fetch();

if (!$sorte) {
    header('Location: perfil.php');
    exit();
}

// Gerar números da sorte aleatórios únicos e em ordem crescente
$numeros = [];
while (count($numeros) < 5) {
    $numero = rand(1, 99);
    if (!in_array($numero, $numeros)) {
        $numeros[] = $numero;
    }
}
sort($numeros); // Ordenar em ordem crescente
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sua Sorte - Biscoitos da Sorte</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body style="background: var(--bg-light);">
    <div class="container" style="padding-top: 20px; max-width: 480px;">
        <a href="perfil.php" style="position: fixed; top: 20px; left: 20px; font-size: 28px; text-decoration: none; color: var(--text-dark); z-index: 1000; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: white; border-radius: 50%; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">✕</a>
        
        <div id="shareContent" style="background: white; border-radius: 24px; padding: 40px 24px; box-shadow: 0 8px 32px rgba(0,0,0,0.12); margin: 20px auto; max-width: 400px;">
            <div style="text-align: center; margin-bottom: 32px;">
                <div style="font-size: 24px; font-weight: 600; color: var(--text-dark); display: flex; align-items: center; justify-content: center; gap: 8px;">
                    🥠 Sua Sorte
                </div>
            </div>
            
            <div style="font-size: 100px; margin: 32px 0; text-align: center; filter: drop-shadow(0 4px 12px rgba(255, 140, 66, 0.3));">🥠</div>
            
            <h2 style="font-size: 20px; margin-bottom: 32px; text-align: center; color: var(--text-dark); font-weight: 600;">O destino sussurra...</h2>
            
            <div style="background: linear-gradient(135deg, #FFF5E6 0%, #FFE5CC 100%); padding: 24px; border-radius: 16px; margin-bottom: 32px; position: relative;">
                <div style="font-size: 40px; color: var(--primary); position: absolute; top: 8px; left: 12px; line-height: 1;">"</div>
                <p style="font-style: italic; color: var(--text-dark); line-height: 1.6; font-size: 16px; text-align: center; padding: 0 20px; margin: 0;">
                    <?= htmlspecialchars($sorte['mensagem']) ?>
                </p>
                <div style="font-size: 40px; color: var(--primary); position: absolute; bottom: 8px; right: 12px; line-height: 1;">"</div>
            </div>

            <div style="margin-bottom: 32px; text-align: center;">
                <p style="font-size: 11px; color: var(--text-light); margin-bottom: 16px; letter-spacing: 1px; font-weight: 600;">NÚMEROS DA SORTE</p>
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <?php foreach ($numeros as $numero): ?>
                        <div style="width: 52px; height: 52px; border-radius: 50%; border: 2px solid var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; color: var(--primary); font-size: 18px;">
                            <?= str_pad($numero, 2, '0', STR_PAD_LEFT) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div style="padding: 0 20px; margin-bottom: 100px;">
            <button onclick="shareToStory()" class="btn btn-primary" style="margin-bottom: 12px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                📤 Compartilhar Sorte
            </button>
            
            <a href="perfil.php" class="btn btn-secondary" style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 12px;">
                🧡 Salvar no meu perfil
            </a>

            <a href="categorias.php" style="display: block; text-align: center; margin-top: 20px; color: var(--text-light); text-decoration: none; font-size: 14px; font-weight: 500;">
                🔄 Abrir outro biscoito
            </a>
        </div>
    </div>

    <script>
        async function shareToStory() {
            const content = document.getElementById('shareContent');
            
            try {
                // Gerar imagem do conteúdo
                const canvas = await html2canvas(content, {
                    backgroundColor: '#ffffff',
                    scale: 2,
                    logging: false,
                    width: content.offsetWidth,
                    height: content.offsetHeight
                });
                
                // Converter para blob
                canvas.toBlob(async (blob) => {
                    const file = new File([blob], 'minha-sorte.png', { type: 'image/png' });
                    
                    // Tentar compartilhar
                    if (navigator.share && navigator.canShare && navigator.canShare({ files: [file] })) {
                        try {
                            await navigator.share({
                                files: [file],
                                title: 'Minha Sorte do Dia',
                                text: '<?= addslashes($sorte['mensagem']) ?>'
                            });
                        } catch (err) {
                            if (err.name !== 'AbortError') {
                                downloadImage(canvas);
                            }
                        }
                    } else {
                        // Fallback: baixar imagem
                        downloadImage(canvas);
                    }
                }, 'image/png');
                
            } catch (error) {
                console.error('Erro ao gerar imagem:', error);
                alert('Erro ao gerar imagem para compartilhar');
            }
        }
        
        function downloadImage(canvas) {
            const link = document.createElement('a');
            link.download = 'minha-sorte.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
    </script>
</body>
</html>
