# ERP_OS — Documentação Viva do Estado da Programação

**Última actualização:** 2026-06-17  
**Repositório:** `Bzuzinho/ERP_OS`  
**Branch analisada:** `main`  
**Objectivo:** manter um ponto único, claro e versionado sobre o estado real da programação do ERP_OS.

---

## 1. Enquadramento

O ERP_OS é uma aplicação de gestão operacional, com arquitectura orientada a organizações, permissões, workflows e módulos administrativos, de portal e mobile.

A aplicação está estruturada para servir entidades com operação interna complexa, como juntas de freguesia, associações, serviços municipais, organizações com espaços, equipas, tarefas, tickets, documentos, inventário, reservas e operações recorrentes.

Este documento deve ser actualizado sempre que existir uma nova sprint, refactorização relevante, alteração de domínio ou entrada de funcionalidades em produção.

---

## 2. Stack técnico identificado

### Backend

- PHP `^8.3`.
- Laravel Framework `^13.0` no `composer.json`.
- Inertia Laravel `^2.0`.
- Laravel Sanctum `^4.0`.
- Spatie Laravel Permission `^7.4`.
- Ziggy `^2.0`.
- PHPUnit `^12.5.12`.

### Frontend

- React `^18.2.0`.
- TypeScript `^5.0.2`.
- Inertia React `^2.0.0`.
- Tailwind CSS.
- Vite.
- Axios.
- Headless UI.

### Nota técnica importante

Existe divergência entre documentação interna anterior e dependências actuais:

- `PROJECT_STRUCTURE_MAP.md` refere Laravel 11.
- `composer.json` aponta para Laravel Framework `^13.0`.

**Estado:** requer normalização documental.  
**Acção recomendada:** confirmar versão efectiva em execução e actualizar toda a documentação técnica.

---

## 3. Estrutura macro da aplicação

A aplicação está dividida em três áreas principais:

| Área | Prefixo | Objectivo | Estado |
|---|---|---|---|
| Admin | `/admin` | Gestão interna da organização | Implementado |
| Portal | `/portal` | Área autenticada para utilizadores externos/cidadãos/requerentes | Implementado |
| Mobile | `/mobile` | Interface operacional para equipas em campo | Implementado |

A rota raiz `/` e a rota `/dashboard` redireccionam o utilizador conforme permissões:

- Utilizador com `admin.access` segue para `admin.dashboard`.
- Restantes utilizadores autenticados seguem para `portal.dashboard`.

---

## 4. Estado global por domínio

| Domínio | Estado | Observações |
|---|---:|---|
| Autenticação | Implementado | Login, registo, recuperação de password, verificação de email e perfil. |
| Multi-organização | Implementado | Modelo `Organization` e isolamento por organização previsto. |
| Permissões/Roles | Implementado | Baseado em Spatie Laravel Permission. |
| Admin Dashboard | Implementado | Dashboard interno com KPIs agregados. |
| Portal Dashboard | Implementado | Dashboard para utilizadores autenticados fora do Admin. |
| Contactos | Implementado | CRUD administrativo. |
| Tickets | Implementado avançado | CRUD, estados, atribuição, validação, geração de tarefas, comentários e anexos. |
| Tarefas | Implementado avançado | CRUD, estados, conclusão, validação, reabertura, checklists, anexos e mobile. |
| Eventos | Implementado | CRUD Admin, participantes e consulta Portal. |
| Espaços | Implementado avançado | CRUD, estado, manutenção, limpeza, reservas e pedidos associados. |
| Reservas de Espaços | Implementado avançado | Aprovação, rejeição, conclusão, cancelamento e portal. |
| Inventário | Implementado avançado | Categorias, localizações, itens, movimentos, empréstimos, reposição e quebras. |
| Pedidos de Recursos | Implementado | Fluxo de pedido, aprovação, preparação, entrega e devolução. |
| Documentos | Implementado avançado | Documentos, tipos, versões, regras de acesso e downloads. |
| Atas | Implementado | CRUD e aprovação de atas. |
| Recursos Humanos | Implementado | Departamentos, colaboradores, equipas, presenças, ausências e licenças. |
| Planeamento Operacional | Implementado avançado | Planos, tarefas, participantes, recursos, aprovação, conclusão e recorrência. |
| Operações Recorrentes | Implementado | Pausa, retoma, cancelamento, runs e execução. |
| Notificações | Implementado | Admin e Portal, marcar como lida e marcar tudo como lido. |
| Relatórios | Implementado | Relatórios por domínio e exportação. |
| Mobile Operacional | Implementado | Hoje, tarefas, calendário, comunicações, anexos e observações. |
| Testes | Implementado parcial/boa cobertura | Existem testes por domínio e sprint. Requer execução contínua e relatório actualizado. |

---

## 5. Rotas principais

### 5.1 Rotas base

| Método | Rota | Descrição |
|---|---|---|
| GET | `/` | Redirecciona para login, Admin ou Portal. |
| GET | `/dashboard` | Redirecciona para dashboard adequado ao perfil. |
| GET | `/profile` | Editar perfil. |
| PATCH | `/profile` | Actualizar perfil. |
| DELETE | `/profile` | Eliminar conta/perfil. |

---

## 6. Área Admin

Prefixo: `/admin`  
Middleware: `auth`, `permission:admin.access`

### 6.1 Dashboard e navegação

| Método | Rota | Nome | Estado |
|---|---|---|---|
| GET | `/admin` | `admin.dashboard` | Implementado |
| GET | `/admin/more` | `admin.more.index` | Implementado |

### 6.2 Contactos

| Recurso | Controller | Estado |
|---|---|---|
| `contacts` | `AdminContactController` | CRUD completo |

### 6.3 Tickets

| Funcionalidade | Rota/Acção | Estado |
|---|---|---|
| CRUD Tickets | `Route::resource('tickets')` | Implementado |
| Alterar estado | `PATCH /admin/tickets/{ticket}/status` | Implementado |
| Atribuir ticket | `PATCH /admin/tickets/{ticket}/assign` | Implementado |
| Submeter para validação | `POST /admin/tickets/{ticket}/submit-validation` | Implementado |
| Validar ticket | `POST /admin/tickets/{ticket}/validate` | Implementado |
| Cancelar ticket | `POST /admin/tickets/{ticket}/cancel` | Implementado |
| Gerar tarefas | `POST /admin/tickets/{ticket}/generate-tasks` | Implementado |
| Comentários | `POST /admin/tickets/{ticket}/comments` | Implementado |
| Anexos | `POST /admin/tickets/{ticket}/attachments` | Implementado |
| Download anexos | `GET /admin/attachments/{attachment}/download` | Implementado |

### 6.4 Tarefas

| Funcionalidade | Rota/Acção | Estado |
|---|---|---|
| CRUD Tarefas | `Route::resource('tasks')` | Implementado |
| Alterar estado | `PATCH /admin/tasks/{task}/status` | Implementado |
| Concluir tarefa | `POST /admin/tasks/{task}/complete` | Implementado |
| Submeter para validação | `POST /admin/tasks/{task}/submit-validation` | Implementado |
| Validar tarefa | `POST /admin/tasks/{task}/validate` | Implementado |
| Reabrir tarefa | `POST /admin/tasks/{task}/reopen` | Implementado |
| Criar checklist | `POST /admin/tasks/{task}/checklists` | Implementado |
| Actualizar checklist | `PATCH /admin/tasks/{task}/checklists/{checklist}` | Implementado |
| Apagar checklist | `DELETE /admin/tasks/{task}/checklists/{checklist}` | Implementado |
| Itens de checklist | criar/actualizar/apagar | Implementado |

### 6.5 Eventos

| Funcionalidade | Rota/Acção | Estado |
|---|---|---|
| CRUD Eventos | `Route::resource('events')` | Implementado |
| Alterar estado | `PATCH /admin/events/{event}/status` | Implementado |
| Adicionar participante | `POST /admin/events/{event}/participants` | Implementado |
| Remover participante | `DELETE /admin/events/{event}/participants/{participant}` | Implementado |

### 6.6 Documentos e atas

| Funcionalidade | Rota/Acção | Estado |
|---|---|---|
| Tipos de documento | `Route::resource('document-types')` | Implementado |
| Documentos | `Route::resource('documents')` | Implementado |
| Download documento | `GET /admin/documents/{document}/download` | Implementado |
| Versões | `POST /admin/documents/{document}/versions` | Implementado |
| Regras de acesso | criar/apagar | Implementado |
| Atas | `Route::resource('meeting-minutes')` | Implementado |
| Aprovar ata | `POST /admin/meeting-minutes/{meetingMinute}/approve` | Implementado |

### 6.7 Espaços, reservas, manutenção e limpeza

| Funcionalidade | Rota/Acção | Estado |
|---|---|---|
| Espaços | `Route::resource('spaces')` | Implementado |
| Alterar estado do espaço | `PATCH /admin/spaces/{space}/status` | Implementado |
| Criar ticket de manutenção | `POST /admin/spaces/{space}/maintenance-ticket` | Implementado |
| Reservas | `Route::resource('space-reservations')` | Implementado |
| Aprovar reserva | `POST /admin/space-reservations/{spaceReservation}/approve` | Implementado |
| Rejeitar reserva | `POST /admin/space-reservations/{spaceReservation}/reject` | Implementado |
| Concluir reserva | `POST /admin/space-reservations/{spaceReservation}/complete` | Implementado |
| Cancelar reserva | `POST /admin/space-reservations/{spaceReservation}/cancel` | Implementado |
| Manutenção | `Route::resource('space-maintenance')` | Implementado |
| Estado de manutenção | `PATCH /admin/space-maintenance/{spaceMaintenance}/status` | Implementado |
| Limpeza | `Route::resource('space-cleaning')` | Implementado |
| Concluir limpeza | `POST /admin/space-cleaning/{spaceCleaning}/complete` | Implementado |

### 6.8 Pedidos de recursos

| Funcionalidade | Rota/Acção | Estado |
|---|---|---|
| Listar/criar/ver pedidos | `resource-requests` | Implementado |
| Aprovar | `POST /admin/resource-requests/{resourceRequest}/approve` | Implementado |
| Rejeitar | `POST /admin/resource-requests/{resourceRequest}/reject` | Implementado |
| Preparar | `POST /admin/resource-requests/{resourceRequest}/prepare` | Implementado |
| Entregar | `POST /admin/resource-requests/{resourceRequest}/deliver` | Implementado |
| Devolver | `POST /admin/resource-requests/{resourceRequest}/return` | Implementado |

Os pedidos de recursos podem ser associados a:

- Reservas de espaço.
- Eventos.
- Tarefas.
- Tickets.
- Planos operacionais.

### 6.9 Inventário

| Módulo | Estado |
|---|---|
| Categorias de inventário | CRUD sem detalhe individual |
| Localizações de inventário | CRUD sem detalhe individual |
| Itens de inventário | CRUD completo |
| Estado de item | Implementado |
| Movimentos | Listar, criar, guardar e ver detalhe |
| Empréstimos | Listar, criar, guardar, ver detalhe e devolver |
| Pedidos de reposição | Listar, criar, guardar, ver detalhe, aprovar, rejeitar e completar |
| Quebras/Danos | Listar, criar, guardar, ver detalhe e resolver |

### 6.10 Relatórios

| Relatório | Estado |
|---|---|
| Geral | Implementado |
| Tickets | Implementado |
| Tarefas | Implementado |
| Eventos | Implementado |
| Espaços | Implementado |
| Inventário | Implementado |
| RH | Implementado |
| Planeamento | Implementado |
| Documentos | Implementado |
| Exportação | Implementado |

### 6.11 Recursos Humanos

Prefixo interno: `/admin/hr/...`

| Funcionalidade | Estado |
|---|---|
| Departamentos | CRUD completo |
| Colaboradores | CRUD completo |
| Estado do colaborador | Implementado |
| Equipas | CRUD completo |
| Adicionar membro a equipa | Implementado |
| Remover membro de equipa | Implementado |
| Presenças | CRUD completo |
| Validar presença | Implementado |
| Tipos de ausência | CRUD completo |
| Pedidos de ausência/licença | CRUD completo |
| Aprovar pedido | Implementado |
| Rejeitar pedido | Implementado |
| Cancelar pedido | Implementado |

### 6.12 Planeamento operacional

Prefixo interno: `/admin/operational-plans` e `/admin/recurring-operations`

| Funcionalidade | Estado |
|---|---|
| Planos operacionais | CRUD completo |
| Alterar estado | Implementado |
| Aprovar plano | Implementado |
| Cancelar plano | Implementado |
| Concluir plano | Implementado |
| Associar tarefas ao plano | Implementado |
| Remover tarefas do plano | Implementado |
| Gerar tarefas do plano | Implementado |
| Participantes do plano | Implementado |
| Recursos do plano | Implementado |
| Operações recorrentes | CRUD completo |
| Pausar recorrência | Implementado |
| Retomar recorrência | Implementado |
| Cancelar recorrência | Implementado |
| Criar execução/run | Implementado |
| Executar run | Implementado |

### 6.13 Configurações

Prefixo: `/admin/settings`

| Funcionalidade | Estado |
|---|---|
| Página geral de settings | Implementado |
| Utilizadores | CRUD completo |
| Actualizar roles do utilizador | Implementado |
| Activar utilizador | Implementado |
| Desactivar utilizador | Implementado |
| Reset de password | Implementado |
| Avatar de utilizador | Implementado |
| Roles | Listar, ver, editar e actualizar |
| Organização | Editar dados e actualizar logótipo |
| Áreas de serviço | CRUD completo |
| Associar utilizadores a áreas de serviço | Implementado |

---

## 7. Área Portal

Prefixo: `/portal`  
Middleware: `auth`

| Funcionalidade | Estado |
|---|---|
| Dashboard Portal | Implementado |
| Menu Mais | Implementado |
| Tickets | Listar, criar, guardar e ver detalhe |
| Comentários em tickets | Implementado |
| Anexos em tickets | Implementado |
| Download de anexos | Implementado |
| Notificações | Listar, marcar lida e marcar todas lidas |
| Eventos | Listar e ver detalhe |
| Documentos | Listar, ver detalhe e download |
| Atas | Listar e ver detalhe |
| Espaços | Listar e ver detalhe |
| Reservas de espaço | Listar, criar, guardar, ver detalhe e cancelar |
| Planos operacionais | Listar e ver detalhe |

---

## 8. Área Mobile

Prefixo: `/mobile`  
Middleware: `auth`

| Rota | Funcionalidade | Estado |
|---|---|---|
| `/mobile` | Redirecciona para Hoje | Implementado |
| `/mobile/hoje` | Vista Hoje | Implementado |
| `/mobile/tarefas` | Minhas tarefas | Implementado |
| `/mobile/tarefas/{task}` | Detalhe de tarefa | Implementado |
| `/mobile/tarefas/{task}/iniciar` | Iniciar tarefa | Implementado |
| `/mobile/tarefas/{task}/concluir` | Concluir tarefa | Implementado |
| `/mobile/tarefas/{task}/enviar-validacao` | Enviar para validação | Implementado |
| `/mobile/tarefas/{task}/reabrir` | Reabrir tarefa | Implementado |
| `/mobile/tarefas/{task}/checklists/{checklist}` | Actualizar checklist | Implementado |
| `/mobile/tarefas/{task}/anexos` | Upload de anexos | Implementado |
| `/mobile/tarefas/{task}/anexos/{attachment}/download` | Download de anexos | Implementado |
| `/mobile/tarefas/{task}/observacoes` | Adicionar observações | Implementado |
| `/mobile/agenda` | Agenda | Implementado |
| `/mobile/comunicacoes` | Comunicações | Implementado |
| `/mobile/mais` | Menu Mais | Implementado |

---

## 9. Modelos principais

### Identidade e estrutura

- `User`
- `Organization`
- `Contact`
- `ContactAddress`
- `Comment`
- `Attachment`
- `ActivityLog`
- `ServiceArea`

### Ticketing

- `Ticket`
- `TicketStatusHistory`

### Tarefas

- `Task`
- `TaskChecklist`
- `TaskChecklistItem`

### Eventos

- `Event`
- `EventParticipant`

### Espaços

- `Space`
- `SpaceReservation`
- `SpaceReservationApproval`
- `SpaceMaintenanceRecord`
- `SpaceCleaningRecord`

### Inventário

- `InventoryCategory`
- `InventoryLocation`
- `InventoryItem`
- `InventoryMovement`
- `InventoryLoan`
- `InventoryRestockRequest`
- `InventoryBreakage`

### Documentos

- `Document`
- `DocumentType`
- `DocumentVersion`
- `DocumentAccessRule`
- `MeetingMinute`

### Recursos Humanos

- `Employee`
- `Department`
- `Team`
- `TeamMember`
- `AbsenceType`
- `AttendanceRecord`
- `EmployeeSchedule`
- `EmployeeEventAssignment`
- `EmployeeTaskAssignment`
- `LeaveRequest`

### Planeamento

- `OperationalPlan`
- `OperationalPlanTask`
- `OperationalPlanParticipant`
- `OperationalPlanResource`
- `RecurringOperation`
- `RecurringOperationRun`

### Notificações

- `Notification`
- `NotificationRecipient`

---

## 10. Services e Actions

A arquitectura usa uma separação relevante entre:

- Controllers: entrada HTTP e renderização Inertia.
- Models: entidades de domínio.
- Policies: autorização fina por entidade.
- Actions: operações específicas de negócio.
- Services: agregação, métricas, notificações, relatórios, storage e lógica de domínio.

### Exemplos de Actions implementadas

- Criação de tickets.
- Actualização de estado de tickets.
- Atribuição de tickets.
- Criação e conclusão de tarefas.
- Aprovação/rejeição/cancelamento de reservas.
- Criação de eventos.
- Movimentos de inventário.
- Empréstimos e devoluções.
- Reposição de stock.
- Gestão de documentos e versões.
- Aprovação de atas.
- Criação e aprovação de planos operacionais.
- Geração de tarefas a partir de planos.
- Gestão de operações recorrentes.
- Criação de colaboradores, departamentos, equipas e ausências.
- Gestão de utilizadores, roles, passwords e organização.

### Exemplos de Services implementados

- Dashboards e KPIs.
- Relatórios por domínio.
- Exportação CSV.
- Gestão documental.
- Gestão de stock.
- Notificações.
- Resolução de destinatários.
- Planeamento operacional.
- Recorrências.
- Reservas de espaços.
- Disponibilidade de espaços.
- Limpeza de espaços.
- Geração de referências de tickets.
- Activity logging.

---

## 11. Migrações e base de dados

O projecto tem migrações para:

- Utilizadores.
- Cache.
- Jobs.
- Permissões.
- Organizações.
- Perfil de utilizador.
- CRM/contactos/tickets.
- Tarefas, eventos e checklists.
- Espaços, reservas, aprovações, manutenção e limpeza.
- Inventário.
- Documentos e atas.
- RH.
- Planeamento operacional.
- Operações recorrentes.
- Áreas de serviço.
- Notificações.

**Estado:** estrutura de dados ampla e consistente com os módulos funcionais.

---

## 12. Testes

Existem testes organizados por autenticação, perfil, domínios e sprints.

Áreas cobertas:

- Autenticação.
- Perfil.
- Eventos.
- Tarefas.
- Portal.
- Espaços.
- Inventário.
- RH.
- Planeamento.
- Dashboard e relatórios.
- Segurança.
- Tickets.
- Contactos.
- Comentários e anexos.
- Notificações.
- Navegação.
- Estabilização Portal.
- UX demo.
- Isolamento por organização.
- Gestão de utilizadores.

**Estado:** existe cobertura relevante.  
**Acção recomendada:** executar testes após cada sprint e acrescentar neste documento o resultado da última execução.

Formato sugerido:

```md
## Última execução de testes

Data: AAAA-MM-DD
Comando: php artisan test
Resultado: X passed, Y failed, Z skipped
Observações:
- ...
```

---

## 13. Funcionalidades que parecem prontas para demonstração

Estas áreas parecem ter maturidade suficiente para demo funcional:

1. Login e redireccionamento por perfil.
2. Dashboard Admin.
3. Dashboard Portal.
4. Criação e gestão de tickets.
5. Comentários e anexos em tickets.
6. Geração de tarefas a partir de ticket.
7. Gestão de tarefas com checklists.
8. Mobile operacional de tarefas.
9. Gestão de espaços.
10. Reservas de espaços com aprovação.
11. Gestão de inventário.
12. Pedidos de recursos.
13. Gestão documental.
14. Gestão de atas.
15. Planeamento operacional.
16. Operações recorrentes.
17. Notificações.
18. Relatórios.
19. Gestão de utilizadores e permissões.
20. RH básico.

---

## 14. Pontos de atenção técnicos

### 14.1 Divergência de versão Laravel

A documentação anterior refere Laravel 11, mas o `composer.json` aponta para Laravel `^13.0`.

**Prioridade:** Alta.  
**Impacto:** documentação, instalação, compatibilidade, deploy, onboarding técnico.

### 14.2 Documentação desactualizada face às rotas actuais

O ficheiro `PROJECT_STRUCTURE_MAP.md` é útil, mas precisa de ser actualizado para incluir:

- Área Mobile.
- Validação de tickets.
- Validação de tarefas.
- Reabertura de tarefas.
- Geração de tarefas por ticket.
- Pedidos de recursos.
- Ligações entre pedidos de recursos e tickets/eventos/tarefas/reservas/planos.

### 14.3 Necessidade de matriz de permissões

O sistema usa Spatie Permissions e policies, mas falta uma matriz funcional de permissões por perfil.

Sugestão:

| Perfil | Admin | Portal | Mobile | Tickets | Tarefas | Inventário | RH | Settings |
|---|---|---|---|---|---|---|---|---|
| Super Admin | Sim | Sim | Sim | Total | Total | Total | Total | Total |
| Admin Operacional | Sim | Sim | Sim | Total | Total | Parcial | Parcial | Não |
| Técnico Campo | Não | Sim | Sim | Ver atribuídos | Executar | Não | Não | Não |
| Utilizador Portal | Não | Sim | Não | Criar/ver próprios | Não | Não | Não | Não |

### 14.4 Necessidade de mapa de estados por domínio

Deve existir uma página própria com workflows.

Exemplos:

- Ticket: novo → em análise → encaminhado/em execução → resolvido → fechado.
- Tarefa: pending → in_progress → waiting → done/cancelled.
- Reserva: requested → approved/rejected → completed/cancelled.
- Plano: draft → pending_approval → approved → scheduled/in_progress → completed/cancelled.

### 14.5 Necessidade de validação de UX real

As rotas e páginas existem, mas é necessário confirmar em browser:

- Se todas as páginas renderizam.
- Se os botões aparecem conforme permissões.
- Se os formulários estão completos.
- Se os fluxos têm mensagens de erro/sucesso.
- Se o mobile está utilizável em ecrã pequeno.

---

## 15. Próximos passos recomendados

### Prioridade 1 — Estabilização documental

- Confirmar versão Laravel real.
- Actualizar `PROJECT_STRUCTURE_MAP.md`.
- Manter este documento como fonte principal de estado.
- Criar matriz de permissões.
- Criar mapa de workflows por domínio.

### Prioridade 2 — Validação técnica

- Executar `composer install`.
- Executar `npm install`.
- Executar `php artisan migrate:fresh --seed`.
- Executar `php artisan test`.
- Executar `npm run build`.
- Registar resultados neste documento.

### Prioridade 3 — Validação funcional

- Fazer smoke test de todas as rotas principais.
- Testar login Admin, Portal e Mobile.
- Testar criação de ticket completo com anexo e comentário.
- Testar geração de tarefas.
- Testar execução de tarefa em mobile.
- Testar reserva de espaço e aprovação.
- Testar pedido de recurso associado a reserva/tarefa/ticket.
- Testar relatório e exportação.

### Prioridade 4 — Preparação para produto

- Definir perfis comerciais do ERP.
- Separar módulos vendáveis.
- Preparar seeds demo.
- Criar documentação para IA/codex.
- Criar documentação para onboarding de programador.
- Criar checklist de deploy.

---

## 16. Checklist viva por sprint

Usar este bloco em cada nova sprint.

```md
## Sprint XX — Nome da Sprint

Data início:
Data fim:
Branch:
Commit principal:

### Objectivo
- ...

### Implementado
- ...

### Alterações técnicas
- Models:
- Controllers:
- Routes:
- Pages:
- Services:
- Actions:
- Migrations:
- Tests:

### Testes executados
- Comando:
- Resultado:

### Pendências
- ...

### Riscos
- ...

### Decisão
- Aprovado / Parcial / Requer correcção
```

---

## 17. Estado executivo actual

O ERP_OS encontra-se num estado avançado de programação, com os principais domínios estruturais já implementados: autenticação, permissões, tickets, tarefas, eventos, espaços, reservas, inventário, documentos, RH, planeamento, notificações, relatórios, Portal e Mobile.

A aplicação já tem profundidade operacional suficiente para ser tratada como produto em construção e não apenas como protótipo.

O próximo esforço deve concentrar-se em:

1. Validar tecnicamente tudo o que existe.
2. Corrigir divergências documentais.
3. Testar fluxos reais de ponta a ponta.
4. Fechar matriz de permissões.
5. Criar dados demo.
6. Preparar documentação para desenvolvimento assistido por IA.

---

## 18. Registo de actualizações

| Data | Autor | Alteração |
|---|---|---|
| 2026-06-17 | ChatGPT | Criação inicial da documentação viva do estado da programação. |
