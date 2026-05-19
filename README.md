# OASSAB — Site institucional + CMS (Laravel)

Site institucional da **OASSAB — Obras de Assistência e de Serviço Social da Arquidiocese de Brasília** em Laravel 11 + Blade + Tailwind CSS, com painel administrativo para gerenciar **Notícias**, **Projetos** e **Portal Transparência**.

## Stack

- **PHP 8.2+**
- **Laravel 11**
- **MySQL 5.7+** (HostGator em produção)
- **Blade** + **Tailwind CSS 3** + **Vite**
- Fonte: **Jost** (Google Fonts)
- Editor WYSIWYG: **Quill 2** via CDN
- Paleta: azul `#1f2754` · azul-escuro `#080f33` · laranja `#f14a16` · creme `#fbfbfb`

## Estrutura

```
app/
  Http/
    Controllers/
      PageController.php          # site público (Eloquent)
      Auth/LoginController.php    # login/logout
      Admin/
        DashboardController.php
        PostController.php        # CRUD de posts
        ProfileController.php     # alterar senha
    Middleware/
      EnsureUserIsAdmin.php       # alias 'admin' em bootstrap/app.php
    Requests/Admin/
      StorePostRequest.php
  Models/
    Post.php                      # belongsToMany(Category), scopes published/forCategory
    Category.php
    User.php                      # is_admin
database/
  migrations/                     # categories, posts, category_post, is_admin
  seeders/                        # CategoriesSeeder, PostsSeeder, AdminUserSeeder
public/                           # versionado no Git e enviado no deploy (FTP)
  storage -> storage/app/public   # symlink (não versionado; criar no servidor com storage:link)
  images/                         # logo, ícone, hero, fundos e capas legadas
  files/                          # PDFs dos relatórios
  favicon.ico
  build/                          # gerado no CI (npm run build), não commitado
storage/app/public/
  posts/          # capas de notícias/projetos (JPG/PNG/WEBP)
  editais/        # PDF principal + anexos do Programa Edital Aberto
  transparency/   # PDFs do Portal Transparência
  (tudo acima versionado no Git; ver deploy abaixo)
resources/
  css/app.css
  js/site.js, app.js
  views/
    layouts/app.blade.php
    components/                   # site-header, site-footer, page-hero, post-card, ...
    pages/                        # home, quem-somos, projetos, transparencia, contato, noticias, relatorios, post
    admin/
      layouts/admin.blade.php
      auth/login.blade.php
      dashboard.blade.php
      posts/{index,form}.blade.php
      profile/edit.blade.php
routes/web.php
```

## Rotas

### Site público

| Caminho                       | View                  |
| ----------------------------- | --------------------- |
| `/`                           | `pages.home`          |
| `/quem-somos`                 | `pages.quem-somos`    |
| `/projetos`                   | `pages.projetos`      |
| `/transparencia`              | `pages.transparencia` |
| `/contato`                    | `pages.contato`       |
| `/noticias`                   | `pages.noticias`      |
| `/relatorios-de-atividades`   | `pages.relatorios`    |
| `/posts/{slug}`               | `pages.post`          |

### Painel administrativo

Todas exigem usuário com `is_admin = true`.

| Caminho                            | Ação                        |
| ---------------------------------- | --------------------------- |
| `GET  /admin/login`                | tela de login               |
| `POST /admin/login`                | autenticação                |
| `POST /admin/logout`               | sair                        |
| `GET  /admin`                      | dashboard                   |
| `GET  /admin/posts`                | lista (filtra por categoria/busca) |
| `GET  /admin/posts/create`         | novo post                   |
| `POST /admin/posts`                | criar                       |
| `GET  /admin/posts/{post}/edit`    | editar                      |
| `PUT  /admin/posts/{post}`         | atualizar                   |
| `DELETE /admin/posts/{post}`       | excluir                     |
| `GET  /admin/profile`              | editar perfil/senha         |
| `PUT  /admin/profile`              | salvar perfil/senha         |

## Como rodar localmente

Pré-requisitos: PHP 8.2+, Composer, Node 18+ e npm.

```bash
composer install
npm install

# (Apenas na primeira vez)
cp .env.example .env
php artisan key:generate

# Configure DB_HOST/DB_DATABASE/DB_USERNAME/DB_PASSWORD no .env
php artisan migrate --force
php artisan db:seed --force        # importa as categorias, 30 posts iniciais e o admin padrão
php artisan storage:link

# Em uma aba:
npm run dev

# Em outra aba:
php artisan serve
```

Acesse <http://127.0.0.1:8000>.

## Primeira vez: MySQL no cPanel (HostGator)

O **Remote MySQL** com host `%` só libera **de qual IP** pode conectar. Ele **não** cria o usuário MySQL, **não** cria o banco e **não** cria as tabelas do Laravel.

### 1. No cPanel → MySQL® Databases

1. **Create New Database** — ex.: `oassab44_laravel` (o nome completo no cPanel já vem com o prefixo da conta).
2. **Create New User** — ex.: `oassab44_admin` (pode ser outro nome; **não precisa** ser igual ao do banco).
3. **Add User To Database** — associe o usuário ao banco com **ALL PRIVILEGES**.
4. Anote **nome exato** do banco, do usuário e a **senha** (copie para o `.env`).

### 2. Remote MySQL (acesso da sua máquina)

cPanel → **Remote MySQL** → adicione `%` (ou seu IP fixo). Isso é só firewall; o passo 1 ainda é obrigatório.

### 3. `.env` (local apontando para o remoto)

```env
DB_CONNECTION=mysql
DB_HOST=216.172.172.253
DB_PORT=3306
DB_DATABASE=oassab44_laravel
DB_USERNAME=oassab44_admin
DB_PASSWORD=<senha criada no passo 1>
```

`DB_USERNAME` deve ser o **usuário MySQL** do cPanel, não o nome do banco (a menos que você tenha criado os dois com o mesmo nome de propósito).

No **servidor** (produção), use `DB_HOST=localhost` com o mesmo banco/usuário/senha.

### 4. Criar tabelas e admin (na sua máquina ou no Terminal do cPanel)

```bash
php artisan config:clear
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
```

Isso cria `users`, `posts`, `categories`, etc., e o admin padrão (`admin@oassab.org.br` / `OASSAB@2026`).

### 5. Conferir

```bash
php artisan db:show
```

Se listar tabelas, a conexão está ok. Teste o login em `/admin/login`.

## Erro 500 ou falha no login do admin

A tela de login (`GET /admin/login`) é só HTML; o **POST** consulta a tabela `users` no MySQL. Se a conexão falhar, o Laravel responde 500 (ou mensagem de erro no formulário, após atualização do `LoginController`).

Confira `storage/logs/laravel.log`. O caso mais comum:

```
Access denied for user '...'@'seu-ip-externo' (using password: YES)
```

**Causas comuns:** usuário/banco **não criados** no cPanel; usuário sem **ALL PRIVILEGES** no banco; `DB_USERNAME`/`DB_PASSWORD` diferentes do cPanel; migrations não rodadas (`users` inexistente); ou IP não liberado em **Remote MySQL** (além do `%`, o usuário MySQL do passo “MySQL® Databases” ainda é obrigatório).

**Correção:**

| Ambiente | O que fazer |
|----------|-------------|
| **Produção (HostGator)** | No `.env` do servidor: `DB_HOST=localhost`, usuário/senha/banco exatamente como no cPanel → MySQL® Databases. Rode `php artisan config:clear` (ou `config:cache`) no servidor. |
| **Local com MySQL da HostGator** | cPanel → **Remote MySQL** → adicione seu IP público. Use a mesma senha do cPanel (se mudou lá, atualize o `.env` local). |
| **Local sem Remote MySQL** | Use SQLite no `.env` (`DB_CONNECTION=sqlite`), rode `migrate` + `db:seed` — banco separado do site em produção. |

Isso **não** se resolve com deploy de código: é configuração do `.env` no servidor e/ou permissão MySQL no cPanel. O deploy via GitHub Actions **não envia** o `.env` (está no `.gitignore`).

## Credenciais padrão do admin

```
URL:    /admin/login
E-mail: admin@oassab.org.br
Senha:  OASSAB@2026
```

> **Importante:** após o primeiro login, abra `/admin/profile` e troque a senha. As credenciais acima existem apenas para o acesso inicial.

## Como editar o conteúdo

1. Acesse `/admin/login` e entre com as credenciais.
2. No menu lateral clique em **Posts** para listar, criar, editar ou excluir.
3. Em cada post você define:
   - Título, slug (auto se vazio), data, resumo e corpo (editor Quill).
   - **Categorias** — marque uma ou mais entre **Notícias**, **Projetos** e **Portal Transparência**. O mesmo post pode aparecer em mais de uma categoria simultaneamente.
   - Capa (JPG/PNG/WEBP, até 4 MB) — fica em `storage/app/public/posts/{slug}/`.
   - Após qualquer upload no admin, **commit** a pasta correspondente em `storage/app/public/` antes do deploy (ver item 5 em “Notas para deploy”).
   - Status **publicado** (visível no site) ou rascunho.

Posts publicados aparecem automaticamente em:
- `/noticias` e na home (3 mais recentes) → categoria **Notícias**
- `/projetos` → categoria **Projetos**
- `/transparencia` → categoria **Portal Transparência**

## Performance

O site usa três camadas de cache que se sobrepõem e são invalidadas automaticamente quando o admin cria/edita/exclui um post:

1. **Cache de queries** ([`App\Services\ContentCache`](app/Services/ContentCache.php)) — cada página pública envolve a consulta ao banco em `Cache::remember(...)`. Sem hit no MySQL remoto entre escritas.
2. **Cache de página inteira** (middleware [`page.cache`](app/Http/Middleware/CachePublicPages.php)) — para visitantes anônimos em GET, o HTML completo é guardado em arquivo. Cabeçalho de resposta `X-Page-Cache: HIT|MISS` para diagnóstico.
3. **Invalidação automática** ([`App\Observers\PostObserver`](app/Observers/PostObserver.php)) — ao salvar/excluir/restaurar um post, `Cache::flush()` é chamado, repopulando na próxima visita.

Imagens de posts são otimizadas pelo [`App\Services\ImageOptimizer`](app/Services/ImageOptimizer.php) no upload do admin: escala para no máximo 1600px, gera 3 variantes (480/800/1200) em **WebP** + **JPG**. O Blade usa `<picture>`/`srcset` via [`<x-responsive-image>`](resources/views/components/responsive-image.blade.php).

### Pré-requisitos da hospedagem

- Extensão **php-gd** (ou php-imagick) habilitada.
- Apache com **mod_rewrite**, **mod_deflate** e **mod_expires** (já configurados em [`public/.htaccess`](public/.htaccess)).
- Diretórios `storage/framework/cache/data` e `storage/framework/sessions` graváveis.

## Build de produção

```bash
npm install
npm run build

composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link

# Caches do Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Otimiza imagens existentes (gera variantes WebP + JPG)
php artisan images:optimize
```

> Use `php artisan optimize:clear` antes de cada novo build se algo travar com cache antigo.

## Notas para deploy na HostGator

1. Aponte o document root para `public/`.
2. Garanta que `storage/` e `bootstrap/cache/` estejam graváveis (`chmod -R ug+rwX`).
3. Verifique no `.env` da produção:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `CACHE_STORE=file` e `SESSION_DRIVER=file` (HostGator não tem Redis; o driver `database` deixa o site lento porque toda request vai ao MySQL remoto).
4. Rode `php artisan storage:link` (ou crie symlink `public/storage -> storage/app/public`). Sem esse link, URLs `/storage/posts/...` retornam 404 mesmo com os arquivos no disco.
5. **Arquivos enviados pelo admin** (não vão sozinhos para produção — é preciso commit + push):
   | Tipo | Pasta no Git |
   |------|----------------|
   | Capa de post | `storage/app/public/posts/{slug}/` |
   | PDF do edital (+ anexos) | `storage/app/public/editais/{slug}/` |
   | PDF do Portal Transparência | `storage/app/public/transparency/{slug}/` |
   ```bash
   git add storage/app/public/
   git commit -m "Atualiza arquivos do storage público"
   git push
   ```
6. Primeira vez:
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   php artisan images:optimize
   ```
7. Sempre que atualizar código:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan event:cache
   ```
8. Troque a senha do admin imediatamente após o primeiro login.

## Como rodar em desenvolvimento

Use `php artisan serve` (não `php -S`) — o `php -S` entrega arquivos estáticos via PHP, o que torna o site lento em dev.

```bash
php artisan serve   # http://127.0.0.1:8000
npm run dev         # em outro terminal
```

Para testar como produção localmente, copie o `.env.example` para `.env`, ajuste o DB e rode os mesmos passos do build de produção.

## Fora de escopo

- Versionamento/revisões de post.
- Múltiplos níveis de usuário (apenas `is_admin`).
- Comentários, busca full-text, tags livres.
- E-mail de recuperação de senha (a senha é trocada manualmente em `/admin/profile`).
