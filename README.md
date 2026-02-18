# 🥠 Biscoitos da Sorte - Micro SaaS

Sistema web de biscoitos da sorte digitais com mensagens personalizadas por categoria e pagamento via PIX.

## 🚀 Início Rápido

### Desenvolvimento Local

```bash
# 1. Iniciar servidor
php -S localhost:8000

# Ou usar o script
./start-server.sh

# 2. Acessar
http://localhost:8000
```

### Credenciais de Teste

- **Usuário:** `teste@biscoitos.com` / `123456`
- **Admin:** `admin@biscoitos.com` / `admin123`

## 📚 Documentação

Toda a documentação está na pasta `docs/`:

- **[docs/INSTALL.md](docs/INSTALL.md)** - Instalação completa
- **[docs/GUIA_TESTES.md](docs/GUIA_TESTES.md)** - Como testar tudo
- **[docs/DEPLOY_PRODUCAO.md](docs/DEPLOY_PRODUCAO.md)** - Deploy em produção
- **[docs/CONFIGURAR_PIX.md](docs/CONFIGURAR_PIX.md)** - Configurar pagamentos
- **[docs/README.md](docs/README.md)** - Índice completo

## ✨ Funcionalidades

### Para Usuários
- ✅ Cadastro e login seguro
- ✅ 5 categorias de sorte (Amor, Trabalho, Dinheiro, Vida, Amizades)
- ✅ Pagamento via PIX com QR Code
- ✅ Visualização de mensagens únicas
- ✅ Números da sorte
- ✅ Compartilhamento para stories
- ✅ Histórico de sortes
- ✅ Perfil personalizável com foto
- ✅ Design responsivo (mobile-first)

### Para Administradores
- ✅ Dashboard com estatísticas
- ✅ Gráficos de vendas
- ✅ Controle de estoque de frases
- ✅ Upload em massa de frases (CSV/XLSX)
- ✅ Relatórios detalhados

## 🛠️ Tecnologias

- **Backend:** PHP 7.4+
- **Banco:** SQLite (dev) / MySQL (prod)
- **Frontend:** HTML5, CSS3, JavaScript
- **Pagamentos:** PIX (QR Code EMV)
- **Segurança:** bcrypt, prepared statements, CSRF, XSS protection

## 📁 Estrutura

```
biscoitos/
├── admin/              # Painel administrativo
├── assets/             # CSS, JS, imagens
├── config/             # Configurações
├── docs/               # Documentação completa
├── includes/           # Bibliotecas
├── logs/               # Logs do sistema
├── pages/              # Páginas da aplicação
├── uploads/            # Uploads de usuários
├── .env                # Variáveis de ambiente
├── index.php           # Página inicial
└── start-server.sh     # Iniciar servidor local
```

## 🔒 Segurança

- Proteção contra SQL Injection
- Proteção contra XSS
- Tokens CSRF
- Senhas com bcrypt
- Rate limiting
- Validação de sessões
- Logs de atividade

## 📦 Deploy

Para colocar em produção, siga o checklist completo em **[docs/DEPLOY_PRODUCAO.md](docs/DEPLOY_PRODUCAO.md)**

Principais passos:
1. Migrar para MySQL
2. Configurar PIX real
3. Habilitar HTTPS
4. Configurar .env
5. Ajustar permissões
6. Testar tudo

## 🧪 Testes

Execute os testes seguindo **[docs/GUIA_TESTES.md](docs/GUIA_TESTES.md)**

```bash
# Testar localmente
php -S localhost:8000

# Acessar e testar funcionalidades
# Ver guia completo em docs/GUIA_TESTES.md
```

## 📝 Licença

Proprietary - Todos os direitos reservados

## 🆘 Suporte

Consulte a documentação em `docs/` ou verifique:
- Logs: `logs/activity.log`
- Erros: `logs/php-errors.log`
- Problemas comuns: `docs/GUIA_TESTES.md`
