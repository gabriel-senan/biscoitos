#!/bin/bash

# Script de backup para Biscoitos da Sorte
# Uso: ./scripts/backup.sh [destino]

set -e

BACKUP_DIR="${1:-backups}"
DATE=$(date +%Y%m%d-%H%M%S)
APP_DIR=$(dirname "$(dirname "$(readlink -f "$0")")")

echo "📦 Iniciando backup..."
echo "Diretório: $BACKUP_DIR"
echo "Data: $DATE"

# Criar diretório de backup
mkdir -p "$BACKUP_DIR"

# Backup do banco de dados
if [ -f "$APP_DIR/database.sqlite" ]; then
    echo "💾 Backup do banco de dados..."
    cp "$APP_DIR/database.sqlite" "$BACKUP_DIR/db-$DATE.sqlite"
    echo "✅ Banco: $BACKUP_DIR/db-$DATE.sqlite"
fi

# Backup dos uploads
if [ -d "$APP_DIR/uploads" ]; then
    echo "📁 Backup dos uploads..."
    tar -czf "$BACKUP_DIR/uploads-$DATE.tar.gz" -C "$APP_DIR" uploads/
    echo "✅ Uploads: $BACKUP_DIR/uploads-$DATE.tar.gz"
fi

# Backup do .env
if [ -f "$APP_DIR/.env" ]; then
    echo "⚙️  Backup do .env..."
    cp "$APP_DIR/.env" "$BACKUP_DIR/env-$DATE.txt"
    echo "✅ Config: $BACKUP_DIR/env-$DATE.txt"
fi

# Limpar backups antigos (manter últimos 7 dias)
echo "🧹 Limpando backups antigos..."
find "$BACKUP_DIR" -name "db-*.sqlite" -mtime +7 -delete 2>/dev/null || true
find "$BACKUP_DIR" -name "uploads-*.tar.gz" -mtime +7 -delete 2>/dev/null || true
find "$BACKUP_DIR" -name "env-*.txt" -mtime +7 -delete 2>/dev/null || true

echo ""
echo "✅ Backup concluído!"
echo "📊 Arquivos criados:"
ls -lh "$BACKUP_DIR" | grep "$DATE"
