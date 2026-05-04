<?php
/**
 * Teste de compra de créditos
 */

require_once 'config/database.php';
require_once 'includes/creditos.php';

echo "=== TESTE DE COMPRA DE CRÉDITOS ===\n\n";

$usuario_id = 1000; // usuário teste@biscoitos.com

// 1. Verificar saldo inicial
echo "1. Saldo inicial...\n";
$saldo_inicial = obterSaldoCreditos($pdo, $usuario_id);
echo "   Saldo: {$saldo_inicial} créditos\n\n";

// 2. Simular compra de créditos (sem categoria)
echo "2. Simulando compra de 10 créditos...\n";
try {
    // Criar pedido de créditos (categoria_id = NULL)
    $stmt = $pdo->prepare("
        INSERT INTO pedidos (usuario_id, categoria_id, valor, status, metodo_pagamento, data_pagamento) 
        VALUES (?, NULL, ?, 'pago', 'pix', datetime('now'))
    ");
    $stmt->execute([$usuario_id, 10.00]);
    $pedido_id = $pdo->lastInsertId();
    echo "   ✅ Pedido criado: ID {$pedido_id}\n";
    
    // Adicionar créditos
    $resultado = adicionarCreditos($pdo, $usuario_id, 10, "Compra de 10 créditos", $pedido_id);
    if ($resultado) {
        echo "   ✅ Créditos adicionados com sucesso!\n";
    } else {
        echo "   ❌ Erro ao adicionar créditos\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erro: " . $e->getMessage() . "\n";
}

// 3. Verificar saldo final
echo "\n3. Saldo final...\n";
$saldo_final = obterSaldoCreditos($pdo, $usuario_id);
echo "   Saldo: {$saldo_final} créditos\n";
echo "   Diferença: +" . ($saldo_final - $saldo_inicial) . " créditos\n\n";

// 4. Verificar pedido criado
echo "4. Verificando pedido...\n";
$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
$stmt->execute([$pedido_id]);
$pedido = $stmt->fetch();
if ($pedido) {
    echo "   ✅ Pedido encontrado:\n";
    echo "      - ID: {$pedido['id']}\n";
    echo "      - Usuário: {$pedido['usuario_id']}\n";
    echo "      - Categoria: " . ($pedido['categoria_id'] ?? 'NULL (créditos)') . "\n";
    echo "      - Valor: R$ " . number_format($pedido['valor'], 2, ',', '.') . "\n";
    echo "      - Status: {$pedido['status']}\n";
}

echo "\n=== TESTE CONCLUÍDO ===\n";
