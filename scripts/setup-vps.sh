#!/bin/bash

# Script de setup inicial para VPS
# Uso: curl -fsSL https://raw.githubusercontent.com/seu-repo/main/scripts/setup-vps.sh | bash

set -e

echo "🚀 Setup VPS - Biscoitos da Sorte"
echo "=================================="
echo ""

# Verificar se é root
if [ "$EUID" -ne 0 ]; then 
    echo "❌ Execute como root: sudo bash setup-vps.sh"
    exit 1
fi

# Atualizar sistema
echo "📦 Atualizando sistema..."
apt update && apt upgrade -y

# Instalar dependências básicas
echo "📦 Instalando dependências..."
apt install -y curl git wget nano ufw

# Instalar Docker
if ! command -v docker &> /dev/null; then
    echo "🐳 Instalando Docker..."
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    rm get-docker.sh
    
    # Instalar Docker Compose
    apt install -y docker-compose-plugin
    
    echo "✅ Docker instalado!"
else
    echo "✅ Docker já instalado"
fi

# Configurar firewall
echo "🔒 Configurando firewall..."
ufw --force enable
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw reload

echo "✅ Firewall configurado!"

# Criar usuário para aplicação (opcional)
if ! id "biscoitos" &>/dev/null; then
    echo "👤 Criando usuário biscoitos..."
    useradd -m -s /bin/bash biscoitos
    usermod -aG docker biscoitos
    echo "✅ Usuário criado!"
fi

# Criar diretório da aplicação
echo "📁 Criando diretório da aplicação..."
mkdir -p /var/www/biscoitos-sorte
chown biscoitos:biscoitos /var/www/biscoitos-sorte

echo ""
echo "✅ Setup concluído!"
echo ""
echo "Próximos passos:"
echo "1. Fazer upload/clone da aplicação em /var/www/biscoitos-sorte"
echo "2. Configurar .env"
echo "3. Executar ./deploy.sh"
echo ""
echo "Comandos úteis:"
echo "  su - biscoitos          # Trocar para usuário biscoitos"
echo "  cd /var/www/biscoitos-sorte"
echo "  git clone seu-repo.git ."
echo "  cp .env.example .env"
echo "  nano .env"
echo "  ./deploy.sh"
