<?php
/**
 * Funções para gerenciamento de créditos
 */

/**
 * Obter saldo de créditos do usuário
 */
function obterSaldoCreditos($pdo, $usuario_id) {
    $stmt = $pdo->prepare("SELECT saldo FROM creditos WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $result = $stmt->fetch();
    
    if (!$result) {
        // Criar registro de créditos se não existir
        $stmt = $pdo->prepare("INSERT INTO creditos (usuario_id, saldo) VALUES (?, 0)");
        $stmt->execute([$usuario_id]);
        return 0;
    }
    
    return (int)$result['saldo'];
}

/**
 * Adicionar créditos ao usuário
 */
function adicionarCreditos($pdo, $usuario_id, $quantidade, $descricao = '', $pedido_id = null) {
    $pdo->beginTransaction();
    
    try {
        $saldo_anterior = obterSaldoCreditos($pdo, $usuario_id);
        $saldo_novo = $saldo_anterior + $quantidade;
        
        // Atualizar saldo
        $stmt = $pdo->prepare("
            INSERT INTO creditos (usuario_id, saldo, atualizado_em) 
            VALUES (?, ?, datetime('now'))
            ON CONFLICT(usuario_id) 
            DO UPDATE SET saldo = ?, atualizado_em = datetime('now')
        ");
        $stmt->execute([$usuario_id, $saldo_novo, $saldo_novo]);
        
        // Registrar transação
        $stmt = $pdo->prepare("
            INSERT INTO transacoes_creditos 
            (usuario_id, tipo, quantidade, saldo_anterior, saldo_novo, descricao, pedido_id) 
            VALUES (?, 'compra', ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$usuario_id, $quantidade, $saldo_anterior, $saldo_novo, $descricao, $pedido_id]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Erro ao adicionar créditos: " . $e->getMessage());
        return false;
    }
}

/**
 * Consumir créditos do usuário
 */
function consumirCreditos($pdo, $usuario_id, $quantidade, $descricao = '') {
    $pdo->beginTransaction();
    
    try {
        $saldo_anterior = obterSaldoCreditos($pdo, $usuario_id);
        
        if ($saldo_anterior < $quantidade) {
            $pdo->rollBack();
            return false;
        }
        
        $saldo_novo = $saldo_anterior - $quantidade;
        
        // Atualizar saldo
        $stmt = $pdo->prepare("
            UPDATE creditos 
            SET saldo = ?, atualizado_em = datetime('now') 
            WHERE usuario_id = ?
        ");
        $stmt->execute([$saldo_novo, $usuario_id]);
        
        // Registrar transação
        $stmt = $pdo->prepare("
            INSERT INTO transacoes_creditos 
            (usuario_id, tipo, quantidade, saldo_anterior, saldo_novo, descricao) 
            VALUES (?, 'uso', ?, ?, ?, ?)
        ");
        $stmt->execute([$usuario_id, $quantidade, $saldo_anterior, $saldo_novo, $descricao]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Erro ao consumir créditos: " . $e->getMessage());
        return false;
    }
}

/**
 * Obter histórico de transações do usuário
 */
function obterHistoricoCreditos($pdo, $usuario_id, $limite = 50) {
    $stmt = $pdo->prepare("
        SELECT * FROM transacoes_creditos 
        WHERE usuario_id = ? 
        ORDER BY criado_em DESC 
        LIMIT ?
    ");
    $stmt->execute([$usuario_id, $limite]);
    return $stmt->fetchAll();
}

/**
 * Calcular quantos créditos o usuário recebe por um valor
 */
function calcularCreditos($valor) {
    // R$ 5,00 = 5 créditos
    return floor($valor / 5) * 5;
}

/**
 * Calcular valor necessário para comprar X créditos
 */
function calcularValorCreditos($creditos) {
    // 5 créditos = R$ 5,00
    return ($creditos / 5) * 5;
}
