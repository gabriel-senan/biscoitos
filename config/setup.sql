CREATE DATABASE IF NOT EXISTS biscoitos_sorte CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE biscoitos_sorte;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_completo VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    icone VARCHAR(50),
    cor VARCHAR(20),
    ativo BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sortes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    mensagem TEXT NOT NULL,
    usada BOOLEAN DEFAULT FALSE,
    criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    INDEX idx_categoria_usada (categoria_id, usada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    categoria_id INT NOT NULL,
    sorte_id INT,
    valor DECIMAL(10,2) NOT NULL,
    status ENUM('pendente', 'pago', 'cancelado') DEFAULT 'pendente',
    metodo_pagamento VARCHAR(50),
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_pagamento TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    FOREIGN KEY (sorte_id) REFERENCES sortes(id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir categorias padrão
INSERT INTO categorias (nome, descricao, icone, cor) VALUES
('Amor', 'Descubra o que o coração...', '❤️', '#FF6B9D'),
('Trabalho', 'Caminhos para o seu sucesso.', '💼', '#4A90E2'),
('Dinheiro', 'Prosperidade e novas chances.', '💰', '#FFD700'),
('Vida', 'Equilíbrio e bem-estar pessoal.', '🌱', '#7ED321'),
('Amizades', 'Conexões que importam.', '🤝', '#F5A623');

-- Inserir algumas sortes de exemplo
INSERT INTO sortes (categoria_id, mensagem) VALUES
(1, 'O amor está mais perto do que você imagina. Abra os olhos para as pequenas gentilezas.'),
(1, 'A sorte favorece a mente que está preparada para enxergar as oportunidades.'),
(2, 'A persistência é o caminho do êxito. Continue plantando sementes.'),
(2, 'Seu talento será reconhecido em breve. Mantenha a confiança.'),
(3, 'Prosperidade vem para aqueles que sabem valorizar o que têm.'),
(3, 'Uma oportunidade financeira surgirá quando você menos esperar.'),
(4, 'Cuide do seu corpo, é o único lugar que você tem para viver.'),
(4, 'O equilíbrio está em aceitar o que não pode mudar e transformar o que pode.'),
(5, 'As melhores amizades são aquelas que nos fazem crescer.'),
(5, 'Um amigo verdadeiro aparecerá quando você mais precisar.');
