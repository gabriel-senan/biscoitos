<?php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../pages/auth.php');
    exit();
}

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $categoria_id = (int)$_POST['categoria_id'];
            $mensagem = trim($_POST['mensagem']);
            
            if (!empty($mensagem)) {
                $stmt = $pdo->prepare("INSERT INTO sortes (categoria_id, mensagem) VALUES (?, ?)");
                $stmt->execute([$categoria_id, $mensagem]);
                $success = "Sorte adicionada com sucesso!";
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM sortes WHERE id = ? AND usada = 0");
            $stmt->execute([$id]);
            $success = "Sorte removida com sucesso!";
        }
    }
}

// Buscar categorias
$categorias = $pdo->query("SELECT * FROM categorias WHERE ativo = 1")->fetchAll();

// Buscar sortes
$filtro_categoria = $_GET['categoria'] ?? '';
$query = "SELECT s.*, c.nome as categoria_nome, c.icone, c.cor 
          FROM sortes s 
          JOIN categorias c ON s.categoria_id = c.id";

if ($filtro_categoria) {
    $query .= " WHERE s.categoria_id = " . (int)$filtro_categoria;
}

$query .= " ORDER BY s.criada_em DESC";
$sortes = $pdo->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Sortes - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="header">
        <a href="index.php" style="font-size: 24px; text-decoration: none;">←</a>
        <div class="logo">Gerenciar Sortes</div>
        <div></div>
    </div>

    <div class="container" style="padding-top: 40px;">
        <?php if (isset($success)): ?>
            <div style="background: #E8F5E9; color: #2E7D32; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3 style="margin-bottom: 20px; text-align: center;">Adicionar Nova Sorte</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label>Categoria</label>
                    <select name="categoria_id" class="form-control" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>">
                                <?= htmlspecialchars($cat['icone']) ?> <?= htmlspecialchars($cat['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Mensagem da Sorte</label>
                    <textarea name="mensagem" class="form-control" rows="3" 
                              placeholder="Digite uma mensagem inspiradora..." required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Adicionar Sorte</button>
            </form>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="text-align: center; flex: 1;">Sortes Cadastradas</h3>
                <select onchange="window.location.href='?categoria='+this.value" 
                        style="padding: 8px 12px; border: 2px solid #E5E5E5; border-radius: 8px;">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $filtro_categoria == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php foreach ($sortes as $sorte): ?>
                <div class="card" style="margin-bottom: 12px; background: var(--bg-light);">
                    <div style="display: flex; align-items: start; gap: 12px;">
                        <div style="background: <?= htmlspecialchars($sorte['cor']) ?>20; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <?= htmlspecialchars($sorte['icone']) ?>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-size: 12px; color: var(--text-light); margin-bottom: 4px;">
                                <?= htmlspecialchars($sorte['categoria_nome']) ?>
                                <?php if ($sorte['usada']): ?>
                                    <span style="background: #FFE5E5; color: #D32F2F; padding: 2px 8px; border-radius: 4px; margin-left: 8px;">USADA</span>
                                <?php else: ?>
                                    <span style="background: #E8F5E9; color: #2E7D32; padding: 2px 8px; border-radius: 4px; margin-left: 8px;">DISPONÍVEL</span>
                                <?php endif; ?>
                            </div>
                            <p style="font-size: 14px; margin-bottom: 8px;">
                                "<?= htmlspecialchars($sorte['mensagem']) ?>"
                            </p>
                            <?php if (!$sorte['usada']): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $sorte['id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: #D32F2F; cursor: pointer; font-size: 12px;">
                                        🗑️ Excluir
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
