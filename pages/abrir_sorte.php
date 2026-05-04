<?php
require_once '../config/database.php';
require_once '../includes/creditos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || !isset($_POST['categoria_id'])) {
    header('Location: categorias.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$categoria_id = (int)$_POST['categoria_id'];

// Verificar saldo de créditos
$saldo = obterSaldoCreditos($pdo, $usuario_id);

if ($saldo < 1) {
    $_SESSION['mensagem_erro'] = "Você não tem créditos suficientes. Compre créditos para abrir um biscoito!";
    header('Location: comprar_creditos.php');
    exit();
}

// Buscar uma sorte disponível da categoria
$stmt = $pdo->prepare("SELECT id FROM sortes WHERE categoria_id = ? AND usada = 0 LIMIT 1");
$stmt->execute([$categoria_id]);
$sorte = $stmt->fetch();

if (!$sorte) {
    $_SESSION['mensagem_erro'] = "Desculpe, não há sortes disponíveis nesta categoria no momento.";
    header('Location: categorias.php');
    exit();
}

// Consumir 1 crédito
$stmt = $pdo->prepare("SELECT nome FROM categorias WHERE id = ?");
$stmt->execute([$categoria_id]);
$categoria = $stmt->fetch();
$descricao = "Abertura de biscoito - " . $categoria['nome'];

if (!consumirCreditos($pdo, $usuario_id, 1, $descricao)) {
    $_SESSION['mensagem_erro'] = "Erro ao processar créditos. Tente novamente.";
    header('Location: categorias.php');
    exit();
}

// Criar pedido (sem valor, pois foi pago com créditos)
$stmt = $pdo->prepare("
    INSERT INTO pedidos (usuario_id, categoria_id, sorte_id, valor, status, metodo_pagamento, data_pagamento) 
    VALUES (?, ?, ?, 0.00, 'pago', 'creditos', datetime('now'))
");
$stmt->execute([$usuario_id, $categoria_id, $sorte['id']]);

// Marcar sorte como usada
$stmt = $pdo->prepare("UPDATE sortes SET usada = 1 WHERE id = ?");
$stmt->execute([$sorte['id']]);

// Redirecionar para exibir a sorte
header('Location: sorte.php?id=' . $sorte['id']);
exit();
