<?php
// Constantes da aplicação

// Versão
define('APP_VERSION', '1.0.0');
define('APP_NAME', 'Biscoitos da Sorte');

// Preços
define('PRECO_SORTE', 1.00);

// Limites
define('MAX_SORTES_POR_DIA', 10);
define('MIN_SORTES_DISPONIVEIS', 50);

// Categorias (IDs fixos)
define('CATEGORIA_AMOR', 1);
define('CATEGORIA_TRABALHO', 2);
define('CATEGORIA_DINHEIRO', 3);
define('CATEGORIA_VIDA', 4);
define('CATEGORIA_AMIZADES', 5);

// Status de pedidos
define('STATUS_PENDENTE', 'pendente');
define('STATUS_PAGO', 'pago');
define('STATUS_CANCELADO', 'cancelado');

// Métodos de pagamento
define('METODO_PIX', 'pix');
define('METODO_CARTAO', 'cartao');
define('METODO_APPLE', 'apple');

// Configurações PIX (carregadas do .env)
define('PIX_CHAVE', getenv('PIX_KEY') ?: '+5511986411335');
define('PIX_TIPO_CHAVE', getenv('PIX_KEY_TYPE') ?: 'telefone');
define('PIX_BENEFICIARIO', getenv('PIX_MERCHANT_NAME') ?: 'Biscoitos da Sorte');
define('PIX_CIDADE', getenv('PIX_MERCHANT_CITY') ?: 'SAO PAULO');
