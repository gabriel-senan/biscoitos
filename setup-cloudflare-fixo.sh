#!/bin/bash

echo "=========================================="
echo "CLOUDFLARE TUNNEL - URL FIXA"
echo "=========================================="
echo ""

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}📋 Pré-requisitos:${NC}"
echo "1. Conta Cloudflare (gratuita): https://dash.cloudflare.com/sign-up"
echo "2. Um domínio (pode ser gratuito do DuckDNS ou Freenom)"
echo ""
echo -e "${YELLOW}Opções de domínio gratuito:${NC}"
echo "- DuckDNS: https://www.duckdns.org (ex: biscoitos.duckdns.org)"
echo "- Freenom: https://www.freenom.com (ex: biscoitos.tk, .ml, .ga)"
echo "- Cloudflare Pages: subdomínio gratuito (ex: biscoitos.pages.dev)"
echo ""
read -p "Você já tem uma conta Cloudflare? (s/n): " tem_conta

if [ "$tem_conta" != "s" ]; then
    echo ""
    echo -e "${YELLOW}Crie sua conta agora:${NC}"
    echo "1. Acesse: https://dash.cloudflare.com/sign-up"
    echo "2. Crie conta gratuita"
    echo "3. Volte aqui e execute o script novamente"
    exit 0
fi

echo ""
read -p "Você já tem um domínio adicionado no Cloudflare? (s/n): " tem_dominio

if [ "$tem_dominio" != "s" ]; then
    echo ""
    echo -e "${YELLOW}Opções:${NC}"
    echo ""
    echo "A) Usar domínio gratuito DuckDNS:"
    echo "   1. Acesse: https://www.duckdns.org"
    echo "   2. Faça login com Google"
    echo "   3. Crie subdomínio (ex: biscoitos)"
    echo "   4. Adicione no Cloudflare como domínio"
    echo ""
    echo "B) Comprar domínio barato:"
    echo "   - Registro.br: .com.br por R$40/ano"
    echo "   - Hostinger: .com por R$20/ano"
    echo ""
    read -p "Pressione ENTER quando tiver o domínio pronto..."
fi

# Instalar cloudflared
echo ""
echo -e "${GREEN}[1/5] Instalando cloudflared...${NC}"
cd /tmp
ARCH=$(uname -m)
if [ "$ARCH" = "armv7l" ] || [ "$ARCH" = "armhf" ]; then
    CLOUDFLARED_URL="https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-arm"
elif [ "$ARCH" = "aarch64" ] || [ "$ARCH" = "arm64" ]; then
    CLOUDFLARED_URL="https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-arm64"
else
    CLOUDFLARED_URL="https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64"
fi

wget -q --show-progress "$CLOUDFLARED_URL" -O cloudflared
chmod +x cloudflared
echo "mortadela1" | sudo -S mv cloudflared /usr/local/bin/
echo -e "${GREEN}✓ cloudflared instalado${NC}"

# Autenticar
echo ""
echo -e "${GREEN}[2/5] Autenticação no Cloudflare${NC}"
echo -e "${YELLOW}Uma janela do navegador vai abrir. Faça login e autorize.${NC}"
read -p "Pressione ENTER para continuar..."
cloudflared tunnel login

# Criar túnel
echo ""
echo -e "${GREEN}[3/5] Criando túnel...${NC}"
read -p "Nome do túnel (ex: biscoitos): " TUNNEL_NAME
cloudflared tunnel create "$TUNNEL_NAME"

TUNNEL_ID=$(cloudflared tunnel list | grep "$TUNNEL_NAME" | awk '{print $1}')
echo -e "${GREEN}✓ Túnel criado: $TUNNEL_ID${NC}"

# Configurar domínio
echo ""
echo -e "${GREEN}[4/5] Configurando domínio...${NC}"
echo "Digite o domínio completo (ex: biscoitos.duckdns.org ou app.seudominio.com):"
read DOMAIN

# Criar configuração
echo "mortadela1" | sudo -S mkdir -p /etc/cloudflared
CRED_FILE=$(ls ~/.cloudflared/*.json 2>/dev/null | grep "$TUNNEL_ID")

echo "mortadela1" | sudo -S tee /etc/cloudflared/config.yml > /dev/null <<EOF
tunnel: $TUNNEL_ID
credentials-file: $CRED_FILE

ingress:
  - hostname: $DOMAIN
    service: http://localhost:80
  - service: http_status:404
EOF

# Configurar DNS
echo ""
echo -e "${GREEN}[5/5] Configurando DNS...${NC}"
cloudflared tunnel route dns "$TUNNEL_NAME" "$DOMAIN"

# Parar serviço antigo
echo "mortadela1" | sudo -S systemctl stop cloudflared-quick 2>/dev/null
echo "mortadela1" | sudo -S systemctl disable cloudflared-quick 2>/dev/null

# Instalar serviço
echo "mortadela1" | sudo -S cloudflared service install
echo "mortadela1" | sudo -S systemctl start cloudflared
echo "mortadela1" | sudo -S systemctl enable cloudflared

# Atualizar .env
echo "mortadela1" | sudo -S sed -i "s|BASE_URL=.*|BASE_URL=https://$DOMAIN|g" /var/www/biscoitos/.env
echo "mortadela1" | sudo -S systemctl restart php8.4-fpm

echo ""
echo "=========================================="
echo -e "${GREEN}✅ CONFIGURAÇÃO CONCLUÍDA!${NC}"
echo "=========================================="
echo ""
echo -e "${GREEN}🌐 Sua URL FIXA:${NC}"
echo -e "${YELLOW}   https://$DOMAIN${NC}"
echo ""
echo "Esta URL NÃO muda mais! 🎉"
echo ""
echo "📊 Comandos úteis:"
echo "  Status:    sudo systemctl status cloudflared"
echo "  Reiniciar: sudo systemctl restart cloudflared"
echo "  Logs:      sudo journalctl -u cloudflared -f"
echo ""
