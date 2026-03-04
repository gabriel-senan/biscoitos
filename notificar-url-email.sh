#!/bin/bash

# Script para enviar URL por email quando muda
# Configure seu email abaixo

EMAIL_DESTINO="seu-email@gmail.com"  # ALTERE AQUI

echo "=========================================="
echo "📧 NOTIFICADOR DE URL POR EMAIL"
echo "=========================================="
echo ""

# Verificar se tem email configurado
if [ "$EMAIL_DESTINO" = "seu-email@gmail.com" ]; then
    echo "⚠️  Configure seu email no script primeiro!"
    echo ""
    echo "Edite o arquivo e altere a linha:"
    echo "  EMAIL_DESTINO=\"seu-email@gmail.com\""
    echo ""
    exit 1
fi

# Buscar URL atual
URL=$(ssh seschi@192.168.1.108 "echo 'mortadela1' | sudo -S journalctl -u cloudflared-quick -n 100 | grep -o 'https://.*\.trycloudflare\.com' | tail -1")

if [ -z "$URL" ]; then
    echo "❌ Não foi possível encontrar a URL"
    exit 1
fi

# Criar mensagem
ASSUNTO="🥠 Nova URL - Biscoitos da Sorte"
MENSAGEM="Olá!

A URL pública do Biscoitos da Sorte foi atualizada:

🌐 URL: $URL

📅 Data: $(date '+%d/%m/%Y às %H:%M:%S')

Acesse e compartilhe!

---
Este é um email automático do sistema Biscoitos da Sorte.
"

# Enviar email (usando mail command)
if command -v mail &> /dev/null; then
    echo "$MENSAGEM" | mail -s "$ASSUNTO" "$EMAIL_DESTINO"
    echo "✅ Email enviado para: $EMAIL_DESTINO"
else
    echo "⚠️  Comando 'mail' não instalado"
    echo ""
    echo "Para instalar:"
    echo "  sudo apt install mailutils"
    echo ""
    echo "Ou use o script de WhatsApp/Telegram"
fi

# Salvar em arquivo também
echo "$URL" > url-publica.txt
echo "$(date '+%Y-%m-%d %H:%M:%S') - $URL" >> historico-urls.txt

echo ""
echo "URL: $URL"
echo ""
