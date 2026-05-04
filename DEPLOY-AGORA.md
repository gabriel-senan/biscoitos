# ⚡ Deploy AGORA na sua VPS

## 🎯 Sua VPS está pronta!

✅ Docker instalado  
✅ 12GB RAM disponível  
✅ 39GB disco livre  
✅ Nginx rodando (ghostprotocol.com.br)

## 🚀 Deploy em 3 Comandos

### 1️⃣ Configurar

```bash
cp .env.example .env
nano .env
```

**Edite apenas estas linhas:**
```env
BASE_URL=http://167.234.247.255:8080
PIX_KEY=5511986411335  # Sua chave PIX real
```

### 2️⃣ Deploy Automático

```bash
./deploy-to-vps.sh
```

### 3️⃣ Acessar

**http://167.234.247.255:8080**

---

## 🌐 Quer usar um domínio?

### Passo 1: Configurar DNS

Aponte um subdomínio para `167.234.247.255`:
```
biscoitos.seudominio.com.br  →  167.234.247.255
```

### Passo 2: Configurar Nginx na VPS

```bash
# Conectar na VPS
ssh -i /home/senan/Downloads/vps/ssh-key-2026-04-26.key ubuntu@167.234.247.255

# Criar virtual host
sudo nano /etc/nginx/sites-available/biscoitos.seudominio.com.br
```

**Cole esta configuração:**
```nginx
server {
    listen 80;
    server_name biscoitos.seudominio.com.br;

    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

**Ativar:**
```bash
sudo ln -s /etc/nginx/sites-available/biscoitos.seudominio.com.br /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Passo 3: SSL (HTTPS)

```bash
sudo certbot --nginx -d biscoitos.seudominio.com.br
```

### Passo 4: Atualizar .env

```bash
cd ~/biscoitos-sorte
nano .env
```

Alterar:
```env
BASE_URL=https://biscoitos.seudominio.com.br
```

Reiniciar:
```bash
docker compose restart
```

---

## 📝 Comandos Úteis

```bash
# Ver logs
ssh -i /home/senan/Downloads/vps/ssh-key-2026-04-26.key ubuntu@167.234.247.255 \
  'cd ~/biscoitos-sorte && docker compose logs -f'

# Reiniciar
ssh -i /home/senan/Downloads/vps/ssh-key-2026-04-26.key ubuntu@167.234.247.255 \
  'cd ~/biscoitos-sorte && docker compose restart'

# Status
ssh -i /home/senan/Downloads/vps/ssh-key-2026-04-26.key ubuntu@167.234.247.255 \
  'cd ~/biscoitos-sorte && docker compose ps'
```

---

## 🎉 Pronto!

Sua aplicação vai rodar na **porta 8080** junto com o Ghost Protocol (porta 6969).

**Acesso direto:** http://167.234.247.255:8080  
**Com domínio:** https://biscoitos.seudominio.com.br (após configurar)

---

## 🆘 Problemas?

### Container não inicia
```bash
ssh -i /home/senan/Downloads/vps/ssh-key-2026-04-26.key ubuntu@167.234.247.255
cd ~/biscoitos-sorte
docker compose logs app
```

### Porta 8080 não responde
```bash
# Verificar se está rodando
docker compose ps

# Testar localmente na VPS
curl http://localhost:8080
```

### Atualizar código
```bash
./deploy-to-vps.sh  # Envia e atualiza automaticamente
```

---

**Documentação completa:** [DEPLOY-VPS.md](DEPLOY-VPS.md)
