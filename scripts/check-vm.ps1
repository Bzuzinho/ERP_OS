[CmdletBinding()]
param()

$ErrorActionPreference = "Stop"

$VmUser = "ubuntu"
$VmHost = "89.168.82.41"
$VmProjectPath = "/var/www/juntaos"
$SshKey = "C:\projetos\keys\ssh-key-2026-05-04.key"

function Write-Step {
    param([string]$Text)

    Write-Host ""
    Write-Host "==> $Text" -ForegroundColor Cyan
}

function Convert-ToUnixLineEndings {
    param([string]$Text)
    return ($Text -replace "`r`n", "`n" -replace "`r", "`n")
}

if (-not (Test-Path -LiteralPath $SshKey)) {
    throw "Chave SSH não encontrada em '$SshKey'."
}

if (-not (Get-Command ssh -ErrorAction SilentlyContinue)) {
    throw "Comando obrigatório não encontrado: ssh"
}

$remoteScript = @"
if [ -d '$VmProjectPath' ]; then
  cd '$VmProjectPath'
  echo "--- Directoria: \$(pwd)"
  echo "--- Git status:"
  git status --short
else
  echo "AVISO: $VmProjectPath nao existe na VM"
fi
echo "--- PHP:"
php -v
echo "--- Composer:"
composer -V
echo "--- Node:"
node -v || echo "Node nao instalado"
echo "--- npm:"
npm -v || echo "npm nao instalado"
echo "--- Nginx:"
sudo systemctl is-active nginx || true
echo "--- Log Laravel (ultimas 20 linhas):"
if [ -f '$VmProjectPath/storage/logs/laravel.log' ]; then
  tail -n 20 '$VmProjectPath/storage/logs/laravel.log'
else
  echo "Log nao encontrado: $VmProjectPath/storage/logs/laravel.log"
fi
"@

$remoteScript = Convert-ToUnixLineEndings $remoteScript

Write-Step "Testar ligação SSH e estado da VM"
$remoteScript | & ssh -i $SshKey "$VmUser@$VmHost" "bash -s"

Write-Host ""
Write-Host "Verificação da VM concluída." -ForegroundColor Green