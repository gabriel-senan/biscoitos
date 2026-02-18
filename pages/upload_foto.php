<?php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $file = $_FILES['foto'];
    
    // Validar arquivo
    $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    if (!in_array($file['type'], $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Formato não permitido']);
        exit();
    }
    
    // Validar tamanho (max 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Arquivo muito grande (max 2MB)']);
        exit();
    }
    
    // Criar pasta uploads se não existir
    $upload_dir = __DIR__ . '/../uploads/perfil/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Gerar nome único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'user_' . $usuario_id . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Mover arquivo
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Atualizar banco
        $foto_url = 'uploads/perfil/' . $filename;
        $stmt = $pdo->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
        $stmt->execute([$foto_url, $usuario_id]);
        
        echo json_encode(['success' => true, 'foto_url' => '../' . $foto_url]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao fazer upload']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado']);
}
