#!/bin/bash

# Script para monitorar mudanças na URL pública

echo "=========================================="
echo "🔍 MONITOR DE URL PÚBLICA"
echo "=========================================="
echo ""
echo "Monitorando mudanças na URL..."
echo "Pressione Ctrl+C para parar"
echo ""

URL_ANTERIOR=""

while true; do
    # Buscar URL atual
    URL_ATUAL=$(ssh seschi@192.168.1.108 "echo 'mortadela1' | sudo -S journalctl -u cloudflared-quick -n 100 | grep -o 'https://.*\.trycloudflare\.com' | tail -1" 2>/dev/null)
    
    if [ -n "$URL_ATUAL" ]; then
        # Se URL mudou
        if [ "$URL_ATUAL" != "$URL_ANTERIOR" ]; then
            echo ""
            echo "=========================================="
            echo "🔔 URL MUDOU!"
            echo "=========================================="
            echo ""
            echo "Nova URL: $URL_ATUAL"
            echo "Data/Hora: $(date '+%Y-%m-%d %H:%M:%S')"
            echo ""
            
            # Salvar em arquivo
            echo "$URL_ATUAL" > url-publica.txt
            echo "$(date '+%Y-%m-%d %H:%M:%S') - $URL_ATUAL" >> historico-urls.txt
            
            # Notificação visual
            echo "✅ URL salva em url-publica.txt"
            echo ""
            
            URL_ANTERIOR="$URL_ATUAL"
        else
            # URL não mudou, apenas mostrar status
            echo -ne "\r⏳ Monitorando... Última verificação: $(date '+%H:%M:%S')"
        fi
    else
        echo -ne "\r❌ Serviço offline... Tentando novamente: $(date '+%H:%M:%S')"
    fi
    
    # Aguardar 30 segundos antes de verificar novamente
    sleep 30
done
