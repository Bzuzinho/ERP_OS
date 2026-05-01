# JuntaOS — Desenvolvimento Local no Windows

## Requisitos

- **PHP 8.3+** — [https://windows.php.net/download/](https://windows.php.net/download/) (Thread Safe, x64)
- **Composer** — [https://getcomposer.org/Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe)
- **Node.js 20+** — [https://nodejs.org](https://nodejs.org)
- **Git** — [https://git-scm.com](https://git-scm.com)
- **SQLite** (incluído no PHP por defeito no Windows)

---

## Instalação inicial

### 1. Clonar o repositório

```powershell
git clone https://github.com/SEU_ORG/ERP_OS.git juntaos
cd juntaos
```

### 2. Instalar dependências PHP

```powershell
composer install
```

### 3. Instalar dependências JavaScript

```powershell
npm install
```

### 4. Configurar ambiente

```powershell
cp .env.example .env
```

Editar `.env` para SQLite local:

```dotenv
APP_NAME=JuntaOS
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# Deixar DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD comentados

JUNTAOS_DEMO_MODE=false
```

### 5. Gerar chave da aplicação

```powershell
php artisan key:generate
```

### 6. Criar base de dados SQLite

```powershell
New-Item -ItemType File -Path database\database.sqlite
```

### 7. Migrar e popular a base de dados

```powershell
php artisan migrate:fresh --seed
```

---

## Arrancar o servidor de desenvolvimento

### Terminal 1 — Servidor PHP:

```powershell
php artisan serve
```

A aplicação fica disponível em: [http://localhost:8000](http://localhost:8000)

### Terminal 2 — Compilação de assets (hot reload):

```powershell
npm run dev
```

---

## Credenciais de demo

| Campo | Valor |
|-------|-------|
| Email | `admin@juntaos.local` |
| Password | `password` |
| Role | `super_admin` |

---

## Comandos úteis

```powershell
# Limpar caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Ver rotas
php artisan route:list

# Correr testes
php artisan test

# Compilar assets para produção
npm run build

# Reconstruir BD de raiz
php artisan migrate:fresh --seed
```

---

## Nota sobre npm no PowerShell

Em algumas instalações do Windows, pode ser necessário usar `npm.cmd` em vez de `npm`:

```powershell
npm.cmd run dev
npm.cmd run build
```

---

## Configurar PostgreSQL/Neon localmente

Se pretender usar PostgreSQL em vez de SQLite localmente:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=juntaos_dev
DB_USERNAME=postgres
DB_PASSWORD=password
DB_SSLMODE=prefer
```

Ver [docs/database-neon.md](database-neon.md) para configuração com Neon.
