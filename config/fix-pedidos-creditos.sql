-- Permitir categoria_id NULL para pedidos de créditos

-- SQLite não suporta ALTER COLUMN diretamente, então precisamos recriar a tabela

-- 1. Criar tabela temporária com a nova estrutura
CREATE TABLE pedidos_new (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    categoria_id INTEGER, -- Agora permite NULL
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

-- 2. Copiar dados existentes
INSERT INTO pedidos_new SELECT * FROM pedidos;

-- 3. Remover tabela antiga
DROP TABLE pedidos;

-- 4. Renomear nova tabela
ALTER TABLE pedidos_new RENAME TO pedidos;

-- 5. Recriar índices
CREATE INDEX IF NOT EXISTS idx_usuario ON pedidos(usuario_id);
CREATE INDEX IF NOT EXISTS idx_status ON pedidos(status);
