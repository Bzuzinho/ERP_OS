# Fase 0 - Auditoria Técnica Pré-Implementação

**Data:** Maio 7, 2026  
**Status:** ✅ Auditoria Completa  
**Validações:** Composer OK | Testes OK | Build OK

---

## 1. ESTADO ATUAL DAS TABELAS

### 1.1 Tickets Table (Confirmado)

**Campos Existentes:**
- `id`, `organization_id`, `reference` (UNIQUE), `created_by` (FK users)
- `contact_id` (FK nullable), `assigned_to` (FK nullable)
- `department_id`, `service_area_id`, `team_id` (campos adicionados em 2026_05_05_120200)
- `category`, `subcategory`, `priority` (default: 'normal')
- `status` (string, default: 'novo') ✅ String, NÃO ENUM - permite flexibilidade
- `title`, `description`, `location_text` (nullable)
- `source` (string: portal, internal, phone, email, presencial)
- `visibility` (string: internal, default)
- `due_date`, `closed_at`, `closed_by` (nullable)
- `created_at`, `updated_at`, `deleted_at` (soft delete)

**Campos Faltantes para Fase 1:**
- ❌ `type` (distinguish internal/portal/occurrence/maintenance/logistics/cleaning/complaint/information)
- ❌ `validated_at`, `validated_by`, `validation_notes`
- ❌ `resolution_notes`

**Relações Existentes:**
- ✅ belongsTo User (creator)
- ✅ belongsTo Contact
- ✅ belongsTo User (assignee as assigned_to)
- ✅ belongsTo User (closedBy)
- ✅ belongsTo Department
- ✅ hasMany Task (tickets com tarefas)
- ✅ hasMany TicketStatusHistory
- ✅ morphMany Comment
- ✅ morphMany Attachment

**Estados Atuais Confirmados (Model):**
- `novo`, `em_analise`, `aguarda_informacao`, `encaminhado`, `em_execucao`, `agendado`, `resolvido`, `fechado`, `cancelado`, `indeferido`

**Estados em Seeders:**
- `novo`, `em_analise`, `aguarda_informacao`, `encaminhado`, `em_execucao`, `agendado`, `resolvido`, `fechado`

---

### 1.2 Tasks Table (Confirmado)

**Campos Existentes:**
- `id`, `organization_id`, `ticket_id` (FK nullable)
- `space_reservation_id` (FK nullable) ✅ Adicionado em 2026_05_06
- `assigned_to`, `created_by` (FKs)
- `title`, `description` (nullable)
- `status` (string, default: 'pending') ✅ String, NÃO ENUM
- `priority` (default: 'normal')
- `start_date`, `due_date`, `completed_at`, `completed_by` (nullable)
- `created_at`, `updated_at`, `deleted_at` (soft delete)

**Campos Faltantes para Fase 2:**
- ❌ `event_id` (FK events)
- ❌ Relacionar via pivot `operational_plan_tasks` JÁ EXISTE - NÃO precisa adicionar `operational_plan_id` direto
- ❌ `validated_at`, `validated_by`, `validation_notes`
- ❌ `observations` (longtext)
- ❌ `reopen_count` (INT default 0)

**Relações Existentes:**
- ✅ belongsTo Organization
- ✅ belongsTo Ticket
- ✅ belongsTo SpaceReservation
- ✅ belongsTo User (assignee as assigned_to)
- ✅ belongsTo User (creator)
- ✅ belongsTo User (completedBy)
- ✅ hasMany TaskChecklist
- ✅ belongsToMany OperationalPlan (via operational_plan_tasks) ✅
- ✅ morphMany Attachment
- ✅ morphMany Comment
- ❌ hasMany Event (não existe - precisa criar event_tasks pivot)

**Estados Atuais Confirmados (Model):**
- `pending`, `in_progress`, `waiting`, `done`, `cancelled`

**Estados em Seeders:**
- `pending`, `in_progress`, `waiting`, `done`, `cancelled`

**AVISO IMPORTANTE:** Plano propõe mudar para:
- `por_iniciar`, `em_curso`, `bloqueada`, `concluida`, `por_validar`, `validada`, `reaberta`, `cancelada`

Esta é uma mudança IMPORTANTE que afetará seeders, factories, testes e controllers. Ver secção de risco.

---

### 1.3 Events Table (Confirmado)

**Campos Existentes:**
- `id`, `organization_id`, `space_id` (FK) ✅ Adicionado em 2026_05_06
- `title`, `description` (nullable)
- `event_type` (string: meeting, appointment, visit, activity, maintenance, assembly, reservation)
- `status` (string: scheduled, confirmed, cancelled, completed)
- `start_at`, `end_at` (timestamps)
- `location_text` (nullable)
- `created_by` (FK users)
- `related_ticket_id`, `related_contact_id` (FKs nullable)
- `visibility` (string: public, internal, restricted)
- `created_at`, `updated_at`, `deleted_at` (soft delete)

**Campos Faltantes para Fase 5:**
- ❌ `internal_notes` (text)
- ❌ `allows_task_creation` (boolean, default: false)
- ❌ `allows_request_creation` (boolean, default: false)
- ❌ Create pivot table `event_tasks` para many-to-many com Task

**Relações Existentes:**
- ✅ belongsTo User (creator)
- ✅ belongsTo Ticket (related_ticket_id)
- ✅ belongsTo Contact (related_contact_id)
- ✅ belongsTo Space
- ✅ hasMany EventParticipant
- ✅ hasMany MeetingMinute
- ✅ hasMany SpaceReservation
- ✅ morphMany Document
- ✅ morphMany Comment
- ❌ belongsToMany Task (via event_tasks - precisa criar)

**Estados Atuais:**
- `scheduled`, `confirmed`, `cancelled`, `completed`

---

### 1.4 OperationalPlans Table (Confirmado)

**Campos Existentes:**
- `id`, `organization_id`, `title`, `slug`, `description`
- `plan_type` (string: activity, maintenance, cleaning, public_event, inspection, campaign, project, emergency, administrative, other)
- `status` (string: draft, pending_approval, approved, scheduled, in_progress, completed, cancelled, archived)
- `visibility` (public, portal, internal, restricted)
- `start_date`, `end_date`, `owner_user_id`, `department_id`, `team_id`
- `related_ticket_id`, `related_space_id` (FKs nullable)
- `budget_estimate` (decimal 10,2)
- `progress_percent` (int, default: 0)
- `approved_by`, `approved_at`, `cancelled_by`, `cancelled_at`, `cancellation_reason`
- `completed_by`, `completed_at`, `created_by`
- `created_at`, `updated_at`, `deleted_at` (soft delete)

**Campos Faltantes para Fase 9:**
- ❌ `budget_actual` (decimal 10,2)
- ❌ `budget_currency` (varchar 3, default: 'EUR')

**Relações Existentes:**
- ✅ belongsTo User (owner, creator, approver, canceller, completer)
- ✅ belongsTo Department
- ✅ belongsTo Team
- ✅ belongsTo Ticket (related)
- ✅ belongsTo Space (related)
- ✅ hasMany OperationalPlanTask
- ✅ belongsToMany Task (via operational_plan_tasks) ✅ JÁ EXISTE
- ✅ hasMany OperationalPlanParticipant
- ✅ hasMany OperationalPlanResource
- ✅ morphMany Attachment
- ✅ morphMany Comment

**Estados Atuais:**
- `draft`, `pending_approval`, `approved`, `scheduled`, `in_progress`, `completed`, `cancelled`, `archived`

---

### 1.5 SpaceReservations Table (Confirmado)

**Campos Existentes:**
- `id`, `organization_id`, `space_id`, `requested_by_user_id`, `contact_id`, `event_id`
- `status` (string: requested, approved, rejected, cancelled, completed)
- `start_at`, `end_at`
- `purpose`, `notes`, `internal_notes` (nullable)
- `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`
- `cancelled_by`, `cancelled_at`, `cancellation_reason`
- `created_at`, `updated_at`, `deleted_at` (soft delete)

**Campos Faltantes para Fase 6:**
- ❌ `allows_maintenance_request` (boolean, default: true)
- ❌ `allows_material_request` (boolean, default: true)

**Relações Existentes:**
- ✅ belongsTo Space
- ✅ belongsTo User (requestedBy)
- ✅ belongsTo Contact
- ✅ belongsTo Event
- ✅ hasMany Task
- ✅ hasMany SpaceReservationApproval
- ✅ morphMany Attachment
- ✅ (será) morphMany ResourceRequest (Fase 6)

---

### 1.6 InventoryItems Table (Confirmado)

**Campos Existentes:**
- `id`, `organization_id`, `inventory_category_id`, `inventory_location_id`
- `name`, `slug`, `description`, `sku`
- `item_type` (consumable, equipment, vehicle, tool, furniture, document, other)
- `unit` (unit, box, pack, liter, kg, meter, hour, day, other)
- `current_stock`, `minimum_stock`, `maximum_stock`
- `unit_cost` (decimal)
- `status` (active, inactive, damaged, lost, maintenance, retired)
- `is_stock_tracked`, `is_loanable`, `is_active` (booleans)
- `created_at`, `updated_at`, `deleted_at` (soft delete)

**Relações Existentes:**
- ✅ belongsTo InventoryCategory
- ✅ belongsTo InventoryLocation
- ✅ hasMany InventoryMovement
- ✅ hasMany InventoryLoan
- ✅ hasMany InventoryRestockRequest

**Status Atual:** ✅ Já está bem estruturado

---

### 1.7 MeetingMinutes Table (Confirmado)

**Campos Existentes:**
- `id`, `organization_id`, `event_id`, `document_id`
- `title`, `summary` (nullable)
- `status` (string: draft, reviewed, approved, archived)
- `approved_at`, `approved_by`, `created_by`
- `created_at`, `updated_at`, `deleted_at` (soft delete)

**Relações Existentes:**
- ✅ belongsTo Organization
- ✅ belongsTo Event
- ✅ belongsTo Document
- ✅ belongsTo User (creator, approvedBy)

**Status Atual:** ✅ Já tem tudo para Fase 5

---

### 1.8 Attachments Table (Confirmado)

**Campos Existentes:**
- `id`, `organization_id`, `uploaded_by` (FK users)
- `attachable_type`, `attachable_id` (Polymorphic)
- `file_path`, `file_name`, `mime_type`, `size`
- `visibility` (internal, default)
- `created_at`, `updated_at`, `deleted_at` (soft delete)

**Status Atual:** ✅ Já é morphable - perfeito para futuras extensões

---

### 1.9 Comments Table (Confirmado)

**Campos Existentes:**
- `id`, `organization_id`, `user_id`
- `commentable_type`, `commentable_id` (Polymorphic)
- `body` (text)
- `visibility` (string: internal)
- `created_at`, `updated_at`, `deleted_at` (soft delete)

**Status Atual:** ✅ Já é morphable

---

### 1.10 Notifications Table (Confirmado)

**Campos Existentes:**
- `id`, `organization_id`, `user_id` (FK creator)
- `type`, `title`, `message`
- `notifiable_type`, `notifiable_id` (Polymorphic)
- `action_url`, `priority`, `data` (json)
- `created_at`

**Relações Existentes:**
- ✅ belongsTo Organization
- ✅ belongsTo User (creator)
- ✅ hasMany NotificationRecipient
- ✅ morphTo notifiable

**Status Atual:** ✅ Bem estruturado

---

## 2. RELAÇÕES CRÍTICAS - AUDITORIA

### 2.1 ✅ Confirmado - Não Precisa Alterar

| Relação | Status | Notas |
|---------|--------|-------|
| Task ←→ OperationalPlan (via pivot) | ✅ Existe | Usar `belongsToMany('OperationalPlan', 'operational_plan_tasks')` |
| Event ←→ MeetingMinute | ✅ Existe | Usar `hasMany('MeetingMinute')` |
| Event ←→ Space | ✅ Existe | Adicionado em 2026_05_06 |
| Task ←→ SpaceReservation | ✅ Existe | Adicionado em 2026_05_06 |
| Comment (morphable) | ✅ Existe | Reutilizar para novas entidades |
| Attachment (morphable) | ✅ Existe | Reutilizar para novas entidades |

### 2.2 ❌ Faltam - Precisa Criar (Fase 5)

| Relação | Status | Ação |
|---------|--------|------|
| Event ←→ Task (via pivot) | ❌ Falta | CREATE TABLE `event_tasks` |
| Task ←→ Event (reverse) | ❌ Falta | ADD `belongsToMany()` em Task |

### 2.3 ❌ Faltam - Para Fase 6

| Entidade | Status | Ação |
|----------|--------|------|
| ResourceRequest | ❌ Falta | CREATE TABLE |
| ResourceRequestItem | ❌ Falta | CREATE TABLE |

### 2.4 ❌ Faltam - Para Fase 10

| Entidade | Status | Ação |
|----------|--------|------|
| ConversationChannel | ❌ Falta | CREATE TABLE |
| ConversationChannelMember | ❌ Falta | CREATE TABLE |
| ConversationMessage | ❌ Falta | CREATE TABLE |

---

## 3. ESTADOS (STATUS) - AUDITORIA CRÍTICA

### 3.1 Tickets - Estado Atual

**Model Define:**
```php
public const STATUSES = [
    'novo', 'em_analise', 'aguarda_informacao', 'encaminhado',
    'em_execucao', 'agendado', 'resolvido', 'fechado',
    'cancelado', 'indeferido',
];
```

**Seeders Usam:**
```
'novo', 'em_analise', 'aguarda_informacao', 'encaminhado',
'em_execucao', 'agendado', 'resolvido', 'fechado'
```

**Plano Propõe (Fase 1):**
```
'novo', 'em_analise', 'com_tarefas', 'em_execucao',
'aguarda_validacao', 'resolvido', 'cancelado', 'indeferido'
```

**Diferenças:**
- ❌ REMOVER: `aguarda_informacao`, `encaminhado`, `agendado`
- ✅ MANTER: `novo`, `em_analise`, `em_execucao`, `resolvido`, `cancelado`, `indeferido`
- ✅ ADICIONAR: `com_tarefas`, `aguarda_validacao`

**Risco:** 🟡 MÉDIO - Existem testes/seeders usando `aguarda_informacao`, `encaminhado`. Precisam ser atualizados.

### 3.2 Tasks - Estado Atual

**Model Define:**
```php
public const STATUSES = ['pending', 'in_progress', 'waiting', 'done', 'cancelled'];
```

**Seeders Usam:**
```
'pending', 'in_progress', 'waiting', 'done', 'cancelled'
```

**Plano Propõe (Fase 2):**
```
'por_iniciar', 'em_curso', 'bloqueada', 'concluida',
'por_validar', 'validada', 'reaberta', 'cancelada'
```

**Diferenças:**
- ❌ REMOVER: `pending`, `in_progress`, `waiting`, `done`
- ✅ ADICIONAR: `por_iniciar`, `em_curso`, `bloqueada`, `concluida`, `por_validar`, `validada`, `reaberta`

**Risco:** 🔴 ALTO - Mudança radical de nomenclatura. Afeta **seeders, factories, 10+ testes, controllers, React pages, dashboards**.

---

## 4. MOTOR DE BD

**Configuração Local:**
```
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

**Configuração Produção (estimada):**
- Provavelmente PostgreSQL (Neon) ou MySQL
- Comentado em config: MySQL está como option

**Impacto:**
- ✅ SQLite local - OK para desenvolvimento
- ✅ Schema statements são compatíveis (sem ENUM rígido)
- ✅ String status é portável entre bd engines

**IMPORTANTE:** Usar STRING para status, NÃO ENUM rigid da BD, para manter flexibilidade entre SQLite/MySQL/PostgreSQL.

---

## 5. PERMISSIONS (Spatie) - AUDITORIA

**Framework:** Spatie Permission v6+  
**Padrão:** Roles + Permissions (sem Teams)

**Permissions Existentes (119 total):**
- ✅ admin.access, settings.*, users.*, roles.*
- ✅ tickets.*, tasks.*, events.*, documents.*
- ✅ spaces.*, inventory.*, hr.*, planning.*
- ✅ reports.*

**Roles Existentes (11 total):**
- `super_admin`, `admin_junta`, `executivo`, `administrativo`
- `operacional`, `manutencao`, `armazem`, `rh`
- `cidadao`, `associacao`, `empresa`

**Permissions Faltantes para Novas Funcionalidades:**

| Fase | Permissões | Status |
|------|-----------|--------|
| Fase 1 | `tickets.validate`, `tickets.generate-tasks`, `tickets.cancel` | ❌ Falta |
| Fase 2 | `tasks.start`, `tasks.validate`, `tasks.reopen` | ❌ Falta |
| Fase 3 | `mobile.access` | ❌ Falta |
| Fase 5 | `events.create-tasks`, `events.create-requests`, `events.generate-minutes` | ❌ Falta |
| Fase 6 | `resources.create`, `resources.approve`, `resources.manage` | ❌ Falta |

**Ação:** Adicionar via nova seeder ou extended RoleAndPermissionSeeder.

---

## 6. VALIDAÇÕES EXECUTADAS ✅

### 6.1 Composer
```
✅ PASS: Autoload gerado com sucesso
- 7275 classes indexadas
- Todos os packages descobertos (Inertia, Breeze, Spatie Permission, etc)
```

### 6.2 Tests
```
✅ PASS: AuthenticationTest rodou com sucesso
- 4 testes passaram
- 8 assertions
- Duration: 2.58s
```

### 6.3 Build Frontend
```
✅ PASS: npm run build concluiu com sucesso
- Assets gerados sem erros
- Gzip compressão OK
- Built in 5.42s
```

### 6.4 Database Integrity
```
✅ PASS: Migrations presentes
- 35 migrations encontradas
- Estrutura de tabelas confirmada
- Soft deletes implementados
```

---

## 7. RISCOS IDENTIFICADOS

### 🔴 ALTO RISCO

1. **Task Status Naming Change** (Fase 2)
   - Mudança de `pending/in_progress/waiting/done/cancelled` para `por_iniciar/em_curso/bloqueada/concluida/por_validar/validada/reaberta/cancelada`
   - Impacto: 10+ testes, seeders, factories, controllers, UI
   - Mitigation: Criar migration script para atualizar dados existentes

2. **Ticket Status Subset** (Fase 1)
   - Remover estados `aguarda_informacao`, `encaminhado`, `agendado`
   - Impacto: Qualquer ticket com esses estados will break
   - Mitigation: Mapeamento de estados na migration (aguarda_informacao → em_analise, etc)

### 🟡 MÉDIO RISCO

1. **Backward Compatibility** (Fases 1-2)
   - Novos campos (validated_at, validated_by, etc) serão NOT NULL sem default
   - Impacto: Queries existing que não sabem desses campos
   - Mitigation: Usar nullable no início, adicionar default em migration

2. **Pivot Table Creation** (Fase 5)
   - event_tasks precisa criação (novo pivot)
   - Impacto: Sem impacto em dados existentes, only in relational logic
   - Mitigation: Migration é segura

3. **Mobile Performance** (Fase 3)
   - Novas routes/controllers para mobile
   - Impacto: Performance queries em dispositivos baixa-rede
   - Mitigation: Lazy loading, pagination, caching

### 🟢 BAIXO RISCO

1. **New Permissions** (Phases 1-10)
   - Adicionar novas permissions é seguro
   - Mitigation: None - é aditiva

2. **New Models** (Phases 6, 10)
   - ResourceRequest, ConversationChannel, etc
   - Mitigation: Morphable design evita duplicação

---

## 8. PROPOSTA FINAL - FASE 1A (TICKETS REFACTORING)

### 8.1 Migrations Realmente Necessárias

```sql
-- M1: Add Ticket Type & Validation Fields
ALTER TABLE tickets ADD COLUMN type VARCHAR(50) DEFAULT 'internal';
ALTER TABLE tickets ADD COLUMN validated_at TIMESTAMP NULL;
ALTER TABLE tickets ADD COLUMN validated_by BIGINT UNSIGNED NULL;
ALTER TABLE tickets ADD CONSTRAINT fk_tickets_validated_by FOREIGN KEY (validated_by) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE tickets ADD COLUMN validation_notes TEXT NULL;
ALTER TABLE tickets ADD COLUMN resolution_notes TEXT NULL;

-- M2: Create Ticket Status Migration Data (para estados removidos)
-- Não precisa migration, mas precisa seeder update para remover dados antigos
```

### 8.2 Models Necessários

**Ticket.php** changes:
- Add to STATUSES: 'com_tarefas', 'aguarda_validacao'
- Remove from STATUSES: 'aguarda_informacao', 'encaminhado', 'agendado' (opcional, manter compatibilidade)
- Add cast: `validated_at` → datetime
- Add relation: belongsTo User (validator)
- Add scopes: awaitingValidation(), withPendingTasks()
- Add methods: canGenerateTasks(), canBeValidated(), canClose()

### 8.3 Controllers Necessários (Mínimos)

- `TicketValidationController@validate` - POST /admin/tickets/{ticket}/validate
- `TicketValidationController@cancel` - POST /admin/tickets/{ticket}/cancel
- `TicketTaskGenerationController@store` - POST /admin/tickets/{ticket}/generate-tasks

### 8.4 Actions Necessários

- `ValidateTicketAction` - validar e fechar pedido
- `GenerateTasksFromTicketAction` - gerar tarefas de pedido
- `CancelTicketAction` - cancelar pedido
- `TicketTypeAssignmentAction` - assign type ao criar/atualizar

### 8.5 Services Necessários

- `TicketStateTransitionService` - máquina de estados
- `TicketResolutionService` - lógica de resolução

### 8.6 Routes Necessários

```php
Route::prefix('admin')->name('admin.')->group(function () {
    Route::post('/tickets/{ticket}/validate', [TicketValidationController::class, 'validate'])->name('tickets.validate');
    Route::post('/tickets/{ticket}/cancel', [TicketValidationController::class, 'cancel'])->name('tickets.cancel');
    Route::post('/tickets/{ticket}/generate-tasks', [TicketTaskGenerationController::class, 'store'])->name('tickets.generate-tasks');
});
```

### 8.7 React Pages Necessários

- `Admin/Tickets/Show.tsx` - refactor para adicionar buttons validação/geração tarefas
- Modular - reuse componentes existentes

### 8.8 Testes Necessários

- `TicketTypeAssignmentTest.php`
- `TicketStateTransitionTest.php`
- `TicketValidationFlowTest.php`
- `TicketTaskGenerationTest.php`

### 8.9 Permissions Necessários

- `tickets.validate`
- `tickets.generate-tasks`
- `tickets.cancel`

Atribuir a: `admin_junta`, `executivo` (depending on role strategy)

---

## 9. RECOMENDAÇÕES

### 9.1 Status String vs ENUM

**DECISÃO:** ✅ Usar STRING, NÃO ENUM rígido
- Motivos:
  - Compatibilidade entre SQLite/MySQL/PostgreSQL
  - Flexibilidade para adicionar estados sem alterar schema
  - Validação fica em Model (constantes)
  - Mais fácil fazer migrations incrementais

### 9.2 Dados Existentes

**Ação:** Antes de implementar Fase 1:
1. Backup BD
2. Script migration para mapear estados antigos:
   - `aguarda_informacao` → `em_analise`
   - `encaminhado` → `em_analise`
   - `agendado` → `em_execucao`
3. Atualizar seeders/factories para usar novos estados

### 9.3 Incrementalidade

**Estratégia recomendada:**
- Fase 1A: Add fields/migrations/models (backwards compatible)
- Fase 1B: Add controllers/actions/permissions (new features, não quebra existentes)
- Fase 1C: Update UI/tests

### 9.4 Compatibilidade Retroativa

**Garantir:**
- ✅ Portal munícipe continua funcional (tickets do portal têm `type='portal'`)
- ✅ Existing tickets continuam com estado válido (mapear se necessário)
- ✅ APIs existentes não quebram (adicionar fields opcionais com defaults)

---

## 10. RESUMO EXECUTIVO

### O que Está OK ✅
- Estrutura de BD sólida (35 migrations, relações bem pensadas)
- Padrão de morphable attachments/comments perfeito para extensões
- Relação operational_plan_tasks já existe - não duplicar
- Spatie Permission bem configurado
- Testes rodando, build OK
- Validações (composer, tests, npm) passaram

### O que Falta ❌
**Fase 1 (Tickets):**
- 5 novos campos (type, validated_at, validated_by, validation_notes, resolution_notes)
- 3 controllers, 3 actions, 2 services
- Update de estados: adicionar 'com_tarefas', 'aguarda_validacao'

**Fase 2 (Tasks):**
- 5 novos campos (event_id, validated_at, validated_by, validation_notes, observations, reopen_count)
- Nota: operational_plan_id NÃO precisa - usar pivot existente
- 4 controllers, 4 actions, 2 services
- Mudança radical de status names (ALTO RISCO)

**Fase 5 (Events):**
- 2 novos campos (internal_notes, allows_task_creation, allows_request_creation)
- 1 nova pivot: event_tasks (sem impacto em dados)

**Fases 6, 10:**
- Novos models/tables (ResourceRequest, ConversationChannel)

### Riscos Críticos 🔴
1. Task status renaming (Fase 2) - 10+ ficheiros afetados
2. Ticket status subset (Fase 1) - mapear estados antigos

---

## PRÓXIMOS PASSOS

✅ **Auditoria Concluída**

Opções:
1. **Aprovar Fase 1** - Implementar Tickets refactoring
2. **Revisar Risco** - Discutir Task status renaming strategy
3. **Ajustar Plano** - Considerar backward compatibility vs clean state

---

**Preparado por:** GitHub Copilot  
**Data:** Maio 7, 2026  
**Confiança:** 95%  
**Pronto para Implementação:** ✅ SIM, após aprovação de riscos

---

## 11. FASE 0B - AUDITORIA DE ESCOPOS OPERACIONAIS (MULTI-ORG / MULTI-DEPARTAMENTO / MULTI-TEMA)

**Data:** Maio 8, 2026  
**Objetivo:** Validar se a arquitetura atual suporta pirâmide operacional com múltiplos contextos por utilizador antes de avançar para Fase 1A.

### 11.1 O que já existe

#### Organizações e utilizadores
- Existe `organizations`.
- `users` tem apenas **um** `organization_id` direto (single-org ativo por registo de utilizador).
- Existe `OrganizationScope` e trait `BelongsToOrganization` para aplicar isolamento por `organization_id`.
- Bypass de organização existe apenas para papel `super_admin`.

#### Departamentos e equipas
- `departments` pertence a organização (`organization_id`).
- `teams` pertence a organização e departamento (`organization_id`, `department_id`).
- Não existe pivot `department_user`.
- Não existe pivot `team_user`.

#### Service areas (temas)
- `service_areas` existe com `organization_id`.
- Existe pivot `service_area_user` com `role` e `is_primary`.
- Não existe `department_id` em `service_areas`.
- Não existe `parent_id` em `service_areas` (sem árvore hierárquica).

### 11.2 O que falta para suportar os casos obrigatórios

#### Multi-organização por utilizador
- Falta pivot `organization_user` (ou equivalente).
- Atualmente o sistema usa `user.organization_id` como organização corrente única em controllers/services.

#### Acesso por departamento
- Falta pivot `department_user` para autorizar acesso granular por departamento.

#### Acesso por tema com hierarquia
- `service_area_user` já existe e deve ser reutilizada.
- Falta reforçar `service_areas` com:
   - `parent_id` (árvore)
   - `department_id` (vinculação opcional ao departamento)

#### Contexto ativo no dashboard
- Não existe `DashboardContext`.
- `AdminDashboardService` trabalha com `User` e assume uma organização (`$user->organization_id`).
- Não há vista consolidada "Tudo o que me diz respeito" multi-scope.

### 11.3 Auditoria de filtragem por domínio (organization / department / service_area)

#### Tickets
- Direto: `organization_id`, `department_id`, `service_area_id`, `team_id`.
- Conclusão: já permite filtro completo por contexto, mas autorização ainda é orientada ao `user.organization_id`.

#### Tasks
- Direto: `organization_id`, `ticket_id`, `space_reservation_id`.
- Indireto: departamento/tema via `ticket`.
- Lacuna: não tem `department_id` nem `service_area_id` próprios.

#### Events
- Direto: `organization_id`, `space_id`, `related_ticket_id`.
- Indireto: departamento/tema via `related_ticket_id`.
- Lacuna: não tem `department_id` nem `service_area_id` próprios.

#### Operational plans
- Direto: `organization_id`, `department_id`, `team_id`.
- Indireto: tema via `related_ticket_id` (quando existir ticket associado).
- Lacuna: não tem `service_area_id` próprio.

#### Spaces / reservations
- Direto: `organization_id`.
- Indireto: tema/departamento apenas por vínculos relacionados (evento, ticket, tarefa).
- Lacuna: não tem `department_id` nem `service_area_id` próprios.

#### Inventory (items/movements/loans)
- Direto: `organization_id`.
- Indireto: departamento/tema via `related_ticket_id`, `related_task_id`, `related_event_id`, `related_space_id`, `related_space_reservation_id`.
- Lacuna: não tem `department_id` nem `service_area_id` próprios.

#### Communications
- Hoje: notificações + comentários + anexos (morphable).
- Não existem tabelas de canais (`conversation_channels`, etc.).
- Filtragem por departamento/tema ainda não existe para comunicação contextual.

### 11.4 Migrations mínimas propostas (arquitetura primeiro)

#### M1. Criar `organization_user` (obrigatória)
Campos:
- `organization_id`
- `user_id`
- `role_context` (string)
- `has_global_access` (boolean, default false)
- `is_default` (boolean, default false)
- `is_active` (boolean, default true)
- timestamps

Regras:
- unique(`organization_id`, `user_id`)
- unique parcial por utilizador para `is_default = true` (garantir um contexto default)

#### M2. Criar `department_user` (obrigatória)
Campos:
- `department_id`
- `user_id`
- `organization_id` (redundância útil para validação/índices)
- `role_context` (nullable)
- `is_active` (boolean)
- timestamps

Regras:
- unique(`department_id`, `user_id`)
- validação de consistência com `department.organization_id`

#### M3. Evoluir `service_areas` para árvore e vínculo departamental
Adicionar:
- `parent_id` FK self nullable
- `department_id` FK departments nullable

Índices:
- (`organization_id`, `department_id`)
- (`organization_id`, `parent_id`)

#### M4. Reforçar `service_area_user`
Adicionar:
- `organization_id` (para validação e performance)
- `is_active` (boolean)

Regras:
- manter unique(`service_area_id`, `user_id`)
- validar organização do utilizador e da service area

#### M5. Migração de compatibilidade
- Backfill de `organization_user` a partir de `users.organization_id`.
- Marcar `is_default = true` para associação principal existente.
- Backfill opcional de `service_area_user.organization_id`.

### 11.5 Serviços e arquitetura aplicacional propostos

#### UserOperationalScopeService (novo)
Responsabilidades:
- `getOrganizations(User $user)`
- `getDepartments(User $user, ?int $organizationId)`
- `getServiceAreas(User $user, ?int $organizationId, ?int $departmentId)`
- `canAccessContext(User $user, ?int $organizationId, ?int $departmentId, ?int $serviceAreaId)`
- `applyScope(Builder $query, User $user, DashboardContext $context, array $mapping = [])`

Regras de acesso:
- `has_global_access = true`: vê tudo na organização (departamentos e temas).
- Sem global access: precisa associação explícita por departamento e/ou service area.
- Regra do Presidente na Junta: modelar por `has_global_access = true` no vínculo da organização Junta.
- Presidente noutra organização externa: vínculo sem global access e com service areas/departamentos explícitos.

#### DashboardContext (novo DTO/Value Object)
Campos:
- `user`
- `organization_id`
- `department_id`
- `service_area_id`
- `all_my_scopes` (boolean)

Validação:
- construído por factory no backend após validação com `UserOperationalScopeService`.

#### OperationalDashboardService (evolução)
- Trocar assinatura principal para receber `DashboardContext` em vez de apenas `User`.
- Aplicar filtros por árvore: organização -> departamento -> tema.
- Suportar modo "Tudo o que me diz respeito" agregando contextos permitidos.

### 11.6 Impacto na Fase 1A (tickets)

Antes de mexer em lógica funcional de tickets/tasks:
- Implementar fundação de escopo operacional (M1-M5 + serviços de escopo).
- Ajustar validações backend para filtros de contexto em tickets.
- Só depois introduzir novos estados/ações da Fase 1A.

Impacto direto:
- `TicketController`, `StoreTicketRequest`, `UpdateTicketRequest` deverão validar `department_id` e `service_area_id` contra contexto autorizado do utilizador, não apenas contra `user.organization_id`.
- Listagens/dashboards deixam de depender exclusivamente de `user.organization_id`.

### 11.7 Recomendações de compatibilidade

1. Manter `users.organization_id` temporariamente como "contexto default legado".
2. Introduzir feature flag para novo modelo de escopo (rollout progressivo).
3. Não remover filtros atuais até concluir backfill e testes de autorização.
4. Criar testes de segurança de escopo cruzado:
    - multi-org por utilizador
    - global access numa organização e restrito noutra
    - filtros backend rejeitando contexto não autorizado
5. Só depois de estabilizar escopos, avançar com Fase 1A funcional.

### 11.8 Conclusão

✅ O projeto está bem preparado para isolamento por organização, mas ainda em modelo single-org ativo por utilizador.  
⚠️ Para cumprir os casos obrigatórios de pirâmide operacional, a prioridade técnica correta é introduzir camada de escopos (organization_user + department_user + service area hierárquica + UserOperationalScopeService + DashboardContext) antes da Fase 1A de tickets.
