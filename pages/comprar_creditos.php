<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../includes/creditos.php';
require_once '../includes/security.php';
require_once '../includes/security_headers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: auth.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$saldo = obterSaldoCreditos($pdo, $usuario_id);

// Pacotes de créditos disponíveis
$pacotes = [
    ['creditos' => 5, 'valor' => 5.00, 'bonus' => 0],
    ['creditos' => 10, 'valor' => 10.00, 'bonus' => 0],
    ['creditos' => 25, 'valor' => 25.00, 'bonus' => 5],
    ['creditos' => 50, 'valor' => 50.00, 'bonus' => 10],
    ['creditos' => 100, 'valor' => 100.00, 'bonus' => 25],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar Créditos</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="header">
        <a href="categorias.php" style="font-size: 24px; text-decoration: none; color: var(--text-dark);">←</a>
        <div class="logo">Créditos</div>
        <a href="perfil.php" style="font-size: 24px; text-decoration: none; color: var(--text-dark);">
            Perfil
        </a>
    </div>

    <div class="container" style="padding-top: 40px;">
        <!-- Saldo Atual -->
        <div class="card" style="background: linear-gradient(135deg, #FF8C42 0%, #FFB366 100%); color: white; text-align: center; margin-bottom: 24px;">
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Seu Saldo</div>
            <div style="font-size: 48px; font-weight: 700; margin-bottom: 4px;"><?= $saldo ?></div>
            <div style="font-size: 16px; opacity: 0.9;">créditos</div>
        </div>

        <!-- Pacotes de Créditos -->
        <?php foreach ($pacotes as $pacote): ?>
            <?php 
                $total_creditos = $pacote['creditos'] + $pacote['bonus'];
                $economia = $pacote['bonus'] > 0 ? round(($pacote['bonus'] / $pacote['creditos']) * 100) : 0;
            ?>
            <div class="card" style="margin-bottom: 16px; position: relative;">
                <?php if ($pacote['bonus'] > 0): ?>
                    <div style="position: absolute; top: -8px; right: 16px; background: #7ED321; color: white; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 700; box-shadow: 0 2px 8px rgba(126, 211, 33, 0.3);">
                        +<?= $pacote['bonus'] ?> BÔNUS
                    </div>
                <?php endif; ?>
                
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 32px; font-weight: 700; color: var(--primary); margin-bottom: 4px;">
                            <?= $total_creditos ?>
                        </div>
                        <div style="font-size: 14px; color: var(--text-light);">
                            créditos
                            <?php if ($pacote['bonus'] > 0): ?>
                                <span style="color: #7ED321; font-weight: 600;">(<?= $pacote['creditos'] ?> + <?= $pacote['bonus'] ?> bônus)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <form action="processar_compra_creditos.php" method="POST" style="margin: 0;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="pacote_creditos" value="<?= $pacote['creditos'] ?>">
                        <input type="hidden" name="pacote_bonus" value="<?= $pacote['bonus'] ?>">
                        <input type="hidden" name="pacote_valor" value="<?= $pacote['valor'] ?>">
                        <button type="submit" class="btn btn-primary" style="padding: 12px 24px; white-space: nowrap;">
                            R$ <?= number_format($pacote['valor'], 2, ',', '.') ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Informações -->
        <div style="background: #F5F5F5; padding: 20px; border-radius: 12px; margin-top: 32px; margin-bottom: 100px;">
            <h3 style="font-size: 16px; margin-bottom: 12px; font-weight: 600;">Como funciona?</h3>
            <ul style="font-size: 14px; color: var(--text-light); line-height: 1.8; padding-left: 20px;">
                <li>Cada crédito permite abrir 1 biscoito da sorte</li>
                <li>R$ 5,00 = 5 créditos</li>
                <li>Pacotes maiores ganham créditos bônus</li>
                <li>Créditos não expiram</li>
                <li>Pagamento via PIX instantâneo</li>
            </ul>
        </div>
    </div>

    <!-- Navegação Mobile -->
    <nav class="mobile-nav">
        <a href="categorias.php" class="mobile-nav-item">
            <span>Sortes</span>
        </a>
        <a href="comprar_creditos.php" class="mobile-nav-item active">
            <span>Créditos</span>
        </a>
        <a href="perfil.php" class="mobile-nav-item">
            <span>Perfil</span>
        </a>
    </nav>
</body>
</html>
