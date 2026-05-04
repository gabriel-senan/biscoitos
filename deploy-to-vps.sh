#!/bin/bash

# Script de deploy automático para VPS
# Uso: ./deploy-to-vps.sh

set -e

VPS_IP="167.234.247.255"
VPS_USER="ubuntu"
SSH_KEY="/home/senan/Downloads/vps/ssh-key-2026-04-26.key"
REMOTE_DIR="~/biscoitos-sorte"

echo "🚀 Deploy para VPS - Biscoitos da Sorte"
echo "========================================"
echo "VPS: $VPS_USER@$VPS_IP"
echo "Diretório remoto: $REMOTE_DIR"
echo ""

# Verificar se chave SSH existe
if [ ! -f "$SSH_KEY" ]; then
    echo "❌ Chave SSH não encontrada: $SSH_KEY"
    exit 1
fi

# Verificar se .env existe
if [ ! -f .env ]; then
    echo "⚠️  Arquivo .env não encontrado!"
    echo "Criando a partir do .env.example..."
    cp .env.example .env
    echo ""
    echo "⚠️  IMPORTANTE: Edite o arquivo .env antes de continuar!"
    echo "Configure pelo menos:"
    echo "  - BASE_URL"
    echo "  - PIX_KEY"
    echo ""
    read -p "Deseja editar agora? (s/N) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Ss]$ ]]; then
        ${EDITOR:-nano} .env
    else
        echo "❌ Configure o .env e execute novamente"
        exit 1
    fi
fi

# Confirmar deploy
echo "📋 Arquivos que serão enviados:"
echo "  - Código da aplicação"
echo "  - Configurações Docker"
echo "  - Arquivo .env"
echo ""
read -p "Continuar com o deploy? (s/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Ss]$ ]]; then
    echo "❌ Deploy cancelado"
    exit 1
fi

# Enviar arquivos
echo ""
echo "📤 Enviando arquivos para VPS..."
rsync -avz --progress \
    -e "ssh -i $SSH_KEY" \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='database.sqlite' \
    --exclude='uploads/perfil/*' \
    --exclude='logs/*.log' \
    --exclude='.vscode' \
    --exclude='backups' \
    ./ $VPS_USER@$VPS_IP:$REMOTE_DIR/

echo "✅ Arquivos enviados!"

# Deploy na VPS
echo ""
echo "🐳 Executando deploy na VPS..."
ssh -i "$SSH_KEY" $VPS_USER@$VPS_IP << 'ENDSSH'
cd ~/biscoitos-sorte

echo "📦 Building imagem Docker..."
docker compose build

echo "🚀 Iniciando aplicação..."
docker compose up -d

echo "⏳ Aguardando inicialização..."
sleep 5

echo "📊 Status dos containers:"
docker compose ps

echo ""
echo "✅ Deploy concluído!"
echo ""
echo "🌐 Aplicação disponível em:"
echo "   http://167.234.247.255:8080"
echo ""
echo "📝 Ver logs:"
echo "   docker compose logs -f app"
echo ""
ENDSSH

echo ""
echo "✅ Deploy finalizado com sucesso!"
echo ""
echo "🔗 Acesse: http://$VPS_IP:8080"
echo ""
echo "📊 Para ver logs:"
echo "   ssh -i $SSH_KEY $VPS_USER@$VPS_IP 'cd $REMOTE_DIR && docker compose logs -f'"
