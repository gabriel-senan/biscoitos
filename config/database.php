<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/constants.php';

try {
    // Usar SQLite para desenvolvimento local
    $db_path = __DIR__ . '/../database.sqlite';
    $pdo = new PDO(
        "sqlite:" . $db_path,
        null,
        null,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Habilitar foreign keys no SQLite
    $pdo->exec('PRAGMA foreign_keys = ON;');
    
} catch (PDOException $e) {
    if (ENVIRONMENT === 'production') {
        error_log("Database connection error: " . $e->getMessage());
        die("Erro ao conectar ao banco de dados. Tente novamente mais tarde.");
    } else {
        die("Erro na conexão: " . $e->getMessage());
    }
}
