[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string]$Message
)

$ErrorActionPreference = "Stop"

$ProjectPath = "C:\projetos\ERP_OS\ERP_OS"
$Branch = "main"
$VmUser = "ubuntu"
$VmHost = "89.168.82.41"
$VmProjectPath = "/var/www/juntaos"
$SshKey = "C:\projetos\keys\ssh-key-2026-05-04.key"

function Write-Step {
    param([string]$Text)

    Write-Host ""
    Write-Host "==> $Text" -ForegroundColor Cyan
}

function Test-RequiredPath {
    param(
        [string]$Path,
        [string]$Description
    )

    if (-not (Test-Path -LiteralPath $Path)) {
        throw "Falta $Description em '$Path'."
    }
}

function Invoke-NativeCommand {
    param(
        [Parameter(Mandatory = $true)]
        [scriptblock]$Script,
        [Parameter(Mandatory = $true)]
        [string]$ErrorMessage
    )

    & $Script

    if ($LASTEXITCODE -ne 0) {
        throw $ErrorMessage
    }
}

function Convert-ToUnixLineEndings {
    param([string]$Text)
    return ($Text -replace "`r`n", "`n" -replace "`r", "`n")
}

Write-Step "Validar ferramentas locais"
foreach ($commandName in @("git", "ssh", "npm.cmd")) {
    if (-not (Get-Command $commandName -ErrorAction SilentlyContinue)) {
        throw "Comando obrigatório não encontrado: $commandName"
    }
}

Write-Step "Validar estrutura do projecto"
Test-RequiredPath -Path $ProjectPath -Description "a pasta do projecto"
Test-RequiredPath -Path (Join-Path $ProjectPath "composer.json") -Description "composer.json"
Test-RequiredPath -Path (Join-Path $ProjectPath "package.json") -Description "package.json"
Test-RequiredPath -Path (Join-Path $ProjectPath "artisan") -Description "artisan"
Test-RequiredPath -Path (Join-Path $ProjectPath ".git") -Description ".git"
Test-RequiredPath -Path $SshKey -Description "a chave SSH"

Set-Location -Path $ProjectPath

$resolvedProjectPath = (Resolve-Path -LiteralPath $ProjectPath).Path
$repoRoot = (& git rev-parse --show-toplevel).Trim()
if ($LASTEXITCODE -ne 0 -or -not $repoRoot) {
    throw "Não foi possível determinar a raiz do repositório Git."
}

if ($repoRoot -ne $resolvedProjectPath) {
    throw "Repositório inesperado. Esperado: '$resolvedProjectPath'. Actual: '$repoRoot'."
}

$currentBranch = (& git branch --show-current).Trim()
if ($LASTEXITCODE -ne 0 -or -not $currentBranch) {
    throw "Não foi possível determinar a branch actual."
}

if ($currentBranch -ne $Branch) {
    throw "Branch actual '$currentBranch' diferente da branch esperada '$Branch'."
}

Write-Step "Mostrar estado do Git"
Invoke-NativeCommand -Script { git status --short --branch } -ErrorMessage "Falha ao obter o estado do Git."

Write-Step "Verificar estratégia de build"
& git check-ignore public/build *> $null
$publicBuildIgnored = ($LASTEXITCODE -eq 0)

if ($publicBuildIgnored) {
    Write-Warning "public/build está ignorado; esta variante vai reconstruir o frontend directamente na VM."
}
else {
    Write-Host "public/build não está ignorado; esta variante ainda vai reconstruir o frontend na VM." -ForegroundColor Yellow
}

Write-Step "Correr build local"
Invoke-NativeCommand -Script { npm.cmd run build } -ErrorMessage "Falha na build local com npm.cmd run build."

Write-Step "Preparar alterações no Git"
Invoke-NativeCommand -Script { git add . } -ErrorMessage "Falha ao adicionar alterações ao Git."

& git diff --cached --quiet
$hasStagedChanges = ($LASTEXITCODE -ne 0)

if ($hasStagedChanges) {
    Write-Step "Criar commit"
    Invoke-NativeCommand -Script { git commit -m $Message } -ErrorMessage "Falha ao criar o commit."

    Write-Step "Fazer push para origin/$Branch"
    Invoke-NativeCommand -Script { git push origin $Branch } -ErrorMessage "Falha ao fazer push para origin/$Branch."
}
else {
    Write-Warning "Sem alterações para commit"
    Write-Host "A continuar com o deploy da versão já existente na branch $Branch." -ForegroundColor Yellow
}

$remoteScript = @"
set -e
cd '$VmProjectPath'
git pull origin $Branch
composer install --no-dev --optimize-autoloader
npm ci
npm run build
composer dump-autoload --optimize
php artisan migrate --force
sudo chown -R ubuntu:www-data '$VmProjectPath'
sudo chmod -R 775 storage bootstrap/cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl reload nginx
"@

Write-Step "Executar deploy completo na VM"
$remoteScript = Convert-ToUnixLineEndings $remoteScript
$remoteScript | & ssh -i $SshKey "$VmUser@$VmHost" "bash -s"

if ($LASTEXITCODE -ne 0) {
    throw "Falha durante o deploy remoto na VM."
}

Write-Host ""
Write-Host "Deploy terminado com sucesso." -ForegroundColor Green
Write-Host "URL: http://89.168.82.41" -ForegroundColor Green