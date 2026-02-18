<?php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || !isset($_POST['categoria_id'])) {
    header('Location: categorias.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$categoria_id = (int)$_POST['categoria_id'];
$metodo_pagamento = $_POST['metodo_pagamento'] ?? 'pix';

// Buscar uma sorte disponível da categoria
$stmt = $pdo->prepare("SELECT id FROM sortes WHERE categoria_id = ? AND usada = 0 LIMIT 1");
$stmt->execute([$categoria_id]);
$sorte = $stmt->fetch();

if (!$sorte) {
    die("Desculpe, não há sortes disponíveis nesta categoria no momento.");
}

// Criar pedido
$stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, categoria_id, sorte_id, valor, status, metodo_pagamento, data_pagamento) VALUES (?, ?, ?, 1.00, 'pago', ?, datetime('now'))");
$stmt->execute([$usuario_id, $categoria_id, $sorte['id'], $metodo_pagamento]);

// Marcar sorte como usada
$stmt = $pdo->prepare("UPDATE sortes SET usada = 1 WHERE id = ?");
$stmt->execute([$sorte['id']]);

// Redirecionar para exibir a sorte
header('Location: sorte.php?id=' . $sorte['id']);
exit();
