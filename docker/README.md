# 🐳 Docker - Biscoitos da Sorte

## 📋 Pré-requisitos

- Docker 20.10+
- Docker Compose 2.0+

## 🚀 Deploy na VPS

### 1. Preparar o servidor

```bash
# Atualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Instalar Docker Compose
sudo apt install docker-compose-plugin -y

# Adicionar usuário ao grupo docker (opcional)
sudo usermod -aG docker $USER
```

### 2. Clonar/Enviar aplicação

```bash
# Opção 1: Via Git
git clone seu-repositorio.git
cd biscoitos-sorte

# Opção 2: Via SCP/SFTP
# Envie os arquivos para /home/usuario/biscoitos-sorte
```

### 3. Configurar variáveis de ambiente

```bash
# Copiar arquivo de exemplo
cp .env.example .env

# Editar com suas configurações
nano .env
```

**IMPORTANTE:** Configure pelo menos:
- `BASE_URL` - URL do seu domínio ou IP
- `PIX_KEY` - Sua chave PIX
- `ADMIN_EMAIL` - Seu email

### 4. Build e iniciar

```bash
# Build da imagem
docker compose build

# Iniciar em background
docker compose up -d

# Ver logs
docker compose logs -f
```

### 5. Verificar status

```bash
# Status dos containers
docker compose ps

# Health check
curl http://localhost/health-check.php
```

## 🔧 Comandos úteis

```bash
# Parar aplicação
docker compose down

# Reiniciar
docker compose restart

# Ver logs em tempo real
docker compose logs -f app

# Acessar shell do container
docker compose exec app sh

# Backup do banco de dados
docker compose exec app cp /var/www/html/database.sqlite /var/www/html/uploads/backup-$(date +%Y%m%d).sqlite

# Restaurar backup
docker compose exec app cp /var/www/html/uploads/backup-YYYYMMDD.sqlite /var/www/html/database.sqlite
```

## 🌐 Configurar domínio (com Nginx Proxy)

Se você quiser usar um domínio com HTTPS, recomendo usar nginx-proxy + letsencrypt:

```bash
# Criar rede compartilhada
docker network create nginx-proxy

# Subir nginx-proxy
docker run -d -p 80:80 -p 443:443 \
  --name nginx-proxy \
  --net nginx-proxy \
  -v /var/run/docker.sock:/tmp/docker.sock:ro \
  -v nginx-certs:/etc/nginx/certs \
  -v nginx-vhost:/etc/nginx/vhost.d \
  -v nginx-html:/usr/share/nginx/html \
  nginxproxy/nginx-proxy

# Subir letsencrypt companion
docker run -d \
  --name nginx-proxy-letsencrypt \
  --net nginx-proxy \
  --volumes-from nginx-proxy \
  -v /var/run/docker.sock:/var/run/docker.sock:ro \
  -v nginx-acme:/etc/acme.sh \
  -e DEFAULT_EMAIL=seu@email.com \
  nginxproxy/acme-companion
```

Depois, adicione ao `docker-compose.yml`:

```yaml
services:
  app:
    environment:
      - VIRTUAL_HOST=seu-dominio.com
      - LETSENCRYPT_HOST=seu-dominio.com
      - LETSENCRYPT_EMAIL=seu@email.com
    networks:
      - biscoitos-network
      - nginx-proxy

networks:
  nginx-proxy:
    external: true
```

## 📊 Monitoramento

```bash
# Uso de recursos
docker stats biscoitos-sorte

# Logs de erro do Nginx
docker compose exec app tail -f /var/log/nginx/error.log

# Logs da aplicação
docker compose exec app tail -f /var/www/html/logs/error.log
```

## 🔒 Segurança

1. **Firewall**: Configure UFW para permitir apenas portas necessárias
```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

2. **Backup automático**: Configure cron para backup diário
```bash
# Editar crontab
crontab -e

# Adicionar linha (backup diário às 3h)
0 3 * * * cd /home/usuario/biscoitos-sorte && docker compose exec -T app cp /var/www/html/database.sqlite /var/www/html/uploads/backup-$(date +\%Y\%m\%d).sqlite
```

3. **Atualizações**: Mantenha Docker e sistema atualizados
```bash
sudo apt update && sudo apt upgrade -y
docker compose pull
docker compose up -d
```

## 🐛 Troubleshooting

### Container não inicia
```bash
docker compose logs app
```

### Permissões de arquivo
```bash
docker compose exec app chown -R www-data:www-data /var/www/html
docker compose exec app chmod -R 777 /var/www/html/uploads /var/www/html/logs
```

### Resetar banco de dados
```bash
docker compose down
rm database.sqlite
docker compose up -d
```

### Porta 80 já em uso
```bash
# Ver o que está usando a porta
sudo lsof -i :80

# Parar Apache/Nginx local se existir
sudo systemctl stop apache2
sudo systemctl stop nginx
```
