# JuntaOS — Base de Dados Neon (PostgreSQL)

## Visão geral

A JuntaOS utiliza **Neon** como serviço de PostgreSQL gerido (serverless). Neon fornece branches, snapshots e pooling de ligações com suporte a SSL por defeito.

---

## Criar projeto no Neon

1. Aceder a [https://neon.tech](https://neon.tech) e criar conta.
2. Criar um novo projeto: `juntaos-prod` (ou `juntaos-dev` para ambiente de desenvolvimento).
3. Copiar a _connection string_ disponibilizada no dashboard.
4. Selecionar a região mais próxima (ex.: `eu-central-1` para Europa).

---

## Configurar variáveis de ambiente

Editar o ficheiro `.env` (não versionar credenciais reais):

```dotenv
DB_CONNECTION=pgsql
DB_HOST=ep-XXXXX.eu-central-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=neondb
DB_USERNAME=neondb_owner
DB_PASSWORD=CHAVE_SECRETA_AQUI
DB_SSLMODE=require
```

> **Nota:** O valor `DB_SSLMODE=require` é obrigatório para ligações ao Neon.

---

## Comandos de migração

### Primeira instalação (desenvolvimento/staging):

```bash
php artisan migrate:fresh --seed
```

> ⚠️ **NUNCA usar `migrate:fresh` em produção** — apaga todos os dados.

### Produção (aplicar migrações sem perda de dados):

```bash
php artisan migrate --force
```

### Verificar estado das migrações:

```bash
php artisan migrate:status
```

---

## Configuração do `config/database.php`

Verificar que a secção `pgsql` inclui o `sslmode`:

```php
'pgsql' => [
    'driver' => 'pgsql',
    'url' => env('DATABASE_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'laravel'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'public',
    'sslmode' => env('DB_SSLMODE', 'prefer'),
],
```

---

## Boas práticas

- Usar **branches Neon** para ambientes dev/staging separados.
- Não partilhar credenciais de produção.
- Fazer backups regulares através do Neon Console ou `pg_dump`.
- Monitorizar ligações activas no dashboard Neon (limite por plano).

---

## Exemplo de `.env` sem credenciais reais

```dotenv
APP_NAME=JuntaOS
APP_ENV=production
APP_KEY=base64:GERAR_COM_ARTISAN_KEY_GENERATE
APP_DEBUG=false
APP_URL=https://app.juntaos.pt

DB_CONNECTION=pgsql
DB_HOST=ep-xxx.eu-central-1.aws.neon.tech
DB_PORT=5432
DB_DATABASE=juntaos
DB_USERNAME=juntaos_owner
DB_PASSWORD=
DB_SSLMODE=require

CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
```
