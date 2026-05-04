# 🚀 Guia de Deploy - Biscoitos da Sorte

## 📋 Requisitos

- VPS com Ubuntu 20.04+ ou Debian 11+
- Mínimo 1GB RAM, 1 vCPU, 10GB disco
- Acesso SSH root ou sudo
- Domínio apontado para o IP da VPS (opcional)

## 🎯 Deploy Rápido (3 passos)

### 1️⃣ Conectar na VPS e preparar

```bash
# Conectar via SSH
ssh root@seu-ip-vps

# Atualizar sistema
apt update && apt upgrade -y

# Instalar Git
apt install git -y
```

### 2️⃣ Clonar e configurar

```bash
# Clonar repositório (ou fazer upload via SCP)
git clone https://github.com/seu-usuario/biscoitos-sorte.git
cd biscoitos-sorte

# Copiar e editar variáveis de ambiente
cp .env.example .env
nano .env
```

**Configure no .env:**
```env
BASE_URL=http://seu-dominio.com  # ou http://seu-ip
PIX_KEY=seu-telefone-ou-chave-pix
ADMIN_EMAIL=seu@email.com
```

### 3️⃣ Deploy automático

```bash
# Dar permissão e executar
chmod +x deploy.sh
./deploy.sh
```

Pronto! Aplicação rodando em `http://seu-ip` ou `http://seu-dominio.com`

---

## 🔧 Deploy Manual (passo a passo)

### 1. Instalar Docker

```bash
# Download e instalação
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Adicionar usuário ao grupo docker
sudo usermod -aG docker $USER

# Recarregar grupos (ou fazer logout/login)
newgrp docker

# Verificar instalação
docker --version
docker compose version
```

### 2. Preparar aplicação

```bash
# Criar diretório
mkdir -p /var/www/biscoitos-sorte
cd /var/www/biscoitos-sorte

# Fazer upload dos arquivos via SCP
# No seu computador local:
scp -r * root@seu-ip:/var/www/biscoitos-sorte/

# Ou clonar do Git
git clone https://github.com/seu-usuario/biscoitos-sorte.git .
```

### 3. Configurar ambiente

```bash
# Copiar arquivo de exemplo
cp .env.example .env

# Editar configurações
nano .env
```

**Configurações importantes:**

```env
# Ambiente
ENVIRONMENT=production

# URL (OBRIGATÓRIO)
BASE_URL=http://seu-dominio.com

# PIX (OBRIGATÓRIO para pagamentos)
PIX_MERCHANT_NAME="Seu Nome ou Empresa"
PIX_MERCHANT_CITY="SUA CIDADE"
PIX_KEY_TYPE=telefone  # ou email, cpf, cnpj, random
PIX_KEY=5511999999999
PIX_AMOUNT=1.00

# Email
ADMIN_EMAIL=admin@seu-dominio.com

# SMTP (opcional)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=seu@gmail.com
SMTP_PASS=sua-senha-app
```

### 4. Build e iniciar

```bash
# Build da imagem
docker compose build

# Iniciar em background
docker compose up -d

# Ver logs
docker compose logs -f
```

### 5. Verificar

```bash
# Status
docker compose ps

# Testar
curl http://localhost/health-check.php

# Ver logs
docker compose logs app
```

---

## 🌐 Configurar Domínio com HTTPS

### Opção 1: Nginx Proxy Manager (Recomendado - Interface Gráfica)

```bash
# Criar rede
docker network create proxy

# Subir Nginx Proxy Manager
docker run -d \
  --name nginx-proxy-manager \
  --network proxy \
  -p 80:80 \
  -p 443:443 \
  -p 81:81 \
  -v npm-data:/data \
  -v npm-letsencrypt:/etc/letsencrypt \
  jc21/nginx-proxy-manager:latest

# Conectar aplicação à rede
docker network connect proxy biscoitos-sorte
```

Acesse `http://seu-ip:81`:
- Email: `admin@example.com`
- Senha: `changeme`

Configure:
1. Proxy Hosts → Add Proxy Host
2. Domain: `seu-dominio.com`
3. Forward to: `biscoitos-sorte:80`
4. SSL → Request SSL Certificate

### Opção 2: Certbot (Linha de comando)

```bash
# Instalar Certbot
apt install certbot python3-certbot-nginx -y

# Instalar Nginx local
apt install nginx -y

# Configurar site
cat > /etc/nginx/sites-available/biscoitos <<EOF
server {
    listen 80;
    server_name seu-dominio.com;

    location / {
        proxy_pass http://localhost:80;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
    }
}
EOF

# Ativar site
ln -s /etc/nginx/sites-available/biscoitos /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx

# Obter certificado SSL
certbot --nginx -d seu-dominio.com

# Renovação automática já está configurada
```

---

## 🛠️ Comandos Úteis

### Usando Makefile (mais fácil)

```bash
make help      # Ver todos os comandos
make up        # Iniciar
make down      # Parar
make restart   # Reiniciar
make logs      # Ver logs
make shell     # Acessar container
make backup    # Backup do banco
make restore   # Restaurar backup
make deploy    # Deploy completo
```

### Usando Docker Compose

```bash
# Iniciar
docker compose up -d

# Parar
docker compose down

# Reiniciar
docker compose restart

# Ver logs
docker compose logs -f app

# Acessar shell
docker compose exec app sh

# Ver status
docker compose ps
```

### Gerenciamento

```bash
# Backup manual
docker compose exec app cp /var/www/html/database.sqlite /var/www/html/uploads/backup.sqlite

# Restaurar backup
docker compose exec app cp /var/www/html/uploads/backup.sqlite /var/www/html/database.sqlite

# Limpar logs
docker compose exec app sh -c "echo '' > /var/www/html/logs/activity.log"
docker compose exec app sh -c "echo '' > /var/www/html/logs/error.log"

# Atualizar aplicação
git pull
docker compose build
docker compose up -d
```

---

## 🔒 Segurança

### 1. Configurar Firewall

```bash
# Instalar UFW
apt install ufw -y

# Configurar regras
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp    # SSH
ufw allow 80/tcp    # HTTP
ufw allow 443/tcp   # HTTPS

# Ativar
ufw enable

# Ver status
ufw status
```

### 2. Mudar porta SSH (opcional)

```bash
# Editar config
nano /etc/ssh/sshd_config

# Mudar linha:
Port 2222

# Reiniciar SSH
systemctl restart sshd

# Adicionar nova porta no firewall
ufw allow 2222/tcp
```

### 3. Fail2Ban (proteção contra brute force)

```bash
# Instalar
apt install fail2ban -y

# Configurar
cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
nano /etc/fail2ban/jail.local

# Ativar
systemctl enable fail2ban
systemctl start fail2ban
```

---

## 📊 Monitoramento

### Logs em tempo real

```bash
# Logs da aplicação
docker compose logs -f app

# Logs do Nginx
docker compose exec app tail -f /var/log/nginx/access.log
docker compose exec app tail -f /var/log/nginx/error.log

# Logs PHP
docker compose exec app tail -f /var/www/html/logs/error.log
```

### Uso de recursos

```bash
# CPU e memória
docker stats biscoitos-sorte

# Espaço em disco
df -h
du -sh /var/www/biscoitos-sorte
```

### Health check

```bash
# Verificar se está respondendo
curl http://localhost/health-check.php

# Monitorar continuamente
watch -n 5 'curl -s http://localhost/health-check.php'
```

---

## 🔄 Backup Automático

### Criar script de backup

```bash
# Criar script
cat > /usr/local/bin/backup-biscoitos.sh <<'EOF'
#!/bin/bash
BACKUP_DIR="/var/backups/biscoitos"
DATE=$(date +%Y%m%d-%H%M%S)

mkdir -p $BACKUP_DIR

# Backup do banco
docker compose -f /var/www/biscoitos-sorte/docker-compose.yml exec -T app \
  cat /var/www/html/database.sqlite > $BACKUP_DIR/db-$DATE.sqlite

# Backup dos uploads
tar -czf $BACKUP_DIR/uploads-$DATE.tar.gz \
  -C /var/www/biscoitos-sorte uploads/

# Manter apenas últimos 7 dias
find $BACKUP_DIR -name "*.sqlite" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "Backup concluído: $DATE"
EOF

# Dar permissão
chmod +x /usr/local/bin/backup-biscoitos.sh
```

### Agendar com Cron

```bash
# Editar crontab
crontab -e

# Adicionar linha (backup diário às 3h da manhã)
0 3 * * * /usr/local/bin/backup-biscoitos.sh >> /var/log/backup-biscoitos.log 2>&1
```

---

## 🐛 Troubleshooting

### Container não inicia

```bash
# Ver logs detalhados
docker compose logs app

# Verificar configuração
docker compose config

# Recriar container
docker compose down
docker compose up -d --force-recreate
```

### Erro de permissão

```bash
# Corrigir permissões
docker compose exec app chown -R www-data:www-data /var/www/html
docker compose exec app chmod -R 777 /var/www/html/uploads
docker compose exec app chmod -R 777 /var/www/html/logs
docker compose exec app chmod 666 /var/www/html/database.sqlite
```

### Porta 80 já em uso

```bash
# Ver o que está usando
sudo lsof -i :80

# Parar Apache/Nginx se existir
sudo systemctl stop apache2
sudo systemctl stop nginx

# Ou mudar porta no docker-compose.yml
ports:
  - "8080:80"  # Usar porta 8080
```

### Banco de dados corrompido

```bash
# Restaurar último backup
make restore

# Ou recriar do zero
docker compose down
rm database.sqlite
docker compose up -d
```

### Aplicação lenta

```bash
# Ver uso de recursos
docker stats biscoitos-sorte

# Aumentar recursos no docker-compose.yml
deploy:
  resources:
    limits:
      cpus: '1'
      memory: 512M
```

---

## 📈 Otimizações

### 1. Cache de assets

Já configurado no Nginx (30 dias para imagens/CSS/JS)

### 2. Compressão Gzip

Já ativada no Nginx para todos os tipos de arquivo

### 3. Limitar logs

```bash
# Adicionar ao docker-compose.yml
logging:
  driver: "json-file"
  options:
    max-size: "10m"
    max-file: "3"
```

### 4. Aumentar PHP memory

```bash
# Editar Dockerfile e rebuild
RUN sed -i 's/memory_limit = 256M/memory_limit = 512M/' "$PHP_INI_DIR/php.ini"
```

---

## 🆘 Suporte

- Documentação: `/docs`
- Logs: `docker compose logs -f`
- Issues: GitHub Issues

---

## ✅ Checklist de Deploy

- [ ] VPS configurada e atualizada
- [ ] Docker instalado
- [ ] Arquivos enviados para VPS
- [ ] Arquivo .env configurado
- [ ] BASE_URL definida corretamente
- [ ] PIX_KEY configurada
- [ ] Build executado com sucesso
- [ ] Aplicação iniciada
- [ ] Health check respondendo
- [ ] Domínio apontado (se aplicável)
- [ ] SSL configurado (se aplicável)
- [ ] Firewall configurado
- [ ] Backup automático agendado
- [ ] Monitoramento configurado

---

**Pronto! Sua aplicação está no ar! 🎉**
