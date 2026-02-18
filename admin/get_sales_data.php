<?php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado e é admin
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit();
}

$start_date = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end'] ?? date('Y-m-d');

// Buscar vendas no período
$stmt = $pdo->prepare("
    SELECT DATE(data_pagamento) as dia, COUNT(*) as total, SUM(valor) as valor
    FROM pedidos
    WHERE status = 'pago' 
    AND DATE(data_pagamento) BETWEEN ? AND ?
    GROUP BY DATE(data_pagamento)
    ORDER BY dia
");
$stmt->execute([$start_date, $end_date]);
$vendas = $stmt->fetchAll();

// Preparar dados para o gráfico
$labels = [];
$values = [];
$total_vendas = 0;
$valor_total = 0;

foreach ($vendas as $venda) {
    $data = new DateTime($venda['dia']);
    $labels[] = $data->format('d/m');
    $values[] = (int)$venda['total'];
    $total_vendas += (int)$venda['total'];
    $valor_total += (float)$venda['valor'];
}

// Se não houver dados, criar array vazio
if (empty($labels)) {
    $labels = ['Sem dados'];
    $values = [0];
}

header('Content-Type: application/json');
echo json_encode([
    'labels' => $labels,
    'values' => $values,
    'total_vendas' => $total_vendas,
    'valor_total' => number_format($valor_total, 2, '.', '')
]);
