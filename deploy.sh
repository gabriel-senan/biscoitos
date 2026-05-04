#!/bin/bash

# Script de deploy automatizado para VPS
# Uso: ./deploy.sh [ambiente]

set -e

ENVIRONMENT=${1:-production}
APP_NAME="biscoitos-sorte"
BACKUP_DIR="backups"

echo "🚀 Deploy do $APP_NAME - Ambiente: $ENVIRONMENT"
echo "================================================"

# Verificar se Docker está instalado
if ! command -v docker &> /dev/null; then
    echo "❌ Docker não encontrado!"
    echo "Instalando Docker..."
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    sudo usermod -aG docker $USER
    echo "✅ Docker instalado! Faça logout e login novamente."
    exit 0
fi

# Verificar se .env existe
if [ ! -f .env ]; then
    echo "⚠️  Arquivo .env não encontrado!"
    if [ -f .env.example ]; then
        echo "Copiando .env.example para .env..."
        cp .env.example .env
        echo "⚠️  IMPORTANTE: Edite o arquivo .env com suas configurações!"
        echo "Execute: nano .env"
        exit 1
    else
        echo "❌ Arquivo .env.example não encontrado!"
        exit 1
    fi
fi

# Criar diretório de backup
mkdir -p $BACKUP_DIR

# Backup do banco de dados se existir
if [ -f database.sqlite ]; then
    BACKUP_FILE="$BACKUP_DIR/backup-$(date +%Y%m%d-%H%M%S).sqlite"
    echo "📦 Fazendo backup do banco de dados..."
    cp database.sqlite "$BACKUP_FILE"
    echo "✅ Backup salvo: $BACKUP_FILE"
fi

# Parar containers antigos
if docker compose ps | grep -q "$APP_NAME"; then
    echo "🛑 Parando containers antigos..."
    docker compose down
fi

# Build da nova imagem
echo "🔨 Building imagem Docker..."
docker compose build --no-cache

# Iniciar aplicação
echo "🚀 Iniciando aplicação..."
docker compose up -d

# Aguardar inicialização
echo "⏳ Aguardando inicialização..."
sleep 5

# Verificar status
if docker compose ps | grep -q "Up"; then
    echo ""
    echo "✅ Deploy concluído com sucesso!"
    echo "================================================"
    echo "🌐 Aplicação disponível em: $(grep BASE_URL .env | cut -d '=' -f2)"
    echo ""
    echo "Comandos úteis:"
    echo "  Ver logs:      docker compose logs -f"
    echo "  Parar:         docker compose down"
    echo "  Reiniciar:     docker compose restart"
    echo "  Status:        docker compose ps"
    echo ""
else
    echo "❌ Erro ao iniciar aplicação!"
    echo "Ver logs: docker compose logs"
    exit 1
fi

# Limpar backups antigos (manter últimos 7 dias)
echo "🧹 Limpando backups antigos..."
find $BACKUP_DIR -name "backup-*.sqlite" -mtime +7 -delete 2>/dev/null || true

echo "✅ Deploy finalizado!"
