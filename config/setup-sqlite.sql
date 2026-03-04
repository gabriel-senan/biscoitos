-- Criar tabelas para SQLite

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome_completo TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    senha TEXT NOT NULL,
    is_admin INTEGER DEFAULT 0,
    foto_perfil TEXT,
    oauth_provider TEXT,
    oauth_id TEXT,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_email ON usuarios(email);

-- Tabela de categorias
CREATE TABLE IF NOT EXISTS categorias (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    descricao TEXT,
    icone TEXT,
    cor TEXT,
    ativo INTEGER DEFAULT 1
);

-- Tabela de sortes
CREATE TABLE IF NOT EXISTS sortes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    categoria_id INTEGER NOT NULL,
    mensagem TEXT NOT NULL,
    usada INTEGER DEFAULT 0,
    criada_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

CREATE INDEX IF NOT EXISTS idx_categoria_usada ON sortes(categoria_id, usada);

-- Tabela de pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    categoria_id INTEGER NOT NULL,
    sorte_id INTEGER,
    valor REAL NOT NULL,
    status TEXT DEFAULT 'pendente',
    metodo_pagamento TEXT,
    data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_pagamento DATETIME,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    FOREIGN KEY (sorte_id) REFERENCES sortes(id)
);

CREATE INDEX IF NOT EXISTS idx_usuario ON pedidos(usuario_id);
CREATE INDEX IF NOT EXISTS idx_status ON pedidos(status);

-- Inserir categorias padrão
INSERT OR IGNORE INTO categorias (id, nome, descricao, icone, cor) VALUES
(1, 'Amor', 'Descubra o que o coração...', '❤️', '#FF6B9D'),
(2, 'Trabalho', 'Caminhos para o seu sucesso.', '💼', '#4A90E2'),
(3, 'Dinheiro', 'Prosperidade e novas chances.', '💰', '#FFD700'),
(4, 'Vida', 'Equilíbrio e bem-estar pessoal.', '🌱', '#7ED321'),
(5, 'Amizades', 'Conexões que importam.', '🤝', '#F5A623');

-- Inserir usuário admin padrão (senha: admin123)
INSERT OR IGNORE INTO usuarios (id, nome_completo, email, senha, is_admin) VALUES
(1, 'Administrador', 'admin@biscoitos.com', '$2y$10$ebHzVnI4HKDZacKGnpfzje3crZI6qCZMizrUitVo/UosBH5eTFJVq', 1);

-- Inserir usuário de teste (senha: 123456)
INSERT OR IGNORE INTO usuarios (id, nome_completo, email, senha, is_admin) VALUES
(2, 'Usuário Teste', 'teste@biscoitos.com', '$2y$10$ebHzVnI4HKDZacKGnpfzje3crZI6qCZMizrUitVo/UosBH5eTFJVq', 0);

-- Inserir sortes de exemplo
INSERT OR IGNORE INTO sortes (categoria_id, mensagem) VALUES
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
