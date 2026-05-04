-- Migração para adicionar sistema de créditos

-- Tabela de créditos dos usuários
CREATE TABLE IF NOT EXISTS creditos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL UNIQUE,
    saldo INTEGER DEFAULT 0,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabela de transações de créditos
CREATE TABLE IF NOT EXISTS transacoes_creditos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    tipo TEXT NOT NULL, -- 'compra', 'uso', 'bonus'
    quantidade INTEGER NOT NULL,
    saldo_anterior INTEGER NOT NULL,
    saldo_novo INTEGER NOT NULL,
    descricao TEXT,
    pedido_id INTEGER,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
);

CREATE INDEX IF NOT EXISTS idx_transacoes_usuario ON transacoes_creditos(usuario_id);

-- Inicializar créditos para usuários existentes (5 créditos de bônus)
INSERT OR IGNORE INTO creditos (usuario_id, saldo)
SELECT id, 5 FROM usuarios;
