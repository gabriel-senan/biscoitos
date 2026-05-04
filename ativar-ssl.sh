#!/bin/bash

# Script para ativar SSL no domínio biscoitosdasorte.com.br
# Execute este script DEPOIS que o DNS estiver apontado para 167.234.247.255

set -e

DOMAIN="biscoitosdasorte.com.br"
VPS_IP="167.234.247.255"
VPS_USER="ubuntu"
SSH_KEY="/home/senan/Downloads/vps/ssh-key-2026-04-26.key"

echo "🔒 Ativando SSL para $DOMAIN"
echo "========================================"
echo ""

# Verificar DNS
echo "🔍 Verificando DNS..."
DNS_IP=$(dig +short "$DOMAIN" | tail -1)

if [ -z "$DNS_IP" ]; then
    echo "❌ DNS não configurado!"
    echo ""
    echo "Configure o DNS primeiro:"
    echo "  Tipo: A"
    echo "  Nome: @ (ou deixe em branco)"
    echo "  Valor: $VPS_IP"
    echo "  TTL: 3600"
    echo ""
    echo "Também configure o www:"
    echo "  Tipo: CNAME"
    echo "  Nome: www"
    echo "  Valor: $DOMAIN"
    echo ""
    exit 1
fi

if [ "$DNS_IP" != "$VPS_IP" ]; then
    echo "⚠️  DNS aponta para: $DNS_IP"
    echo "⚠️  Deveria apontar para: $VPS_IP"
    echo ""
    echo "Aguarde a propagação do DNS (pode levar até 24h)"
    echo ""
    exit 1
fi

echo "✅ DNS configurado corretamente!"
echo ""

# Ativar SSL
echo "🔐 Obtendo certificado SSL..."
ssh -i "$SSH_KEY" $VPS_USER@$VPS_IP << 'ENDSSH'
sudo certbot --nginx \
  -d biscoitosdasorte.com.br \
  -d www.biscoitosdasorte.com.br \
  --non-interactive \
  --agree-tos \
  --email admin@biscoitosdasorte.com.br \
  --redirect
ENDSSH

echo ""
echo "✅ SSL ativado com sucesso!"
echo ""
echo "🌐 Seu site está disponível em:"
echo "   https://biscoitosdasorte.com.br"
echo "   https://www.biscoitosdasorte.com.br"
echo ""
echo "🔒 Certificado válido por 90 dias (renovação automática)"
echo ""
echo "📊 Testar:"
echo "   curl https://biscoitosdasorte.com.br/health-check.php"
