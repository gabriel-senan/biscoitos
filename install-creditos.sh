#!/bin/bash

echo "==================================="
echo "Instalação do Sistema de Créditos"
echo "==================================="
echo ""

# Verificar se o banco existe
if [ ! -f "database.sqlite" ]; then
    echo "❌ Erro: database.sqlite não encontrado!"
    echo "Execute primeiro: sqlite3 database.sqlite < config/setup-sqlite.sql"
    exit 1
fi

echo "1. Criando tabelas de créditos..."
sqlite3 database.sqlite < config/migrate-creditos.sql
if [ $? -eq 0 ]; then
    echo "   ✅ Tabelas de créditos criadas"
else
    echo "   ❌ Erro ao criar tabelas"
    exit 1
fi

echo ""
echo "2. Ajustando tabela de pedidos..."
sqlite3 database.sqlite < config/fix-pedidos-creditos.sql
if [ $? -eq 0 ]; then
    echo "   ✅ Tabela de pedidos ajustada"
else
    echo "   ❌ Erro ao ajustar tabela"
    exit 1
fi

echo ""
echo "3. Executando testes..."
php test-creditos.php
if [ $? -eq 0 ]; then
    echo "   ✅ Testes básicos passaram"
else
    echo "   ❌ Testes falharam"
    exit 1
fi

echo ""
php test-compra-creditos.php
if [ $? -eq 0 ]; then
    echo "   ✅ Teste de compra passou"
else
    echo "   ❌ Teste de compra falhou"
    exit 1
fi

echo ""
echo "==================================="
echo "✅ Instalação concluída com sucesso!"
echo "==================================="
echo ""
echo "Próximos passos:"
echo "1. Inicie o servidor: php -S localhost:8000"
echo "2. Acesse: http://localhost:8000"
echo "3. Login: teste@biscoitos.com / 123456"
echo "4. Teste a compra de créditos"
echo ""
