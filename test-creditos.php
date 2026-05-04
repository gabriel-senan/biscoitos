<?php
/**
 * Script de teste do sistema de créditos
 */

require_once 'config/database.php';
require_once 'includes/creditos.php';

echo "=== TESTE DO SISTEMA DE CRÉDITOS ===\n\n";

// Usuário de teste
$usuario_id = 1000; // usuário teste@biscoitos.com

// 1. Verificar saldo inicial
echo "1. Verificando saldo inicial...\n";
$saldo = obterSaldoCreditos($pdo, $usuario_id);
echo "   Saldo atual: {$saldo} créditos\n\n";

// 2. Adicionar créditos
echo "2. Adicionando 10 créditos...\n";
$resultado = adicionarCreditos($pdo, $usuario_id, 10, "Teste de compra", null);
if ($resultado) {
    echo "   ✅ Créditos adicionados com sucesso!\n";
    $saldo = obterSaldoCreditos($pdo, $usuario_id);
    echo "   Novo saldo: {$saldo} créditos\n\n";
} else {
    echo "   ❌ Erro ao adicionar créditos\n\n";
}

// 3. Consumir créditos
echo "3. Consumindo 1 crédito...\n";
$resultado = consumirCreditos($pdo, $usuario_id, 1, "Teste de abertura de biscoito");
if ($resultado) {
    echo "   ✅ Crédito consumido com sucesso!\n";
    $saldo = obterSaldoCreditos($pdo, $usuario_id);
    echo "   Novo saldo: {$saldo} créditos\n\n";
} else {
    echo "   ❌ Erro ao consumir crédito\n\n";
}

// 4. Tentar consumir mais créditos do que tem
echo "4. Tentando consumir mais créditos do que disponível...\n";
$resultado = consumirCreditos($pdo, $usuario_id, 1000, "Teste de limite");
if ($resultado) {
    echo "   ❌ ERRO: Permitiu consumir mais do que tinha!\n\n";
} else {
    echo "   ✅ Corretamente bloqueou consumo excessivo\n\n";
}

// 5. Verificar histórico
echo "5. Verificando histórico de transações...\n";
$historico = obterHistoricoCreditos($pdo, $usuario_id, 5);
echo "   Total de transações: " . count($historico) . "\n";
foreach ($historico as $transacao) {
    echo "   - {$transacao['tipo']}: {$transacao['quantidade']} créditos ({$transacao['descricao']})\n";
}
echo "\n";

// 6. Testar cálculos
echo "6. Testando cálculos...\n";
echo "   R$ 5,00 = " . calcularCreditos(5) . " créditos\n";
echo "   R$ 25,00 = " . calcularCreditos(25) . " créditos\n";
echo "   10 créditos = R$ " . number_format(calcularValorCreditos(10), 2, ',', '.') . "\n";
echo "   50 créditos = R$ " . number_format(calcularValorCreditos(50), 2, ',', '.') . "\n\n";

// 7. Verificar saldo final
echo "7. Saldo final...\n";
$saldo = obterSaldoCreditos($pdo, $usuario_id);
echo "   Saldo: {$saldo} créditos\n\n";

echo "=== TESTE CONCLUÍDO ===\n";
