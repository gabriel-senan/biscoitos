#!/bin/sh
set -e

echo "🚀 Iniciando Biscoitos da Sorte..."

# Verificar se o banco de dados existe
if [ ! -f /var/www/html/database.sqlite ]; then
    echo "📦 Criando banco de dados SQLite..."
    touch /var/www/html/database.sqlite
    chmod 666 /var/www/html/database.sqlite
    
    # Executar setup se existir
    if [ -f /var/www/html/config/setup-sqlite.sql ]; then
        echo "🔧 Executando setup do banco de dados..."
        sqlite3 /var/www/html/database.sqlite < /var/www/html/config/setup-sqlite.sql
    fi
fi

# Garantir permissões corretas
chown -R www-data:www-data /var/www/html/uploads /var/www/html/logs
chmod -R 777 /var/www/html/uploads /var/www/html/logs
chmod 666 /var/www/html/database.sqlite

echo "✅ Aplicação pronta!"
echo "🌐 Acesse: ${BASE_URL:-http://localhost}"

# Executar comando passado
exec "$@"
