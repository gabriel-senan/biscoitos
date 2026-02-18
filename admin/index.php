<?php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../pages/auth.php');
    exit();
}

// Verificar se é admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../pages/categorias.php');
    exit();
}

// Verificar timeout de sessão
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
    session_unset();
    session_destroy();
    header('Location: ../pages/auth.php?timeout=1');
    exit();
}
$_SESSION['last_activity'] = time();

// Estatísticas
$stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos WHERE status = 'pago'");
$total_vendas = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT SUM(valor) as total FROM pedidos WHERE status = 'pago'");
$valor_total = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as total FROM sortes WHERE usada = 1");
$sortes_usadas = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM sortes WHERE usada = 0");
$sortes_disponiveis = $stmt->fetch()['total'];

// Categorias mais escolhidas
$stmt = $pdo->query("
    SELECT c.nome, c.icone, c.cor, COUNT(p.id) as total,
           ROUND(COUNT(p.id) * 100.0 / (SELECT COUNT(*) FROM pedidos WHERE status = 'pago'), 0) as percentual
    FROM categorias c
    LEFT JOIN pedidos p ON c.id = p.categoria_id AND p.status = 'pago'
    GROUP BY c.id
    ORDER BY total DESC
");
$categorias_stats = $stmt->fetchAll();

// Vendas da semana (últimos 7 dias)
$stmt = $pdo->query("
    SELECT DATE(data_pagamento) as dia, COUNT(*) as total
    FROM pedidos
    WHERE status = 'pago' AND data_pagamento >= datetime('now', '-7 days')
    GROUP BY DATE(data_pagamento)
    ORDER BY dia
");
$vendas_semana = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Biscoitos da Sorte</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="header">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="../pages/categorias.php" style="font-size: 24px; text-decoration: none; color: var(--text-dark);">←</a>
            <div class="logo">Visão Geral</div>
        </div>
        <div style="display: flex; align-items: center; gap: 16px;">
            <a href="../pages/perfil.php" style="font-size: 24px; text-decoration: none; color: var(--text-dark);">
                👤
            </a>
        </div>
    </div>

    <div class="container" style="padding-top: 40px;">
        <h1 style="font-size: 32px; margin-bottom: 8px; text-align: center;">Olá, Admin! 👋</h1>
        <p style="color: var(--text-light); margin-bottom: 32px; text-align: center;">
            Resumo dos biscoitos hoje
        </p>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-icon" style="background: #E8F5E9;">💵</div>
                <div class="stat-label">Vendas Totais</div>
                <div class="stat-value">R$ <?= number_format($valor_total, 2, ',', '.') ?></div>
                <div class="stat-change">+12%</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #FFF3E0;">🥠</div>
                <div class="stat-label">Reveladas</div>
                <div class="stat-value"><?= $sortes_usadas ?></div>
                <div class="stat-change">+5%</div>
            </div>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="text-align: center; flex: 1;">Vendas da Semana</h3>
                <a href="#" onclick="openSalesModal(); return false;" style="color: var(--primary); text-decoration: none; font-size: 14px; font-weight: 600;">Ver tudo →</a>
            </div>

            <div style="display: flex; gap: 8px; justify-content: space-between; align-items: flex-end; height: 120px; padding: 20px 0;">
                <?php 
                $dias = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
                foreach ($dias as $dia): 
                    $altura = rand(30, 100);
                ?>
                    <div style="flex: 1; text-align: center;">
                        <div style="background: <?= $dia === 'Qui' ? 'var(--primary)' : '#E5E5E5' ?>; height: <?= $altura ?>px; border-radius: 8px 8px 0 0; margin-bottom: 8px;"></div>
                        <div style="font-size: 12px; color: var(--text-light);"><?= $dia ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 20px; text-align: center;">Estoque de Frases</h3>
            
            <div style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 14px;">Disponíveis vs Usadas</span>
                    <span style="font-size: 14px; font-weight: 600; color: var(--primary);">
                        <?= round(($sortes_disponiveis / ($sortes_disponiveis + $sortes_usadas)) * 100) ?>% Restante
                    </span>
                </div>
                <div style="background: #E5E5E5; height: 12px; border-radius: 6px; overflow: hidden;">
                    <div style="background: linear-gradient(90deg, var(--primary) 0%, #FFB366 100%); height: 100%; width: <?= round(($sortes_disponiveis / ($sortes_disponiveis + $sortes_usadas)) * 100) ?>%;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: var(--text-light);">
                    <span><?= $sortes_disponiveis ?> Disponíveis</span>
                    <span><?= $sortes_usadas ?> Usadas</span>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 20px; text-align: center;">Favoritos do Público</h3>
            
            <?php foreach ($categorias_stats as $cat): ?>
                <div style="display: flex; align-items: center; margin-bottom: 16px;">
                    <div style="background: <?= htmlspecialchars($cat['cor']) ?>20; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-right: 16px;">
                        <?= htmlspecialchars($cat['icone']) ?>
                    </div>
                    <div style="flex: 1;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <strong><?= htmlspecialchars($cat['nome']) ?></strong>
                            <strong><?= $cat['total'] ?></strong>
                        </div>
                        <div style="font-size: 12px; color: var(--text-light);">
                            <?= $cat['percentual'] ?>% das escolhas
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin: 32px 0;">
            <a href="../pages/categorias.php" class="btn btn-secondary">
                Ver como usuário
            </a>
        </div>
    </div>

    <!-- Navegação Mobile -->
    <nav class="mobile-nav">
        <a href="../pages/categorias.php" class="mobile-nav-item">
            <span>🥠</span>
            Sortes
        </a>
        <a href="index.php" class="mobile-nav-item active">
            <span>📊</span>
            Admin
        </a>
        <a href="../pages/perfil.php" class="mobile-nav-item">
            <span>👤</span>
            Perfil
        </a>
    </nav>

    <!-- Modal de Vendas -->
    <div id="salesModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <button class="modal-close" onclick="closeSalesModal()">×</button>
            <h2 style="margin-bottom: 24px; text-align: center;">Vendas do Mês</h2>
            
            <div style="display: flex; gap: 12px; margin-bottom: 24px;">
                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                    <label>Data Início</label>
                    <input type="date" id="startDate" class="form-control" onchange="updateChart()">
                </div>
                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                    <label>Data Fim</label>
                    <input type="date" id="endDate" class="form-control" onchange="updateChart()">
                </div>
            </div>

            <div style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                <canvas id="salesChart" style="max-height: 300px;"></canvas>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div style="background: #E8F5E9; padding: 16px; border-radius: 12px; text-align: center;">
                    <div style="font-size: 12px; color: var(--text-light); margin-bottom: 4px;">Total de Vendas</div>
                    <div style="font-size: 24px; font-weight: 600; color: #2E7D32;" id="totalSales">0</div>
                </div>
                <div style="background: #FFF3E0; padding: 16px; border-radius: 12px; text-align: center;">
                    <div style="font-size: 12px; color: var(--text-light); margin-bottom: 4px;">Valor Total</div>
                    <div style="font-size: 24px; font-weight: 600; color: var(--primary);" id="totalValue">R$ 0,00</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let salesChart = null;

        function openSalesModal() {
            document.getElementById('salesModal').classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Definir datas padrão (último mês)
            const today = new Date();
            const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
            
            document.getElementById('endDate').valueAsDate = today;
            document.getElementById('startDate').valueAsDate = lastMonth;
            
            updateChart();
        }

        function closeSalesModal() {
            document.getElementById('salesModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        async function updateChart() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (!startDate || !endDate) return;

            try {
                const response = await fetch(`get_sales_data.php?start=${startDate}&end=${endDate}`);
                const data = await response.json();
                
                // Atualizar totais
                document.getElementById('totalSales').textContent = data.total_vendas;
                document.getElementById('totalValue').textContent = 'R$ ' + parseFloat(data.valor_total).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                
                // Atualizar gráfico
                const ctx = document.getElementById('salesChart').getContext('2d');
                
                if (salesChart) {
                    salesChart.destroy();
                }
                
                salesChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Vendas',
                            data: data.values,
                            backgroundColor: data.values.map((v, i) => 
                                i === data.values.length - 1 ? '#FF8C42' : '#E5E5E5'
                            ),
                            borderRadius: 8,
                            barThickness: 30
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Erro ao carregar dados:', error);
            }
        }
    </script>
</body>
</html>
