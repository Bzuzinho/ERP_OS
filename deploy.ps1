[CmdletBinding()]
param(
    [Parameter(Position = 0)]
    [string]$Message
)

$ErrorActionPreference = "Stop"

$deployScript = Join-Path $PSScriptRoot "scripts\deploy-vm-full.ps1"

if (-not (Test-Path -LiteralPath $deployScript)) {
    throw "Script de deploy não encontrado em '$deployScript'."
}

if ([string]::IsNullOrWhiteSpace($Message)) {
    $Message = "deploy: auto " + (Get-Date -Format "yyyy-MM-dd HH:mm:ss")
}

Write-Host "Mensagem de commit: $Message" -ForegroundColor Cyan

& $deployScript -Message $Message

if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}
