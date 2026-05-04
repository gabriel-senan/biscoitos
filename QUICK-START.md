# ⚡ Quick Start - Deploy em 5 minutos

## 🚀 Na sua VPS

```bash
# 1. Instalar Docker (se não tiver)
curl -fsSL https://get.docker.com | sh

# 2. Clonar/Upload do projeto
git clone seu-repo.git biscoitos-sorte
cd biscoitos-sorte

# 3. Configurar
cp .env.example .env
nano .env  # Edite BASE_URL e PIX_KEY

# 4. Deploy!
chmod +x deploy.sh
./deploy.sh
```

## ✅ Pronto!

Acesse: `http://seu-ip` ou `http://seu-dominio.com`

---

## 📝 Comandos Essenciais

```bash
# Ver logs
docker compose logs -f

# Parar
docker compose down

# Reiniciar
docker compose restart

# Backup
make backup

# Status
docker compose ps
```

---

## 🔧 Configuração Mínima (.env)

```env
BASE_URL=http://seu-dominio.com
PIX_KEY=5511999999999
ADMIN_EMAIL=seu@email.com
```

---

## 🌐 Adicionar HTTPS (opcional)

```bash
# Instalar Certbot
apt install certbot python3-certbot-nginx nginx -y

# Configurar proxy
cat > /etc/nginx/sites-available/biscoitos <<EOF
server {
    listen 80;
    server_name seu-dominio.com;
    location / {
        proxy_pass http://localhost:80;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
    }
}
EOF

ln -s /etc/nginx/sites-available/biscoitos /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx

# Obter SSL
certbot --nginx -d seu-dominio.com
```

---

## 🐛 Problemas?

```bash
# Ver erros
docker compose logs app

# Resetar tudo
docker compose down
docker compose up -d --force-recreate

# Permissões
docker compose exec app chmod -R 777 uploads logs
```

---

**Documentação completa:** `DEPLOY.md`
