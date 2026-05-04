<?php
require_once '../config/database.php';
require_once '../includes/creditos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || !isset($_POST['creditos'])) {
    header('Location: comprar_creditos.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$creditos = (int)$_POST['creditos'];
$bonus = (int)$_POST['bonus'];
$valor = (float)$_POST['valor'];
$txid = $_POST['txid'] ?? '';
$total_creditos = $creditos + $bonus;

// Criar pedido de créditos
$stmt = $pdo->prepare("
    INSERT INTO pedidos (usuario_id, categoria_id, valor, status, metodo_pagamento, data_pagamento) 
    VALUES (?, NULL, ?, 'pago', 'pix', datetime('now'))
");
$stmt->execute([$usuario_id, $valor]);
$pedido_id = $pdo->lastInsertId();

// Adicionar créditos ao usuário
$descricao = "Compra de {$creditos} créditos";
if ($bonus > 0) {
    $descricao .= " + {$bonus} bônus";
}

$sucesso = adicionarCreditos($pdo, $usuario_id, $total_creditos, $descricao, $pedido_id);

if ($sucesso) {
    $_SESSION['mensagem_sucesso'] = "Créditos adicionados com sucesso!";
    header('Location: categorias.php');
} else {
    $_SESSION['mensagem_erro'] = "Erro ao adicionar créditos. Contate o suporte.";
    header('Location: comprar_creditos.php');
}
exit();
