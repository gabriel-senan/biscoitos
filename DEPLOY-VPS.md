# 🚀 Deploy na sua VPS (167.234.247.255)

## 📋 Situação Atual

Sua VPS já tem:
- ✅ Ubuntu 22.04.5 LTS
- ✅ Docker 29.4.1
- ✅ Docker Compose 5.1.3
- ✅ Nginx rodando (proxy para ghostprotocol.com.br)
- ✅ 12GB RAM, 39GB disco livre
- ⚠️ Portas 80/443 ocupadas pelo Nginx

## 🎯 Estratégia de Deploy

Vamos rodar o Biscoitos da Sorte na **porta 8080** e configurar o Nginx para fazer proxy reverso.

---

## 🚀 Opção 1: Deploy Rápido (Acesso via IP:8080)

### 1. Enviar arquivos para VPS

```bash
# No seu computador local
cd ~/biscoitos
rsync -avz -e "ssh -i /home/senan/Downloads/vps/ssh-key-2026-04-26.key" \
  --exclude='.git' \
  --exclude='node_modules' \
  --exclude='database.sqlite' \
  ./ ubuntu@167.234.247.255:~/biscoitos-sorte/
```

### 2. Conectar na VPS e configurar

```bash
# Conectar
ssh -i /home/senan/Downloads/vps/ssh-key-2026-04-26.key ubuntu@167.234.247.255

# Ir para o diretório
cd ~/biscoitos-sorte

# Configurar ambiente
cp .env.example .env
nano .env
```

**Configure no .env:**
```env
ENVIRONMENT=production
BASE_URL=http://167.234.247.255:8080
PIX_KEY=5511986411335  # Sua chave PIX
PIX_MERCHANT_NAME="Biscoitos da Sorte"
ADMIN_EMAIL=seu@email.com
```

### 3. Deploy

```bash
# Build e iniciar
docker compose build
docker compose up -d

# Ver logs
docker compose logs -f
```

### 4. Testar

Acesse: **http://167.234.247.255:8080**

---

## 🌐 Opção 2: Deploy com Domínio (Recomendado)

### 1. Configurar DNS

Aponte um subdomínio para sua VPS:
```
biscoitos.seudominio.com.br  →  167.234.247.255
```

### 2. Enviar arquivos (mesmo da Opção 1)

```bash
rsync -avz -e "ssh -i /home/senan/Downloads/vps/ssh-key-2026-04-26.key" \
  --exclude='.git' \
  ./ ubuntu@167.234.247.255:~/biscoitos-sorte/
```

### 3. Configurar na VPS

```bash
ssh -i /home/senan/Downloads/vps/ssh-key-2026-04-26.key ubuntu@167.234.247.255

cd ~/biscoitos-sorte

# Configurar .env
cp .env.example .env
nano .env
```

**Configure no .env:**
```env
ENVIRONMENT=production
BASE_URL=https://biscoitos.seudominio.com.br  # Seu domínio
PIX_KEY=5511986411335
PIX_MERCHANT_NAME="Biscoitos da Sorte"
ADMIN_EMAIL=seu@email.com
```

### 4. Iniciar aplicação

```bash
docker compose up -d
```

### 5. Configurar Nginx

```bash
# Copiar configuração
sudo cp nginx-vhost-biscoitos.conf /etc/nginx/sites-available/biscoitos.seudominio.com.br

# Editar com seu domínio
sudo nano /etc/nginx/sites-available/biscoitos.seudominio.com.br
# Alterar: server_name biscoitos.seudominio.com.br;

# Ativar site
sudo ln -s /etc/nginx/sites-available/biscoitos.seudominio.com.br /etc/nginx/sites-enabled/

# Testar configuração
sudo nginx -t

# Recarregar Nginx
sudo systemctl reload nginx
```

### 6. Configurar SSL (HTTPS)

```bash
# Instalar Certbot se não tiver
sudo apt install certbot python3-certbot-nginx -y

# Obter certificado SSL
sudo certbot --nginx -d biscoitos.seudominio.com.br

# Renovação automática já está configurada
```

### 7. Testar

Acesse: **https://biscoitos.seudominio.com.br**

---

## 🔧 Comandos Úteis

### Gerenciar aplicação

```bash
# Ver status
docker compose ps

# Ver logs
docker compose logs -f app

# Reiniciar
docker compose restart

# Parar
docker compose down

# Atualizar
git pull  # ou rsync novamente
docker compose build
docker compose up -d
```

### Gerenciar Nginx

```bash
# Testar configuração
sudo nginx -t

# Recarregar
sudo systemctl reload nginx

# Reiniciar
sudo systemctl restart nginx

# Ver logs
sudo tail -f /var/log/nginx/biscoitos_access.log
sudo tail -f /var/log/nginx/biscoitos_error.log
```

### Backup

```bash
# Backup do banco
docker compose exec -T app cat /var/www/html/database.sqlite > backup-$(date +%Y%m%d).sqlite

# Backup dos uploads
tar -czf uploads-backup-$(date +%Y%m%d).tar.gz uploads/
```

---

## 🔒 Firewall

Sua VPS já deve ter firewall configurado. Se precisar abrir a porta 8080:

```bash
# Ver regras atuais
sudo ufw status

# Permitir porta 8080 (se for acessar direto)
sudo ufw allow 8080/tcp

# Recarregar
sudo ufw reload
```

**Nota:** Se usar Nginx proxy (Opção 2), não precisa abrir porta 8080.

---

## 📊 Monitoramento

### Ver recursos

```bash
# Uso do container
docker stats biscoitos-sorte

# Espaço em disco
df -h

# Memória
free -h
```

### Logs importantes

```bash
# Logs da aplicação
docker compose logs -f app

# Logs do Nginx (se usar proxy)
sudo tail -f /var/log/nginx/biscoitos_error.log

# Logs do sistema
sudo journalctl -u docker -f
```

---

## 🐛 Troubleshooting

### Container não inicia

```bash
# Ver erro
docker compose logs app

# Verificar porta
sudo ss -tlnp | grep 8080

# Rebuild
docker compose down
docker compose build --no-cache
docker compose up -d
```

### Nginx erro 502 Bad Gateway

```bash
# Verificar se app está rodando
docker compose ps

# Verificar se porta 8080 está respondendo
curl http://localhost:8080

# Ver logs do Nginx
sudo tail -f /var/log/nginx/biscoitos_error.log
```

### Erro de permissão

```bash
# Corrigir permissões
docker compose exec app chown -R www-data:www-data /var/www/html
docker compose exec app chmod -R 777 /var/www/html/uploads
docker compose exec app chmod -R 777 /var/www/html/logs
```

---

## 🎯 Checklist de Deploy

- [ ] Arquivos enviados para VPS
- [ ] .env configurado
- [ ] BASE_URL definida
- [ ] PIX_KEY configurada
- [ ] Docker build executado
- [ ] Container iniciado e rodando
- [ ] Aplicação acessível via porta 8080
- [ ] DNS configurado (se usar domínio)
- [ ] Nginx virtual host criado (se usar domínio)
- [ ] SSL configurado (se usar domínio)
- [ ] Backup configurado

---

## 📞 Comandos Rápidos

```bash
# Conectar na VPS
ssh -i /home/senan/Downloads/vps/ssh-key-2026-04-26.key ubuntu@167.234.247.255

# Enviar arquivos
rsync -avz -e "ssh -i /home/senan/Downloads/vps/ssh-key-2026-04-26.key" \
  --exclude='.git' ./ ubuntu@167.234.247.255:~/biscoitos-sorte/

# Deploy rápido (na VPS)
cd ~/biscoitos-sorte && docker compose up -d --build

# Ver logs (na VPS)
cd ~/biscoitos-sorte && docker compose logs -f
```

---

**Pronto para deploy!** 🚀

Escolha a Opção 1 para testar rapidamente ou Opção 2 para produção com domínio.
