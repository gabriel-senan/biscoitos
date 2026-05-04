#!/bin/bash

# Script para configurar domínio personalizado
# Uso: ./setup-domain.sh seu-dominio.com.br

set -e

if [ -z "$1" ]; then
    echo "❌ Erro: Especifique o domínio"
    echo "Uso: ./setup-domain.sh seu-dominio.com.br"
    exit 1
fi

DOMAIN="$1"
VPS_IP="167.234.247.255"
VPS_USER="ubuntu"
SSH_KEY="/home/senan/Downloads/vps/ssh-key-2026-04-26.key"

echo "🌐 Configurando domínio: $DOMAIN"
echo "========================================"
echo ""

# Verificar DNS
echo "🔍 Verificando DNS..."
DNS_IP=$(dig +short "$DOMAIN" | tail -1)

if [ -z "$DNS_IP" ]; then
    echo "⚠️  DNS não configurado ainda!"
    echo ""
    echo "Configure o DNS do seu domínio:"
    echo "  Tipo: A"
    echo "  Nome: @ (ou deixe em branco)"
    echo "  Valor: $VPS_IP"
    echo "  TTL: 3600"
    echo ""
    read -p "Pressione Enter quando o DNS estiver configurado..."
    DNS_IP=$(dig +short "$DOMAIN" | tail -1)
fi

if [ "$DNS_IP" != "$VPS_IP" ]; then
    echo "⚠️  DNS aponta para: $DNS_IP"
    echo "⚠️  Deveria apontar para: $VPS_IP"
    echo ""
    echo "Aguarde a propagação do DNS (pode levar até 24h)"
    echo "Ou continue mesmo assim? (s/N)"
    read -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Ss]$ ]]; then
        exit 1
    fi
fi

echo "✅ DNS configurado!"
echo ""

# Atualizar .env local
echo "📝 Atualizando .env local..."
sed -i "s|BASE_URL=.*|BASE_URL=https://$DOMAIN|" .env
echo "✅ .env atualizado!"

# Enviar .env atualizado
echo "📤 Enviando configuração para VPS..."
scp -i "$SSH_KEY" .env $VPS_USER@$VPS_IP:~/biscoitos-sorte/.env

# Configurar Nginx na VPS
echo "🔧 Configurando Nginx..."
ssh -i "$SSH_KEY" $VPS_USER@$VPS_IP << ENDSSH
# Criar configuração do Nginx
cat > /tmp/biscoitos.conf << 'EOF'
server {
    listen 80;
    server_name $DOMAIN www.$DOMAIN;

    access_log /var/log/nginx/biscoitos_access.log;
    error_log /var/log/nginx/biscoitos_error.log;

    client_max_body_size 10M;

    location / {
        proxy_pass http://localhost:8080;
        proxy_http_version 1.1;
        proxy_set_header Host \\\$host;
        proxy_set_header X-Real-IP \\\$remote_addr;
        proxy_set_header X-Forwarded-For \\\$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \\\$scheme;
        proxy_read_timeout 300;
        proxy_connect_timeout 300;
        proxy_send_timeout 300;
    }
}
EOF

# Instalar e mover configuração
sudo mv /tmp/biscoitos.conf /etc/nginx/sites-available/$DOMAIN
sudo ln -sf /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/

# Remover configuração antiga se existir
sudo rm -f /etc/nginx/sites-enabled/biscoitos.ghostprotocol.com.br

# Testar e recarregar Nginx
sudo nginx -t && sudo systemctl reload nginx

echo "✅ Nginx configurado!"
ENDSSH

# Configurar SSL
echo ""
echo "🔒 Configurando SSL (HTTPS)..."
ssh -i "$SSH_KEY" $VPS_USER@$VPS_IP << ENDSSH
# Instalar Certbot se não estiver instalado
if ! command -v certbot &> /dev/null; then
    echo "📦 Instalando Certbot..."
    sudo apt update
    sudo apt install -y certbot python3-certbot-nginx
fi

# Obter certificado SSL
echo "🔐 Obtendo certificado SSL..."
sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN --redirect

echo "✅ SSL configurado!"
ENDSSH

# Reiniciar aplicação
echo ""
echo "🔄 Reiniciando aplicação..."
ssh -i "$SSH_KEY" $VPS_USER@$VPS_IP "cd ~/biscoitos-sorte && docker compose restart"

echo ""
echo "✅ Configuração concluída!"
echo ""
echo "🌐 Seu site está disponível em:"
echo "   https://$DOMAIN"
echo "   https://www.$DOMAIN"
echo ""
echo "🔒 SSL/HTTPS ativado automaticamente!"
echo ""
echo "📊 Testar:"
echo "   curl https://$DOMAIN/health-check.php"
