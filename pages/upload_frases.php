<?php
// Desabilitar exibição de erros para retornar JSON limpo
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado e é admin
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit();
}

// Verificar se arquivo foi enviado
if (!isset($_FILES['planilha']) || $_FILES['planilha']['error'] !== UPLOAD_ERR_OK) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao fazer upload do arquivo']);
    exit();
}

$file = $_FILES['planilha'];
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

try {
    $frases_inseridas = 0;
    $erros = [];
    
    // Buscar IDs das categorias
    $stmt = $pdo->query("SELECT id, nome FROM categorias");
    $categorias = [];
    while ($row = $stmt->fetch()) {
        $categorias[strtolower($row['nome'])] = $row['id'];
    }
    
    if ($extension === 'csv') {
        // Processar CSV
        $handle = fopen($file['tmp_name'], 'r');
        
        // Pular cabeçalho
        fgetcsv($handle);
        
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            if (count($data) < 2) continue;
            
            $categoria_nome = strtolower(trim($data[0]));
            $frase = trim($data[1]);
            
            if (empty($frase)) continue;
            
            if (!isset($categorias[$categoria_nome])) {
                $erros[] = "Categoria '{$data[0]}' não encontrada";
                continue;
            }
            
            $categoria_id = $categorias[$categoria_nome];
            
            // Inserir frase
            $stmt = $pdo->prepare("INSERT INTO sortes (categoria_id, mensagem, usada) VALUES (?, ?, 0)");
            $stmt->execute([$categoria_id, $frase]);
            $frases_inseridas++;
        }
        
        fclose($handle);
        
    } elseif (in_array($extension, ['xlsx', 'xls'])) {
        // Processar Excel usando SimpleXLSX
        require_once __DIR__ . '/../includes/SimpleXLSX.php';
        
        $xlsx = \Shuchkin\SimpleXLSX::parse($file['tmp_name']);
        
        if ($xlsx) {
            $rows = $xlsx->rows();
            
            // Debug: contar linhas
            $total_linhas = count($rows);
            
            // Pular cabeçalho (primeira linha)
            if ($total_linhas > 0) {
                array_shift($rows);
            }
            
            foreach ($rows as $index => $row) {
                // Debug: verificar estrutura da linha
                if (count($row) < 2) {
                    $erros[] = "Linha " . ($index + 2) . ": menos de 2 colunas";
                    continue;
                }
                
                // Pegar valores e limpar
                $categoria_nome = isset($row[0]) ? strtolower(trim($row[0])) : '';
                $frase = isset($row[1]) ? trim($row[1]) : '';
                
                // Debug: verificar valores vazios
                if (empty($categoria_nome)) {
                    $erros[] = "Linha " . ($index + 2) . ": categoria vazia";
                    continue;
                }
                
                if (empty($frase)) {
                    $erros[] = "Linha " . ($index + 2) . ": frase vazia";
                    continue;
                }
                
                // Verificar se categoria existe
                if (!isset($categorias[$categoria_nome])) {
                    $erros[] = "Linha " . ($index + 2) . ": Categoria '{$row[0]}' não encontrada (disponíveis: " . implode(', ', array_keys($categorias)) . ")";
                    continue;
                }
                
                $categoria_id = $categorias[$categoria_nome];
                
                // Inserir frase
                try {
                    $stmt = $pdo->prepare("INSERT INTO sortes (categoria_id, mensagem, usada) VALUES (?, ?, 0)");
                    $stmt->execute([$categoria_id, $frase]);
                    $frases_inseridas++;
                } catch (Exception $e) {
                    $erros[] = "Linha " . ($index + 2) . ": Erro ao inserir - " . $e->getMessage();
                }
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao ler arquivo Excel: ' . \Shuchkin\SimpleXLSX::parseError()]);
            exit();
        }
        
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Formato de arquivo não suportado. Use CSV ou XLSX']);
        exit();
    }
    
    header('Content-Type: application/json');
    if ($frases_inseridas > 0) {
        $mensagem = "{$frases_inseridas} frases inseridas com sucesso!";
        if (!empty($erros)) {
            $mensagem .= " (" . count($erros) . " erros)";
        }
        echo json_encode(['success' => true, 'message' => $mensagem, 'erros' => $erros]);
    } else {
        $mensagem = 'Nenhuma frase foi inserida';
        if (!empty($erros)) {
            $mensagem .= '. Erros encontrados: ' . implode('; ', array_slice($erros, 0, 5));
            if (count($erros) > 5) {
                $mensagem .= ' (e mais ' . (count($erros) - 5) . ' erros)';
            }
        }
        echo json_encode(['success' => false, 'message' => $mensagem, 'erros' => $erros]);
    }
    
} catch (Exception $e) {
    error_log("Erro ao processar planilha: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
