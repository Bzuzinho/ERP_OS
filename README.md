# JuntaOS

Plataforma de gestão municipal para Juntas de Freguesia e autarquias locais.

## Stack

- **Laravel 11** + **Inertia.js** + **React 19** + **TypeScript**
- **Tailwind CSS**
- **Spatie Laravel Permission**
- **PostgreSQL / Neon** (produção) ou **SQLite** (desenvolvimento local)
- **Vite** (bundler)

## Módulos implementados

| Módulo | Área |
|---|---|
| CRM Municipal (Contactos/Munícipes) | Admin |
| Pedidos / Tickets | Admin + Portal |
| Tarefas | Admin |
| Agenda / Eventos | Admin + Portal |
| Documentos / Atas | Admin + Portal |
| Espaços / Reservas | Admin + Portal |
| Recursos Materiais (Inventário) | Admin |
| Recursos Humanos | Admin |
| Planeamento Operacional | Admin + Portal |
| Relatórios | Admin |
| Dashboards | Admin + Portal |
| Exportação CSV | Admin |

## Requisitos locais

- PHP 8.3+
- Composer
- Node.js 20+
- SQLite (incluído no PHP) ou PostgreSQL

## Instalação local (Windows)

```powershell
git clone https://github.com/SEU_ORG/ERP_OS.git juntaos
cd juntaos
composer install
npm install
cp .env.example .env
# Editar .env: DB_CONNECTION=sqlite
New-Item -ItemType File -Path database\database.sqlite
php artisan key:generate
php artisan migrate:fresh --seed
```

Arrancar:

```powershell
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

Ver [docs/local-development-windows.md](docs/local-development-windows.md) para guia detalhado.

## Credenciais de demo

| Campo | Valor |
|---|---|
| Email | `admin@juntaos.local` |
| Password | `password` |

## Instalação com PostgreSQL/Neon

Ver [docs/database-neon.md](docs/database-neon.md).

## Deploy em Oracle Cloud VM

Ver [docs/deploy-oracle-vm.md](docs/deploy-oracle-vm.md).

## Comandos de validação

```bash
php artisan migrate:fresh --seed
npm run build
php artisan test
php artisan route:list | head -40
php artisan config:clear && php artisan route:clear && php artisan view:clear
```

## Estrutura de áreas

- `/admin` — Área de administração (requer permissão `admin.access`)
- `/portal` — Portal do munícipe (requer autenticação)
- `/login` — Autenticação



## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
