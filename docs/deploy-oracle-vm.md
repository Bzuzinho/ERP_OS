# JuntaOS — Deploy em Oracle Cloud VM (Ubuntu)

## Requisitos do servidor

- Ubuntu 22.04 LTS ou 24.04 LTS
- 2 vCPU / 4 GB RAM (mínimo recomendado para produção)
- Portas abertas: 80, 443 (via Security List na Oracle Cloud)

---

## 1. Instalar dependências base

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git unzip software-properties-common
```

### PHP 8.3 + extensões

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-pgsql php8.3-mbstring \
  php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip php8.3-intl php8.3-gd \
  php8.3-redis php8.3-opcache
```

### Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Node.js (via nvm ou pacote)

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### Nginx

```bash
sudo apt install -y nginx
```

### Supervisor

```bash
sudo apt install -y supervisor
```

### Certbot (SSL)

```bash
sudo apt install -y certbot python3-certbot-nginx
```

---

## 2. Criar utilizador e directório da aplicação

```bash
sudo useradd -m -s /bin/bash juntaos
sudo mkdir -p /var/www/juntaos
sudo chown juntaos:www-data /var/www/juntaos
sudo chmod 755 /var/www/juntaos
```

---

## 3. Clonar e configurar a aplicação

```bash
cd /var/www/juntaos
git clone https://github.com/SEU_ORG/ERP_OS.git .
cp .env.example .env
```

Editar `.env` com as configurações de produção:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.juntaos.pt
DB_CONNECTION=pgsql
DB_SSLMODE=require
# ... resto das variáveis
```

---

## 4. Instalar dependências e compilar

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

---

## 5. Migrar base de dados

```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
```

---

## 6. Optimizar para produção

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## 7. Permissões de ficheiros

```bash
sudo chown -R www-data:www-data /var/www/juntaos/storage
sudo chown -R www-data:www-data /var/www/juntaos/bootstrap/cache
sudo chmod -R 775 /var/www/juntaos/storage
sudo chmod -R 775 /var/www/juntaos/bootstrap/cache
```

---

## 8. Configurar Nginx

Criar ficheiro `/etc/nginx/sites-available/juntaos`:

```nginx
server {
    listen 80;
    server_name app.juntaos.pt;
    root /var/www/juntaos/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Activar o site e recarregar Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/juntaos /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Activar SSL com Certbot:

```bash
sudo certbot --nginx -d app.juntaos.pt
```

---

## 9. Configurar Supervisor (queue worker)

Criar ficheiro `/etc/supervisor/conf.d/juntaos-worker.conf`:

```ini
[program:juntaos-worker]
command=php /var/www/juntaos/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
directory=/var/www/juntaos
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/juntaos/storage/logs/worker.log
```

Activar:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start juntaos-worker:*
```

---

## 10. Configurar Cron (scheduler)

Adicionar ao crontab do utilizador `www-data`:

```bash
sudo crontab -u www-data -e
```

Adicionar a linha:

```cron
* * * * * cd /var/www/juntaos && php artisan schedule:run >> /dev/null 2>&1
```

---

## 11. Comandos de deploy futuro (rolling update)

```bash
cd /var/www/juntaos
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
sudo systemctl reload nginx
```

---

## 12. Verificação pós-deploy

```bash
php artisan about
php artisan route:list | head -30
curl -I https://app.juntaos.pt/up
```

---

## Notas de segurança Oracle Cloud

- Configurar **Security List** para permitir apenas portas 22, 80, 443.
- Usar **SSH key** (não password) para acesso ao servidor.
- Manter o servidor actualizado: `sudo apt update && sudo apt upgrade`.
- Configurar `fail2ban` para protecção contra brute-force SSH.
