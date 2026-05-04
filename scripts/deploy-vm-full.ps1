param(
    [Parameter(Mandatory = $true)]
    [string]$Message
)

$ErrorActionPreference = "Stop"

# =========================
# CONFIGURAÇÃO
# =========================

$ProjectPath = "C:\projetos\ERP_OS\ERP_OS"
$Branch = "main"

$VmUser = "ubuntu"
$VmHost = "89.168.82.41"
$VmProjectPath = "/var/www/juntaos"

$SshKey = "C:\projetos\keys\ssh-key-2026-05-04.key"

# =========================
# FUNÇÕES
# =========================

function Convert-ToUnixLineEndings {
    param([string]$Text)

    return ($Text -replace "`r`n", "`n" -replace "`r", "`n")
}

function Run-Command {
    param(
        [string]$Description,
        [scriptblock]$Command
    )

    Write-Host ""
    Write-Host "==> $Description" -ForegroundColor Cyan
    $global:LASTEXITCODE = 0
    & $Command

    if ($LASTEXITCODE -ne 0) {
        throw "Falha no passo '$Description' (exit code $LASTEXITCODE)."
    }
}

# =========================
# VALIDAÇÕES LOCAIS
# =========================

Run-Command "A entrar no projeto local" {
    if (-not (Test-Path $ProjectPath)) {
        throw "A pasta do projeto não existe: $ProjectPath"
    }

    Set-Location $ProjectPath
}

Run-Command "Validar ficheiros principais do projeto" {
    $requiredFiles = @(
        "artisan",
        "composer.json",
        "package.json",
        ".git"
    )

    foreach ($file in $requiredFiles) {
        if (-not (Test-Path $file)) {
            throw "Ficheiro/pasta obrigatório em falta: $file"
        }
    }

    if (-not (Test-Path $SshKey)) {
        throw "Chave SSH não encontrada: $SshKey"
    }
}

Run-Command "Estado Git local" {
    git status --short
}

Run-Command "Build frontend local" {
    npm.cmd run build
}

# =========================
# COMMIT E PUSH
# =========================

Run-Command "Adicionar alterações ao Git" {
    git add .
}

$HasChanges = git status --porcelain

if ($HasChanges) {
    Run-Command "Criar commit" {
        git commit -m "$Message"
    }

    Run-Command "Enviar para GitHub" {
        git push origin $Branch
    }
}
else {
    Write-Host ""
    Write-Host "==> Sem alterações para commit. Vou continuar com deploy da branch '$Branch'." -ForegroundColor Yellow
}

# =========================
# FULL DEPLOY REMOTO NA VM
# =========================

$RemoteCommand = @"
set -e

echo '==> Entrar na aplicação'
cd $VmProjectPath

echo '==> Estado Git antes do pull'
git status --short || true

echo '==> Limpar alterações locais de permissões em ficheiros tracked'
git restore --worktree --staged \
    bootstrap/cache/.gitignore \
    storage/app/.gitignore \
    storage/app/private/.gitignore \
    storage/app/public/.gitignore \
    storage/framework/.gitignore \
    storage/framework/cache/.gitignore \
    storage/framework/cache/data/.gitignore \
    storage/framework/sessions/.gitignore \
    storage/framework/testing/.gitignore \
    storage/framework/views/.gitignore \
    storage/logs/.gitignore || true

echo '==> Atualizar código'
git pull origin $Branch

echo '==> Instalar dependências PHP de produção'
composer install --no-dev --optimize-autoloader

echo '==> Otimizar autoload'
composer dump-autoload --optimize

echo '==> Instalar dependências Node na VM'
npm ci

echo '==> Build frontend na VM'
npm run build

echo '==> Executar migrations'
php artisan migrate --force

echo '==> Permissões'
sudo chown -R ubuntu:www-data $VmProjectPath
sudo find storage bootstrap/cache -type d -exec chmod 775 {} +
sudo find storage bootstrap/cache -type f -exec chmod 664 {} +

echo '==> Limpar caches Laravel'
php artisan optimize:clear

echo '==> Recriar caches Laravel'
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo '==> Recarregar Nginx'
sudo systemctl reload nginx

echo '==> Estado Nginx'
sudo systemctl is-active nginx

echo '==> Full deploy remoto concluído'
"@

$RemoteCommand = Convert-ToUnixLineEndings $RemoteCommand

Run-Command "Executar full deploy na VM" {
    $RemoteCommand | ssh -i $SshKey "$VmUser@$VmHost" "bash -s"
}

Write-Host ""
Write-Host "Full deploy terminado com sucesso." -ForegroundColor Green
Write-Host "URL: http://$VmHost" -ForegroundColor Green