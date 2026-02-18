#!/bin/bash

echo "🥠 Biscoitos da Sorte - Iniciando servidor local..."
echo ""

# Verificar se banco existe
if [ ! -f database.sqlite ]; then
    echo "❌ Banco de dados não encontrado!"
    echo ""
    echo "Execute primeiro:"
    echo "./setup-local.sh"
    exit 1
fi

# Verificar porta
PORT=8000
if lsof -Pi :$PORT -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo "⚠️  Porta $PORT já está em uso"
    PORT=8001
    echo "Usando porta alternativa: $PORT"
fi

echo "🚀 Servidor iniciado!"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📱 Acesse no navegador:"
echo "   http://localhost:$PORT"
echo ""
echo "🔍 Páginas disponíveis:"
echo "   Landing Page:  http://localhost:$PORT"
echo "   Cadastro:      http://localhost:$PORT/pages/auth.php"
echo "   Dashboard:     http://localhost:$PORT/admin/"
echo "   Health Check:  http://localhost:$PORT/health-check.php"
echo ""
echo "📊 Logs:"
echo "   tail -f logs/error.log"
echo "   tail -f logs/activity.log"
echo ""
echo "⏹️  Para parar: Ctrl + C"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Iniciar servidor
php -S localhost:$PORT
