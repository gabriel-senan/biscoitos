# Biscoitos da Sorte - Micro SaaS

Sistema web de biscoitos da sorte digitais com mensagens personalizadas por categoria e pagamento via PIX.

## Início Rápido

### Desenvolvimento Local

```bash
# 1. Iniciar servidor
php -S localhost:8000

# 2. Acessar
http://localhost:8000
```

### Credenciais de Teste

- **Usuário:** `teste@biscoitos.com` / `123456`
- **Admin:** `admin@biscoitos.com` / `admin123`

## Funcionalidades

### Para Usuários
- Cadastro e login seguro
- Sistema de créditos (R$ 5,00 = 5 créditos)
- 5 categorias de sorte (Amor, Trabalho, Dinheiro, Vida, Amizades)
- Pagamento via PIX com QR Code
- Cada crédito = 1 biscoito da sorte
- Pacotes com bônus de créditos
- Visualização de mensagens únicas
- Números da sorte
- Compartilhamento para stories
- Histórico de sortes e transações
- Perfil personalizável com foto
- Design responsivo (mobile-first)

### Para Administradores
- Dashboard com estatísticas
- Gráficos de vendas
- Controle de estoque de frases
- Upload em massa de frases (CSV/XLSX)
- Relatórios detalhados

## Tecnologias

- **Backend:** PHP 7.4+
- **Banco:** SQLite (dev) / MySQL (prod)
- **Frontend:** HTML5, CSS3, JavaScript
- **Pagamentos:** PIX (QR Code EMV)
- **Segurança:** bcrypt, prepared statements, CSRF, XSS protection

## Estrutura do Projeto

```
biscoitos/
├── admin/              # Painel administrativo
├── assets/             # CSS, JS, imagens
├── config/             # Configurações e banco
├── docs/               # Documentação
├── includes/           # Bibliotecas PHP
├── logs/               # Logs do sistema
├── pages/              # Páginas da aplicação
├── uploads/            # Uploads de usuários
├── .env                # Variáveis de ambiente
├── index.php           # Página inicial
└── README.md           # Este arquivo
```

## Configuração

### 1. Banco de Dados

O sistema usa SQLite por padrão. Para produção, recomenda-se MySQL.

**SQLite (desenvolvimento):**
```bash
# Já configurado, nenhuma ação necessária
```

**MySQL (produção):**
```bash
# 1. Criar banco de dados
mysql -u root -p
CREATE DATABASE biscoitos_sorte;

# 2. Importar estrutura
mysql -u root -p biscoitos_sorte < config/setup.sql

# 3. Configurar .env
DB_HOST=localhost
DB_NAME=biscoitos_sorte
DB_USER=seu_usuario
DB_PASS=sua_senha
```

### 2. Configurar PIX

Edite o arquivo `.env`:

```bash
PIX_MERCHANT_NAME="Biscoitos da Sorte"
PIX_MERCHANT_CITY="SAO PAULO"
PIX_KEY_TYPE=telefone
PIX_KEY=5511999999999
```

Tipos de chave PIX suportados:
- `telefone` - Telefone com DDD
- `email` - Email
- `cpf` - CPF
- `cnpj` - CNPJ
- `aleatoria` - Chave aleatória

### 3. Configurar URL Base

```bash
BASE_URL=http://localhost:8000
```

## Sistema de Créditos

### Como Funciona

1. Usuário compra créditos via PIX
2. Cada crédito permite abrir 1 biscoito da sorte
3. Créditos não expiram
4. Pacotes maiores ganham bônus

### Pacotes Disponíveis

| Créditos | Valor | Bônus | Total |
|----------|-------|-------|-------|
| 5        | R$ 5  | 0     | 5     |
| 10       | R$ 10 | 0     | 10    |
| 25       | R$ 25 | 5     | 30    |
| 50       | R$ 50 | 10    | 60    |
| 100      | R$ 100| 25    | 125   |

### Fluxo de Compra

1. Usuário seleciona pacote
2. Sistema gera QR Code PIX
3. Usuário paga via PIX
4. Usuário confirma pagamento
5. Créditos são adicionados
6. Usuário é redirecionado para categorias

## Segurança

### Proteções Implementadas

- **SQL Injection:** Prepared statements em todas as queries
- **XSS:** htmlspecialchars em todos os outputs + Content Security Policy
- **CSRF:** Tokens de validação em todos os formulários
- **Upload Seguro:** Validação de MIME type real, extensão e conteúdo
- **Brute Force:** Rate limiting (5 tentativas em 5 minutos)
- **Senhas:** bcrypt + política de senha forte (8+ caracteres, maiúscula, minúscula, número)
- **Session Fixation:** session_regenerate_id após login
- **Headers de Segurança:** X-Frame-Options, X-Content-Type-Options, CSP, etc.
- **Logs:** Registro de atividades e tentativas suspeitas

### Arquivos de Segurança

- `includes/security.php` - Funções de segurança
- `includes/security_headers.php` - Headers HTTP de segurança
- `.htaccess` - Proteção de arquivos sensíveis

### Política de Senha

Senhas devem ter:
- Mínimo 8 caracteres
- Pelo menos 1 letra maiúscula
- Pelo menos 1 letra minúscula
- Pelo menos 1 número

### Rate Limiting

- Login: 5 tentativas em 5 minutos por IP
- Bloqueio automático após exceder limite
- Reset após login bem-sucedido

## Deploy em Produção

### Checklist de Segurança

- [ ] Migrar para MySQL
- [ ] Configurar PIX real
- [ ] Habilitar HTTPS
- [ ] Forçar HTTPS no .htaccess
- [ ] Configurar .env de produção
- [ ] Ajustar permissões de arquivos (755 para pastas, 644 para arquivos)
- [ ] Verificar headers de segurança
- [ ] Testar CSRF em formulários
- [ ] Testar rate limiting
- [ ] Testar upload de arquivo
- [ ] Configurar backup automático
- [ ] Configurar rotação de logs
- [ ] Testar fluxo completo
- [ ] Monitorar logs por 24h

### Implementar Headers de Segurança

Os headers já estão implementados em:
- `pages/abrir_sorte.php`
- `pages/processar_compra_creditos.php`
- `pages/confirmar_compra_creditos.php`
- `pages/upload_foto.php`

Para adicionar em outras páginas, inclua no início:
```php
require_once '../includes/security_headers.php';
```

### Verificar Segurança

```bash
# Testar headers
curl -I https://seudominio.com

# Verificar online
# https://securityheaders.com
# https://observatory.mozilla.org
```

### Passos Detalhados

**1. Preparar Servidor**
```bash
# Requisitos mínimos
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx
- SSL/HTTPS
```

**2. Configurar Banco**
```bash
mysql -u root -p
CREATE DATABASE biscoitos_sorte;
mysql -u root -p biscoitos_sorte < config/setup.sql
```

**3. Configurar .env**
```bash
ENVIRONMENT=production
DB_HOST=localhost
DB_NAME=biscoitos_sorte
DB_USER=usuario_producao
DB_PASS=senha_segura
BASE_URL=https://seudominio.com
PIX_KEY=sua_chave_pix_real
```

**4. Permissões**
```bash
chmod 755 -R .
chmod 777 uploads/
chmod 777 logs/
chmod 600 .env
```

**5. HTTPS**
```bash
# Usar Certbot para SSL gratuito
sudo certbot --apache -d seudominio.com
```

## Administração

### Acessar Painel Admin

```
http://localhost:8000/admin/
```

### Funcionalidades Admin

- Dashboard com métricas
- Gráficos de vendas
- Controle de frases por categoria
- Upload em massa (CSV/XLSX)
- Relatórios de estoque
- Logs de atividade

### Upload de Frases

**Formato CSV:**
```csv
categoria,frase,numeros_sorte
Amor,Você encontrará o amor verdadeiro,7-14-21-28-35-42
Trabalho,Sucesso na carreira,3-9-15-21-27-33
```

**Formato XLSX:**
- Coluna A: categoria
- Coluna B: frase
- Coluna C: numeros_sorte

## Testes

### Testar Localmente

```bash
# 1. Iniciar servidor
php -S localhost:8000

# 2. Acessar
http://localhost:8000

# 3. Fazer login
teste@biscoitos.com / 123456

# 4. Testar funcionalidades
- Comprar créditos
- Abrir biscoito
- Ver histórico
- Atualizar perfil
```

### Testar Admin

```bash
# 1. Acessar admin
http://localhost:8000/admin/

# 2. Login admin
admin@biscoitos.com / admin123

# 3. Testar funcionalidades
- Ver dashboard
- Upload de frases
- Ver relatórios
```

## Monitoramento

### Logs

```bash
# Ver logs de atividade
tail -f logs/activity.log

# Ver erros PHP
tail -f logs/php-errors.log
```

### Métricas Importantes

```sql
-- Total de usuários
SELECT COUNT(*) FROM usuarios;

-- Total de créditos vendidos
SELECT SUM(creditos) FROM transacoes_creditos WHERE tipo = 'compra';

-- Receita total
SELECT SUM(valor) FROM pedidos WHERE status = 'pago';

-- Frases por categoria
SELECT categoria_id, COUNT(*) FROM sortes GROUP BY categoria_id;
```

## Backup

### Backup Manual

```bash
# Backup SQLite
cp database.sqlite backup_$(date +%Y%m%d).sqlite

# Backup MySQL
mysqldump -u root -p biscoitos_sorte > backup_$(date +%Y%m%d).sql

# Backup uploads
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz uploads/
```

### Backup Automático (Cron)

```bash
# Adicionar ao crontab
0 2 * * * /path/to/backup.sh
```

## Troubleshooting

### Problema: QR Code não aparece

**Solução:**
1. Verificar se PIX_KEY está configurada no .env
2. Verificar logs de erro
3. Testar geração manual de PIX

### Problema: Créditos não são adicionados

**Solução:**
1. Verificar tabela transacoes_creditos
2. Verificar logs de atividade
3. Verificar função adicionarCreditos()

### Problema: Upload de foto não funciona

**Solução:**
1. Verificar permissões da pasta uploads/ (755)
2. Verificar tamanho máximo de upload no php.ini
3. Verificar logs de erro
4. Verificar se arquivo é imagem válida

### Problema: Erro de conexão com banco

**Solução:**
1. Verificar credenciais no .env
2. Verificar se MySQL está rodando
3. Verificar permissões do usuário do banco

### Problema: Token CSRF inválido

**Solução:**
1. Limpar cache do navegador
2. Verificar se sessão está ativa
3. Recarregar a página
4. Verificar se csrf_field() está no formulário

### Problema: Rate limiting bloqueando usuário legítimo

**Solução:**
1. Aguardar 5 minutos
2. Limpar sessão: `unset($_SESSION['rate_limit'])`
3. Ajustar limite em `includes/security.php`

## Suporte

Para problemas ou dúvidas:
1. Verificar logs em `logs/`
2. Consultar documentação em `docs/`
3. Verificar issues conhecidos

## Licença

Proprietary - Todos os direitos reservados

## Changelog

### v1.0.0 (Atual)
- Sistema de créditos completo
- Pagamento via PIX
- 5 categorias de mensagens
- Painel administrativo
- Upload em massa de frases
- Sistema de perfil com foto
- Histórico de transações
- Design responsivo
- **Segurança aprimorada:**
  - Proteção CSRF em todos os formulários
  - Upload de arquivo seguro com validação completa
  - Rate limiting no login
  - Política de senha forte
  - Headers de segurança (CSP, X-Frame-Options, etc.)
  - Validação segura de mensagens de erro

---

**Desenvolvido em:** 2024
**Versão:** 1.0.0
**Status:** Produção
**Nível de Segurança:** Alto
