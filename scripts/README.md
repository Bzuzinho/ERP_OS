# Scripts de deploy para a VM Oracle

## Como executar

No PowerShell do Windows dentro do projecto:

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
.\scripts\deploy-vm.ps1 -Message "Mensagem do commit"
```

Para a variante completa:

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
.\scripts\deploy-vm-full.ps1 -Message "Mensagem do commit"
```

Para verificar a VM sem fazer deploy:

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
.\scripts\check-vm.ps1
```

## Quando usar deploy-vm.ps1

Use [deploy-vm.ps1](./deploy-vm.ps1) como opção principal quando quiser um deploy leve e conservador para a VM. Esta versão faz a build do frontend localmente, trata do Git no PC e faz apenas os passos essenciais na VM.

Neste repositório, `public/build` está ignorado no Git. Por isso, esta variante mostra um aviso claro a explicar que os assets compilados não seguem para produção por Git e que pode ser necessário reconstruir o frontend na VM ou rever a estratégia de entrega dos ficheiros gerados pelo Vite.

## Quando usar deploy-vm-full.ps1

Use [deploy-vm-full.ps1](./deploy-vm-full.ps1) apenas quando for mesmo necessário reconstruir o frontend na VM. Esta variante adiciona `npm ci` e `npm run build` no servidor remoto, o que consome mais CPU, disco e RAM.

É a escolha indicada quando o frontend não actualiza porque `public/build` não foi enviado para a VM, ou quando quiser garantir que os assets de produção foram gerados directamente no servidor.

## O que os scripts fazem

Os scripts de deploy:

- validam se está no repositório certo e se os ficheiros obrigatórios existem;
- mostram o estado do Git antes de qualquer alteração;
- correm `npm.cmd run build` localmente antes do commit;
- fazem `git add .`, tentam criar commit com a mensagem fornecida e fazem `git push origin main` quando existem alterações;
- se não houver alterações para commit, mostram `Sem alterações para commit` e continuam com o deploy da versão já existente na branch `main`;
- ligam por SSH à VM e executam `git pull`, Composer, migrations, limpeza/cache do Laravel, permissões e `reload` do Nginx;
- mostram no fim a URL `http://89.168.82.41`.

O script [check-vm.ps1](./check-vm.ps1) apenas testa o acesso à VM e recolhe informação útil: directoria actual, `git status`, versões de PHP/Composer/Node/npm, estado do Nginx e últimas linhas do log Laravel.

## Cuidados

- Não guardar passwords no script.
- Não colocar o `.env` no Git.
- Não correr `migrate:fresh` em produção.
- A VM tem pouca RAM, por isso a variante `deploy-vm.ps1` deve ser a opção por defeito.
- Se o frontend não actualizar, pode ser necessário usar `deploy-vm-full.ps1` ou rever a estratégia do `public/build`.