<?php
require_once '../config/database.php';
require_once '../includes/security.php';
require_once '../includes/security_headers.php';

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
    
    // Validar tipos permitidos
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
    
    // Validar MIME type real do arquivo (não confiar no enviado pelo cliente)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($mime, $allowed_types) || !in_array($extension, $allowed_extensions)) {
        log_activity("Tentativa de upload de arquivo não permitido: $mime", "warning");
        echo json_encode(['success' => false, 'message' => 'Formato não permitido. Use JPG, PNG ou WEBP']);
        exit();
    }
    
    // Validar se é realmente uma imagem válida
    $image_info = getimagesize($file['tmp_name']);
    if ($image_info === false) {
        log_activity("Tentativa de upload de arquivo inválido", "warning");
        echo json_encode(['success' => false, 'message' => 'Arquivo não é uma imagem válida']);
        exit();
    }
    
    // Validar tamanho (max 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Arquivo muito grande (máximo 2MB)']);
        exit();
    }
    
    // Validar dimensões (max 2000x2000px)
    if ($image_info[0] > 2000 || $image_info[1] > 2000) {
        echo json_encode(['success' => false, 'message' => 'Imagem muito grande (máximo 2000x2000px)']);
        exit();
    }
    
    // Criar pasta uploads com permissões seguras
    $upload_dir = __DIR__ . '/../uploads/perfil/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Gerar nome único e seguro
    $filename = 'user_' . $usuario_id . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Mover arquivo
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Definir permissões corretas (leitura para todos, escrita apenas para owner)
        chmod($filepath, 0644);
        
        // Remover foto antiga se existir
        $stmt = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $old_foto = $stmt->fetchColumn();
        
        if ($old_foto && file_exists(__DIR__ . '/../' . $old_foto)) {
            unlink(__DIR__ . '/../' . $old_foto);
        }
        
        // Atualizar banco
        $foto_url = 'uploads/perfil/' . $filename;
        $stmt = $pdo->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
        $stmt->execute([$foto_url, $usuario_id]);
        
        log_activity("Foto de perfil atualizada");
        
        echo json_encode(['success' => true, 'foto_url' => '../' . $foto_url]);
    } else {
        log_activity("Erro ao mover arquivo de upload", "error");
        echo json_encode(['success' => false, 'message' => 'Erro ao fazer upload']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado']);
}
