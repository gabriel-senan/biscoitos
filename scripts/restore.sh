#!/bin/bash

# Script de restauração para Biscoitos da Sorte
# Uso: ./scripts/restore.sh [arquivo_backup]

set -e

APP_DIR=$(dirname "$(dirname "$(readlink -f "$0")")")

if [ -z "$1" ]; then
    echo "❌ Erro: Especifique o arquivo de backup"
    echo "Uso: ./scripts/restore.sh backups/db-20240101-120000.sqlite"
    echo ""
    echo "Backups disponíveis:"
    ls -lh backups/*.sqlite 2>/dev/null || echo "Nenhum backup encontrado"
    exit 1
fi

BACKUP_FILE="$1"

if [ ! -f "$BACKUP_FILE" ]; then
    echo "❌ Arquivo não encontrado: $BACKUP_FILE"
    exit 1
fi

echo "⚠️  ATENÇÃO: Esta operação irá substituir o banco de dados atual!"
echo "Backup: $BACKUP_FILE"
echo ""
read -p "Deseja continuar? (s/N) " -n 1 -r
echo

if [[ ! $REPLY =~ ^[Ss]$ ]]; then
    echo "❌ Operação cancelada"
    exit 1
fi

# Fazer backup do banco atual antes de restaurar
if [ -f "$APP_DIR/database.sqlite" ]; then
    echo "💾 Fazendo backup do banco atual..."
    cp "$APP_DIR/database.sqlite" "$APP_DIR/database.sqlite.before-restore"
    echo "✅ Backup salvo: database.sqlite.before-restore"
fi

# Restaurar backup
echo "📥 Restaurando backup..."
cp "$BACKUP_FILE" "$APP_DIR/database.sqlite"

# Ajustar permissões
chmod 666 "$APP_DIR/database.sqlite"

echo ""
echo "✅ Backup restaurado com sucesso!"
echo "📊 Arquivo: $BACKUP_FILE"
echo ""
echo "Para reverter, use:"
echo "  cp database.sqlite.before-restore database.sqlite"
