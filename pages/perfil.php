<?php
require_once '../config/database.php';
require_once '../includes/creditos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: auth.php');
    exit();
}

// Verificar timeout de sessão
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
    session_unset();
    session_destroy();
    header('Location: auth.php?timeout=1');
    exit();
}
$_SESSION['last_activity'] = time();

$usuario_id = $_SESSION['usuario_id'];

// Filtro de categoria
$filtro_categoria = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;

// Buscar dados do usuário
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch();

// Obter saldo de créditos
$saldo_creditos = obterSaldoCreditos($pdo, $usuario_id);

// Buscar categorias para o filtro
$categorias = $pdo->query("SELECT * FROM categorias WHERE ativo = 1")->fetchAll();

// Buscar sortes do usuário com filtro
$query = "
    SELECT s.*, c.nome as categoria_nome, c.icone, c.cor, p.data_pagamento
    FROM sortes s
    JOIN pedidos p ON p.sorte_id = s.id
    JOIN categorias c ON s.categoria_id = c.id
    WHERE p.usuario_id = ? AND p.status = 'pago'
";

if ($filtro_categoria > 0) {
    $query .= " AND s.categoria_id = ?";
}

$query .= " ORDER BY p.data_pagamento DESC";

$stmt = $pdo->prepare($query);
if ($filtro_categoria > 0) {
    $stmt->execute([$usuario_id, $filtro_categoria]);
} else {
    $stmt->execute([$usuario_id]);
}
$sortes = $stmt->fetchAll();

// Contar biscoitos abertos
$total_biscoitos = count($sortes);

// Calcular data de membro
$data_cadastro = new DateTime($usuario['data_cadastro']);
$mes_ano = $data_cadastro->format('F Y');
$meses_pt = [
    'January' => 'Janeiro', 'February' => 'Fevereiro', 'March' => 'Março',
    'April' => 'Abril', 'May' => 'Maio', 'June' => 'Junho',
    'July' => 'Julho', 'August' => 'Agosto', 'September' => 'Setembro',
    'October' => 'Outubro', 'November' => 'Novembro', 'December' => 'Dezembro'
];
$mes_ano = str_replace(array_keys($meses_pt), array_values($meses_pt), $mes_ano);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Biscoitos da Sorte</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="header">
        <a href="categorias.php" style="font-size: 24px; text-decoration: none; color: var(--text-dark);">←</a>
        <div class="logo">Meu Perfil</div>
        <div style="display: flex; align-items: center; gap: 16px;">
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
            <a href="../admin/index.php" style="text-decoration: none; color: var(--text-dark); font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 6px; padding: 8px 16px; background: var(--secondary); border-radius: 8px; transition: all 0.3s;">
                📊 Dashboard
            </a>
            <?php endif; ?>
            <button onclick="openEditModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--primary); padding: 8px;">
                ⚙️
            </button>
        </div>
    </div>

    <div class="container" style="padding-top: 40px;">
        <?php if (isset($_GET['success'])): ?>
            <div style="background: #E8F5E9; color: #2E7D32; padding: 8px; border-radius: 8px; margin-bottom: 12px; text-align: center; font-size: 13px;">
                ✅ Perfil atualizado!
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div style="background: #FFE5E5; color: #D32F2F; padding: 8px; border-radius: 8px; margin-bottom: 12px; text-align: center; font-size: 13px;">
                ❌ <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <!-- Card de Créditos -->
        <div class="card" style="background: linear-gradient(135deg, #FF8C42 0%, #FFB366 100%); color: white; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 14px; opacity: 0.9; margin-bottom: 4px;">Seus Créditos</div>
                    <div style="font-size: 32px; font-weight: 700;"><?= $saldo_creditos ?></div>
                    <div style="font-size: 12px; opacity: 0.8;">1 crédito = 1 biscoito</div>
                </div>
                <a href="comprar_creditos.php" style="background: white; color: var(--primary); padding: 12px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    + Comprar
                </a>
            </div>
        </div>

        <div style="text-align: center; margin-bottom: 20px;">
            <form id="photoForm" enctype="multipart/form-data" class="photo-upload" style="display: inline-block;">
                <input type="file" id="photoInput" name="foto" accept="image/*" onchange="uploadPhoto(this)">
                <label for="photoInput" style="cursor: pointer;">
                    <?php if (!empty($usuario['foto_perfil']) && file_exists(__DIR__ . '/../' . $usuario['foto_perfil'])): ?>
                        <img src="../<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto" class="photo-preview" id="photoPreview">
                    <?php else: ?>
                        <div class="photo-preview" id="photoPreview" style="background: linear-gradient(135deg, #FF8C42 0%, #FFB366 100%); display: flex; align-items: center; justify-content: center; font-size: 48px; color: white;">
                            👤
                        </div>
                    <?php endif; ?>
                    <div class="photo-upload-btn">
                        📷
                    </div>
                </label>
            </form>
            <h2 style="margin: 12px 0 4px; font-size: 20px; font-weight: 600; letter-spacing: -0.02em;"><?= htmlspecialchars($usuario['nome_completo']) ?></h2>
            <p style="color: var(--text-light); font-size: 13px; margin-bottom: 8px;">Membro desde <?= $mes_ano ?></p>
            <div style="background: #FFE5CC; display: inline-block; padding: 6px 14px; border-radius: 20px; margin-top: 8px; font-weight: 600; color: var(--primary); font-size: 13px;">
                🥠 <?= $total_biscoitos ?> BISCOITOS ABERTOS
            </div>
        </div>

        <div class="filter-scroll">
            <a href="perfil.php" class="btn" style="background: <?= $filtro_categoria == 0 ? 'var(--text-dark)' : 'white' ?>; color: <?= $filtro_categoria == 0 ? 'white' : 'var(--text-dark)' ?>; padding: 8px 18px; white-space: nowrap; border-radius: 20px; font-size: 13px; width: auto; text-decoration: none; border: 2px solid <?= $filtro_categoria == 0 ? 'var(--text-dark)' : '#E5E5E5' ?>; flex-shrink: 0; transition: all 0.3s;">
                ✨ Todas
            </a>
            <?php foreach ($categorias as $cat): ?>
            <a href="perfil.php?categoria=<?= $cat['id'] ?>" class="btn" style="background: <?= $filtro_categoria == $cat['id'] ? 'var(--text-dark)' : 'white' ?>; color: <?= $filtro_categoria == $cat['id'] ? 'white' : 'var(--text-dark)' ?>; padding: 8px 18px; white-space: nowrap; border-radius: 20px; font-size: 13px; width: auto; text-decoration: none; border: 2px solid <?= $filtro_categoria == $cat['id'] ? 'var(--text-dark)' : '#E5E5E5' ?>; flex-shrink: 0; transition: all 0.3s;">
                <?= htmlspecialchars($cat['icone']) ?> <?= htmlspecialchars($cat['nome']) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <h3 style="margin-bottom: 12px; text-align: center; font-size: 16px; font-weight: 600; letter-spacing: -0.01em;">Minhas Sortes</h3>

        <?php if (empty($sortes)): ?>
            <div class="card" style="text-align: center; padding: 30px;">
                <div style="font-size: 40px; margin-bottom: 12px;">🥠</div>
                <p style="color: var(--text-light); font-size: 14px;">Você ainda não abriu nenhum biscoito.</p>
                <a href="categorias.php" class="btn btn-primary" style="margin-top: 16px; display: inline-block; padding: 10px 20px; font-size: 14px;">
                    Abrir meu primeiro biscoito
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($sortes as $sorte): ?>
                <div class="card" style="margin-bottom: 12px; padding: 14px;">
                    <div style="display: flex; align-items: center; margin-bottom: 8px;">
                        <div style="background: <?= htmlspecialchars($sorte['cor']) ?>20; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-size: 14px;">
                            <?= htmlspecialchars($sorte['icone']) ?>
                        </div>
                        <div style="flex: 1;">
                            <strong style="text-transform: uppercase; font-size: 11px; color: <?= htmlspecialchars($sorte['cor']) ?>;">
                                <?= htmlspecialchars($sorte['categoria_nome']) ?>
                            </strong>
                        </div>
                        <span style="font-size: 11px; color: var(--text-light);">
                            <?= date('d M', strtotime($sorte['data_pagamento'])) ?>
                        </span>
                    </div>

                    <p style="font-style: italic; color: var(--text-dark); line-height: 1.5; margin-bottom: 10px; font-size: 13px;">
                        "<?= htmlspecialchars($sorte['mensagem']) ?>"
                    </p>

                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 6px; color: var(--text-light); font-size: 12px;">
                            🔥 3 reações
                        </div>
                        <button onclick="window.location.href='sorte.php?id=<?= $sorte['id'] ?>'" 
                                style="background: none; border: none; color: var(--text-light); cursor: pointer; font-size: 18px;">
                            📤
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="categorias.php" class="fab" title="Abrir novo biscoito">
            +
        </a>
        
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <button onclick="openUploadModal()" class="fab" style="bottom: 100px; background: #4CAF50;" title="Upload de frases">
            📄
        </button>
        <?php endif; ?>
    </div>

    <!-- Navegação Mobile -->
    <nav class="mobile-nav">
        <a href="categorias.php" class="mobile-nav-item">
            <span>🥠</span>
            Sortes
        </a>
        <a href="comprar_creditos.php" class="mobile-nav-item">
            <span>💳</span>
            Créditos
        </a>
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <a href="../admin/index.php" class="mobile-nav-item">
            <span>📊</span>
            Admin
        </a>
        <?php endif; ?>
        <a href="perfil.php" class="mobile-nav-item active">
            <span>👤</span>
            Perfil
        </a>
        <button onclick="openEditModal()" class="mobile-nav-item" style="background: none; border: none; cursor: pointer;">
            <span>⚙️</span>
            Config
        </button>
    </nav>

    <!-- Botão de Configurações Fixo -->
    <button class="settings-fixed-btn" onclick="openEditModal()">
        ⚙️
    </button>

    <!-- Modal de Edição -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeEditModal()">×</button>
            <h2 style="margin-bottom: 24px;">Editar Perfil</h2>
            
            <form id="editForm" method="POST" action="atualizar_perfil.php">
                <div class="form-group">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($usuario['nome_completo']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Nova Senha (deixe em branco para manter)</label>
                    <input type="password" name="nova_senha" class="form-control" placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label>Confirmar Nova Senha</label>
                    <input type="password" name="confirmar_senha" class="form-control" placeholder="••••••••">
                </div>

                <button type="submit" class="btn btn-primary">
                    Salvar Alterações
                </button>

                <button type="button" onclick="closeEditModal()" class="btn btn-secondary" style="margin-top: 12px;">
                    Cancelar
                </button>

                <hr style="margin: 24px 0; border: none; border-top: 1px solid #E5E5E5;">

                <a href="logout.php" style="display: block; text-align: center; color: #D32F2F; text-decoration: none; font-weight: 600;">
                    🚪 Sair da Conta
                </a>
            </form>
        </div>
    </div>

    <!-- Modal de Upload de Frases (Admin) -->
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeUploadModal()">×</button>
            <h2 style="margin-bottom: 16px;">Upload de Frases</h2>
            <p style="color: var(--text-light); font-size: 14px; margin-bottom: 24px;">
                Faça upload de uma planilha Excel (.xlsx ou .csv) com as colunas: Categoria e Frase
            </p>
            
            <div style="background: #F5F5F5; padding: 16px; border-radius: 12px; margin-bottom: 20px;">
                <p style="font-size: 13px; font-weight: 600; margin-bottom: 8px;">📋 Formato da Planilha:</p>
                <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--primary); color: white;">
                            <th style="padding: 8px; text-align: left; border-radius: 4px 0 0 4px;">Categoria</th>
                            <th style="padding: 8px; text-align: left; border-radius: 0 4px 4px 0;">Frase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background: white;">
                            <td style="padding: 8px; border-bottom: 1px solid #E5E5E5;">Amor</td>
                            <td style="padding: 8px; border-bottom: 1px solid #E5E5E5;">O amor verdadeiro...</td>
                        </tr>
                        <tr style="background: white;">
                            <td style="padding: 8px;">Dinheiro</td>
                            <td style="padding: 8px;">Sua disciplina abrirá...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <form id="uploadForm" enctype="multipart/form-data" onsubmit="uploadSpreadsheet(event)">
                <div class="form-group">
                    <label>Selecione a planilha</label>
                    <input type="file" name="planilha" id="planilhaInput" class="form-control" accept=".xlsx,.xls,.csv" required>
                </div>

                <div id="uploadProgress" style="display: none; margin-bottom: 16px;">
                    <div style="background: #E5E5E5; height: 8px; border-radius: 4px; overflow: hidden;">
                        <div id="progressBar" style="background: var(--primary); height: 100%; width: 0%; transition: width 0.3s;"></div>
                    </div>
                    <p id="uploadStatus" style="font-size: 13px; color: var(--text-light); margin-top: 8px; text-align: center;"></p>
                </div>

                <button type="submit" class="btn btn-primary" id="uploadBtn">
                    📤 Fazer Upload
                </button>

                <button type="button" onclick="closeUploadModal()" class="btn btn-secondary" style="margin-top: 12px;">
                    Cancelar
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>

    <script>
        function openEditModal() {
            document.getElementById('editModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function uploadPhoto(input) {
            if (input.files && input.files[0]) {
                const formData = new FormData();
                formData.append('foto', input.files[0]);
                
                fetch('upload_foto.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const preview = document.getElementById('photoPreview');
                        if (preview.tagName === 'IMG') {
                            preview.src = data.foto_url + '?' + new Date().getTime();
                        } else {
                            preview.outerHTML = '<img src="' + data.foto_url + '" alt="Foto" class="photo-preview" id="photoPreview">';
                        }
                    } else {
                        alert(data.message || 'Erro ao fazer upload');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao fazer upload da foto');
                });
            }
        }
    </script>

    <script>
        function openUploadModal() {
            document.getElementById('uploadModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        async function uploadSpreadsheet(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const progressDiv = document.getElementById('uploadProgress');
            const progressBar = document.getElementById('progressBar');
            const statusText = document.getElementById('uploadStatus');
            const uploadBtn = document.getElementById('uploadBtn');
            
            progressDiv.style.display = 'block';
            uploadBtn.disabled = true;
            uploadBtn.textContent = '⏳ Processando...';
            statusText.textContent = 'Enviando arquivo...';
            progressBar.style.width = '30%';
            
            try {
                const response = await fetch('upload_frases.php', {
                    method: 'POST',
                    body: formData
                });
                
                progressBar.style.width = '70%';
                statusText.textContent = 'Processando frases...';
                
                const result = await response.json();
                
                progressBar.style.width = '100%';
                
                if (result.success) {
                    statusText.textContent = `✅ ${result.message}`;
                    statusText.style.color = '#4CAF50';
                    
                    setTimeout(() => {
                        closeUploadModal();
                        location.reload();
                    }, 2000);
                } else {
                    statusText.textContent = `❌ ${result.message}`;
                    statusText.style.color = '#D32F2F';
                    uploadBtn.disabled = false;
                    uploadBtn.textContent = '📤 Fazer Upload';
                }
            } catch (error) {
                console.error('Erro:', error);
                statusText.textContent = '❌ Erro ao fazer upload';
                statusText.style.color = '#D32F2F';
                uploadBtn.disabled = false;
                uploadBtn.textContent = '📤 Fazer Upload';
            }
        }
    </script>
