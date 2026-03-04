#!/bin/bash

# Script para pegar URL pública atual do Cloudflare Tunnel

echo "=========================================="
echo "🌐 BISCOITOS DA SORTE - URL PÚBLICA"
echo "=========================================="
echo ""
echo "Buscando URL atual..."

# Buscar URL no servidor
URL=$(ssh seschi@192.168.1.108 "echo 'mortadela1' | sudo -S journalctl -u cloudflared-quick -n 100 | grep -o 'https://.*\.trycloudflare\.com' | tail -1")

if [ -z "$URL" ]; then
    echo ""
    echo "❌ Não foi possível encontrar a URL"
    echo ""
    echo "Possíveis causas:"
    echo "  1. Serviço cloudflared não está rodando"
    echo "  2. Servidor está desligado"
    echo "  3. Túnel ainda está iniciando"
    echo ""
    echo "Verificar status:"
    echo "  ssh seschi@192.168.1.108 'sudo systemctl status cloudflared-quick'"
    echo ""
    exit 1
fi

# Salvar URL em arquivo
echo "$URL" > url-publica.txt
echo "$(date '+%Y-%m-%d %H:%M:%S') - $URL" >> historico-urls.txt

echo ""
echo "=========================================="
echo "✅ URL ENCONTRADA!"
echo "=========================================="
echo ""
echo "   $URL"
echo ""
echo "=========================================="
echo ""
echo "📋 URL salva em: url-publica.txt"
echo "📝 Histórico em: historico-urls.txt"
echo ""
echo "💡 Dicas:"
echo "  • Copie esta URL e compartilhe"
echo "  • Acesse no navegador para testar"
echo "  • Execute este script sempre que precisar"
echo ""

# Copiar para clipboard se disponível
if command -v xclip &> /dev/null; then
    echo "$URL" | xclip -selection clipboard
    echo "✅ URL copiada para área de transferência!"
    echo ""
elif command -v pbcopy &> /dev/null; then
    echo "$URL" | pbcopy
    echo "✅ URL copiada para área de transferência!"
    echo ""
fi
