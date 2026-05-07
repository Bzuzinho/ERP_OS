# Plano Estratégico de Refatoração: Centro Operacional da Junta
**Data:** Maio 7, 2026  
**Versão:** 1.0  
**Status:** Análise Estruturada (Sem Alterações de Código)

---

## ÍNDICE
1. [Mapa da Estrutura Existente](#1-mapa-da-estrutura-existente)
2. [Princípios de Design](#2-princípios-de-design)
3. [Lista de Alterações Mínimas por Fase](#3-lista-de-alterações-mínimas-por-fase)
4. [Plano de Migrations](#4-plano-de-migrations)
5. [Plano de Models & Relations](#5-plano-de-models--relations)
6. [Plano de Controllers & Routes](#6-plano-de-controllers--routes)
7. [Plano de Services & Actions](#7-plano-de-services--actions)
8. [Plano de Páginas React/Inertia](#8-plano-de-páginas-reactinertia)
9. [Plano de Policies & Permissions](#9-plano-de-policies--permissions)
10. [Plano de Testes](#10-plano-de-testes)
11. [Ordem Recomendada de Implementação](#11-ordem-recomendada-de-implementação)
12. [Risco & Dependências](#12-risco--dependências)

---

## 1. MAPA DA ESTRUTURA EXISTENTE

### 1.1 Entidades Principais Relevantes

```
CORE
├── User (auth)
├── Organization (multi-tenant)
├── Department
└── Team

COMUNICAÇÃO
├── Ticket (tickets/ocorrências)
├── Task (tarefas)
├── Comment (comentários - morphable)
├── Notification (notificações)
└── Attachment (anexos - morphable)

OPERACIONAL
├── Event (agenda)
├── EventParticipant
├── OperationalPlan (planos)
├── OperationalPlanTask
├── OperationalPlanParticipant
├── OperationalPlanResource
└── RecurringOperation (operações recorrentes)

ESPAÇOS
├── Space (espaços)
├── SpaceReservation (reservas)
├── SpaceReservationApproval
├── SpaceMaintenanceRecord
└── SpaceCleaningRecord

INVENTÁRIO
├── InventoryItem
├── InventoryCategory
├── InventoryLocation
├── InventoryMovement
├── InventoryLoan
├── InventoryBreakage
└── InventoryRestockRequest

RECURSOS HUMANOS
├── Employee
├── AttendanceRecord
├── LeaveRequest
├── EmployeeEventAssignment
├── EmployeeTaskAssignment
└── AbsenceType

DOCUMENTOS
├── Document
├── DocumentType
├── DocumentVersion
├── DocumentAccessRule
└── MeetingMinute

SUPORTE
├── TaskChecklist
├── TaskChecklistItem
├── ServiceArea
└── TicketStatusHistory
```

### 1.2 Cobertura Existente

| Domínio | Models | Controllers | Actions | Services | Tests | React Pages |
|---------|--------|-------------|---------|----------|-------|------------|
| Tickets | 2 | 4 | 3 | 2 | ✓ | 4 |
| Tasks | 3 | 3 | 3 | 0 | ✓ | 4 |
| Events | 2 | 2 | 2 | 1 | ✓ | 4 |
| Spaces | 6 | 8 | 11 | 2 | ✓ | 11 |
| Inventory | 7 | 11 | 14 | 4 | ✓ | 14 |
| HR | 9 | 6 | 14 | 1 | ✓ | 10 |
| Planning | 6 | 8 | 14 | 2 | ✓ | 4 |
| Documents | 5 | 8 | 7 | 3 | ✓ | 8 |
| Notifications | 2 | 2 | 0 | 2 | ✓ | 1 |
| **TOTAL** | **42** | **52** | **68** | **17** | **31** | **60** |

---

## 2. PRINCÍPIOS DE DESIGN

### 2.1 Reutilização
- ✓ Reutilizar modelos existentes sempre que possível
- ✓ Evitar duplicate entidades
- ✓ Criar novas tabelas apenas quando essencial
- ✓ Privilegiar relações entre entidades existentes

### 2.2 Compatibilidade
- ✓ Manter portal do munícipe funcional (mas não prioridade)
- ✓ Garantir multi-organização (organization_id em tudo)
- ✓ Garantir RBAC com Spatie permissions & policies
- ✓ Manter audit trail com ActivityLog

### 2.3 UX Mobile
- ✓ Interfaces mobile-first para terreno
- ✓ Menus simplificados
- ✓ Fluxos de checklist offline-ready
- ✓ Carregamento incremental de dados

### 2.4 Integridade Operacional
- ✓ Estado (status) como máquina de estados
- ✓ Validações no Model e Action
- ✓ Eventos Laravel para side-effects
- ✓ Idempotência em operações críticas

---

## 3. LISTA DE ALTERAÇÕES MÍNIMAS POR FASE

### FASE 1: Refatoração de Tickets → Pedidos/Ocorrências

#### Migrations
1. **Alter `tickets` table:**
   - ADD `type` ENUM('internal', 'portal', 'occurrence', 'maintenance', 'logistics', 'cleaning', 'complaint', 'information') DEFAULT 'internal'
   - MODIFY `status` ENUM('novo', 'em_analise', 'com_tarefas', 'em_execucao', 'aguarda_validacao', 'resolvido', 'cancelado', 'indeferido') 
   - ADD `validated_at` TIMESTAMP NULL
   - ADD `validated_by` BIGINT UNSIGNED NULL (FK users)
   - ADD `validation_notes` TEXT NULL
   - ADD `resolution_notes` TEXT NULL

#### Models
1. **Ticket.php** changes:
   - Add casts for new timestamps/status
   - Add validation_notes, resolution_notes attributes
   - Add relations: belongsTo User (validator), morphMany OperationalPlan
   - Add scopes: byType(), byStatus(), pending(), resolved(), awaiting_validation()

#### Controllers
1. **TicketStatusController.php** - reforçar transições de estado
2. **TicketValidationController.php** (novo) - validar pedidos resolvidos

#### Actions
1. **UpdateTicketStatusAction.php** - reforçar com lógica de transição para com_tarefas
2. **GenerateTasksFromTicketAction.php** (novo) - gerar tarefas de pedido
3. **ValidateTicketAction.php** (novo) - validar e fechar pedido
4. **CancelTicketAction.php** (novo) - cancelar pedido

#### Services
1. **TicketStateTransitionService.php** (novo) - máquina de estados
2. **TicketResolutionService.php** (novo) - lógica de resolução

#### Routes
- POST `/admin/tickets/{ticket}/validate` (ValidateTicketAction)
- POST `/admin/tickets/{ticket}/generate-tasks` (GenerateTasksFromTicketAction)

#### React Pages
1. **Admin/Tickets/Show.tsx** - adicionar UI para validação
2. **Admin/Tickets/Edit.tsx** - adicionar tipo e notas de resolução

#### Tests
- TicketTypeAndStatusTest.php
- TicketValidationFlowTest.php
- TicketTaskGenerationTest.php

---

### FASE 2: Reforço de Tasks → Motor Operacional

#### Migrations
1. **Alter `tasks` table:**
   - ADD `operational_plan_id` BIGINT UNSIGNED NULL (FK operational_plans)
   - ADD `event_id` BIGINT UNSIGNED NULL (FK events)
   - MODIFY `status` ENUM('por_iniciar', 'em_curso', 'bloqueada', 'concluida', 'por_validar', 'validada', 'reaberta', 'cancelada')
   - ADD `validated_at` TIMESTAMP NULL
   - ADD `validated_by` BIGINT UNSIGNED NULL (FK users)
   - ADD `validation_notes` TEXT NULL
   - ADD `observations` LONGTEXT NULL
   - ADD `reopen_count` INT DEFAULT 0

2. **Create `task_media` table** (se não existir):
   - id, task_id, attachment_id, type (photo|document), uploaded_at

#### Models
1. **Task.php** changes:
   - Add relations: belongsTo OperationalPlan, belongsTo Event
   - Add hasManyThrough to space (via SpaceReservation)
   - Add scopes: pending(), active(), completed(), awaiting_validation()
   - Add methods: canStart(), canComplete(), canValidate(), canReopen()

2. **Create TaskMedia.php** (se usar nova tabela)

#### Controllers
1. **TaskStatusController.php** - reforçar transições
2. **TaskValidationController.php** (novo) - validação de tarefas
3. **TaskMediaController.php** (novo) - gerenciar fotos/docs

#### Actions
1. **StartTaskAction.php** (novo) - iniciar tarefa
2. **CompleteTaskAction.php** - refatorar para novo status
3. **ValidateTaskAction.php** (novo) - validar tarefa
4. **ReopenTaskAction.php** (novo) - reabrir tarefa
5. **AttachTaskMediaAction.php** (novo) - anexar fotos/docs

#### Services
1. **TaskStateTransitionService.php** (novo)
2. **TaskProgressService.php** (novo) - calcular progresso de tarefas
3. **TaskMediaService.php** (novo) - gerenciar mídia

#### Routes
- POST `/admin/tasks/{task}/start` (StartTaskAction)
- PATCH `/admin/tasks/{task}/status` - refatorar para novos estados
- POST `/admin/tasks/{task}/validate` (ValidateTaskAction)
- POST `/admin/tasks/{task}/reopen` (ReopenTaskAction)
- POST `/admin/tasks/{task}/media` (AttachTaskMediaAction)

#### React Pages
1. **Admin/Tasks/Show.tsx** - adicionar UI para validação e observações
2. **Admin/Tasks/Mobile.tsx** (novo) - versão mobile com checklist focado

#### Tests
- TaskStateTransitionTest.php
- TaskValidationFlowTest.php
- TaskMediaAttachmentTest.php

---

### FASE 3: Mobile / Modo Terreno

#### Routes
Criar `routes/mobile.php`:
- GET `/mobile` - Redirect to Hoje
- GET `/mobile/hoje` (TodayController)
- GET `/mobile/minhas-tarefas` (MyTasksController)
- GET `/mobile/pedidos` (MyRequestsController)
- GET `/mobile/agenda` (MyCalendarController)
- GET `/mobile/comunicacoes` (CommunicationsController)
- GET `/mobile/mais` (MoreController)
- POST `/mobile/tasks/{task}/start` (direct action)
- POST `/mobile/tasks/{task}/complete` (direct action)

#### Controllers (novo)
1. **Mobile/TodayController.php** - resumo do dia
2. **Mobile/MyTasksController.php** - minhas tarefas (contexto terreno)
3. **Mobile/MyRequestsController.php** - meus pedidos
4. **Mobile/MyCalendarController.php** - minha agenda
5. **Mobile/CommunicationsController.php** - notificações/msgs
6. **Mobile/MoreController.php** - menu adicional

#### React Pages (novo)
1. **Mobile/Layout.tsx** - layout base mobile
2. **Mobile/Today/Index.tsx** - dashboard dia
3. **Mobile/MyTasks/Index.tsx** - lista tarefas
4. **Mobile/MyTasks/Show.tsx** - tarefa com checklist
5. **Mobile/MyTasks/StartFlow.tsx** - iniciar tarefa
6. **Mobile/MyTasks/CompleteFlow.tsx** - concluir tarefa
7. **Mobile/MyRequests/Index.tsx** - lista pedidos
8. **Mobile/MyCalendar/Index.tsx** - agenda pessoal
9. **Mobile/Communications/Index.tsx** - notificações
10. **Mobile/More/Index.tsx** - mais opções

#### Tests
- MobileTaskFlowTest.php
- MobileTodayViewTest.php

---

### FASE 4: Dashboard Executivo

#### Services (novo)
1. **OperationalDashboardService.php** - agregar dados reais
   - getScheduleToday()
   - getUpcomingActivities()
   - getAlerts()
   - getSpaceStatus()
   - getHrStatus()
   - getRecentRequests()
   - getActiveTasksAndPlans()

#### Controllers
1. **Admin/DashboardController.php** - refatorar para usar novo service

#### React Pages
1. **Admin/Dashboard/Index.tsx** - refatorar com cards clicáveis

#### Tests
- OperationalDashboardServiceTest.php
- DashboardCardsClickabilityTest.php

---

### FASE 5: Agenda

#### Migrations
1. **Alter `events` table:**
   - ADD `meeting_minutes_id` BIGINT UNSIGNED NULL (FK documents)
   - ADD `internal_notes` TEXT NULL
   - MODIFY `event_type` para incluir 'diligence', 'activity'
   - ADD `allows_task_creation` BOOLEAN DEFAULT FALSE
   - ADD `allows_request_creation` BOOLEAN DEFAULT FALSE

2. **Create `event_tasks` table (pivot)** (se ainda não tiver relação has-many):
   - event_id, task_id

#### Models
1. **Event.php** changes:
   - Add relation: hasMany Task (via event_tasks pivot)
   - Add methods: canGenerateMeetingMinute(), canCreateTasks()

2. **MeetingMinute.php** changes:
   - Add belongsTo Event
   - Add scopes: forEvent()

#### Controllers
1. **EventTaskController.php** (novo) - criar tarefas dentro evento
2. **EventRequestController.php** (novo) - criar pedidos dentro evento
3. **EventMeetingMinuteController.php** (novo) - gerar ata de reunião

#### Actions
1. **CreateTaskFromEventAction.php** (novo)
2. **CreateTicketFromEventAction.php** (novo)
3. **GenerateMeetingMinuteFromEventAction.php** (novo) - pré-popular ata

#### Services
1. **EventToMeetingMinuteService.php** (novo) - gerar ata estruturada

#### Routes
- POST `/admin/events/{event}/tasks` (CreateTaskFromEventAction)
- POST `/admin/events/{event}/requests` (CreateTicketFromEventAction)
- POST `/admin/events/{event}/meeting-minute` (GenerateMeetingMinuteFromEventAction)

#### React Pages
1. **Admin/Events/Show.tsx** - adicionar botões para criar tarefas/pedidos/ata

#### Tests
- EventTaskCreationTest.php
- MeetingMinuteGenerationTest.php

---

### FASE 6: Espaços e Requisições

#### Migrations
1. **Create `resource_requests` table** (novo):
   - id, organization_id, title, status, requested_by, approved_by, created_at
   - requestable_type, requestable_id (morphable: space, event, task, operational_plan, ticket, space_reservation)
   - delivery_date, return_date, notes

2. **Create `resource_request_items` table** (novo):
   - id, resource_request_id, inventory_item_id, quantity, unit, observations

3. **Alter `space_reservations` table:**
   - ADD `allows_maintenance_request` BOOLEAN DEFAULT TRUE
   - ADD `allows_material_request` BOOLEAN DEFAULT TRUE

#### Models
1. **Create ResourceRequest.php** (novo):
   - Morphs to: Space, Event, Task, OperationalPlan, Ticket, SpaceReservation
   - hasMany ResourceRequestItem
   - belongsTo User (requestedBy, approvedBy)

2. **Create ResourceRequestItem.php** (novo):
   - belongsTo ResourceRequest, InventoryItem

3. **Space.php** changes:
   - Add hasMany ResourceRequest
   - Add scopes: available(), maintenance_needed()

4. **SpaceReservation.php** changes:
   - Add hasMany ResourceRequest

#### Controllers
1. **ResourceRequestController.php** (novo) - CRUD
2. **ResourceRequestApprovalController.php** (novo) - approve/reject
3. **ResourceRequestDeliveryController.php** (novo) - entregar material

#### Actions
1. **CreateResourceRequestAction.php** (novo)
2. **ApproveResourceRequestAction.php** (novo)
3. **CompleteResourceRequestAction.php** (novo)

#### Services
1. **ResourceRequestService.php** (novo)
2. **SpaceMaintenanceService.php** (novo) - requisitar manutenção

#### Routes
- GET|POST `/admin/resource-requests`
- PATCH `/admin/resource-requests/{request}/approve`
- POST `/admin/resource-requests/{request}/complete`
- Nested: `/admin/spaces/{space}/resource-requests`, `/admin/events/{event}/resource-requests`

#### React Pages
1. **Admin/ResourceRequests/Index.tsx** (novo)
2. **Admin/ResourceRequests/Create.tsx** (novo) - com contexto (space, event, etc)
3. **Admin/ResourceRequests/Show.tsx** (novo)
4. **Admin/Spaces/Show.tsx** - adicionar tab de requisições

#### Tests
- ResourceRequestFlowTest.php
- ResourceRequestMorphabilityTest.php

---

### FASE 7: Recursos Materiais (Visão Separada)

#### Controllers
1. **Inventory/InventoryDashboardController.php** (novo) - dashboard separado

#### Services
1. **InventoryAlertService.php** (novo) - stock abaixo do mínimo
2. **InventoryLoanTrackingService.php** - reforçar tracking

#### React Pages
1. **Admin/Inventory/Dashboard.tsx** (novo) - visão separada
2. **Admin/Inventory/Items.tsx** (novo)
3. **Admin/Inventory/Stock.tsx** (novo)
4. **Admin/Inventory/Loans.tsx** (novo)
5. **Admin/Inventory/Movements.tsx** (novo)
6. **Admin/Inventory/Alerts.tsx** (novo)

#### Tests
- InventoryAlertTest.php
- InventoryLoanTrackingTest.php

---

### FASE 8: Funcionários/RH

#### Services
1. **EmployeeAvailabilityService.php** (novo) - consultar disponibilidade
   - getByDate()
   - getByDateRange()
   - getAbsences()
   - getLeaveRequests()

#### Models
1. **Employee.php** changes:
   - Add scopes: available(), onLeave(), absent()

#### React Pages
1. **Admin/Hr/Availability.tsx** (novo) - calendário de disponibilidade

#### Tests
- EmployeeAvailabilityTest.php

---

### FASE 9: Planeamento Operacional (Evolução)

#### Migrations
1. **Alter `operational_plans` table:**
   - MODIFY `status` ENUM('rascunho', 'submetido', 'aprovado', 'em_planeamento', 'em_execucao', 'por_validar', 'concluido', 'cancelado', 'arquivado')
   - ADD `budget_actual` DECIMAL(10,2) NULL
   - ADD `budget_currency` VARCHAR(3) DEFAULT 'EUR'

#### Models
1. **OperationalPlan.php** changes:
   - Add relation: morphMany ResourceRequest
   - Add scopes: byStatus()

#### Services
1. **OperationalPlanProgressService.php** (novo):
   - calculateProgress() - com base em tarefas completadas/validadas
   - updateProgress() - event listener

#### Actions
1. **ArchiveOperationalPlanAction.php** (novo)

#### Routes
- POST `/admin/operational-plans/{plan}/archive`

#### React Pages
1. **Admin/OperationalPlans/Show.tsx** - adicionar progresso visual

#### Tests
- OperationalPlanProgressTest.php
- OperationalPlanAutoUpdateTest.php

---

### FASE 10: Comunicação Interna

#### Migrations
1. **Create `conversation_channels` table** (novo):
   - id, organization_id, name, slug, channel_type (department, team, plan, task, request, space, event, general)
   - channelable_type, channelable_id (morphable)
   - created_by, created_at

2. **Create `conversation_channel_members` table** (novo):
   - id, channel_id, user_id, joined_at, left_at

3. **Create `conversation_messages` table** (novo):
   - id, channel_id, user_id, message, created_at
   - parent_message_id (para threads)

#### Models
1. **Create ConversationChannel.php** (novo)
2. **Create ConversationMessage.php** (novo)
3. **Create ConversationChannelMember.php** (novo)

#### Controllers
1. **ConversationChannelController.php** (novo)
2. **ConversationMessageController.php** (novo)

#### Services
1. **ConversationChannelService.php** (novo)
2. **ConversationNotificationService.php** (novo)

#### React Pages (Phase incremental)
1. **Admin/Conversations/Channels.tsx** (novo)
2. **Admin/Conversations/Channel.tsx** (novo) - chat

#### Tests
- ConversationChannelTest.php
- ConversationPermissionTest.php

---

## 4. PLANO DE MIGRATIONS

### Ordem Recomendada de Migrations

```sql
-- FASE 1
ALTER TABLE tickets ADD COLUMN type ENUM('internal', 'portal', 'occurrence', 'maintenance', 'logistics', 'cleaning', 'complaint', 'information') DEFAULT 'internal';
ALTER TABLE tickets MODIFY COLUMN status ENUM('novo', 'em_analise', 'com_tarefas', 'em_execucao', 'aguarda_validacao', 'resolvido', 'cancelado', 'indeferido');
ALTER TABLE tickets ADD COLUMN validated_at TIMESTAMP NULL;
ALTER TABLE tickets ADD COLUMN validated_by BIGINT UNSIGNED NULL;
ALTER TABLE tickets ADD CONSTRAINT fk_tickets_validated_by FOREIGN KEY (validated_by) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE tickets ADD COLUMN validation_notes TEXT NULL;
ALTER TABLE tickets ADD COLUMN resolution_notes TEXT NULL;

-- FASE 2
ALTER TABLE tasks ADD COLUMN operational_plan_id BIGINT UNSIGNED NULL;
ALTER TABLE tasks ADD CONSTRAINT fk_tasks_operational_plan FOREIGN KEY (operational_plan_id) REFERENCES operational_plans(id) ON DELETE SET NULL;
ALTER TABLE tasks ADD COLUMN event_id BIGINT UNSIGNED NULL;
ALTER TABLE tasks ADD CONSTRAINT fk_tasks_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE SET NULL;
ALTER TABLE tasks MODIFY COLUMN status ENUM('por_iniciar', 'em_curso', 'bloqueada', 'concluida', 'por_validar', 'validada', 'reaberta', 'cancelada');
ALTER TABLE tasks ADD COLUMN validated_at TIMESTAMP NULL;
ALTER TABLE tasks ADD COLUMN validated_by BIGINT UNSIGNED NULL;
ALTER TABLE tasks ADD CONSTRAINT fk_tasks_validated_by FOREIGN KEY (validated_by) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE tasks ADD COLUMN validation_notes TEXT NULL;
ALTER TABLE tasks ADD COLUMN observations LONGTEXT NULL;
ALTER TABLE tasks ADD COLUMN reopen_count INT DEFAULT 0;

CREATE TABLE task_media (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    task_id BIGINT UNSIGNED NOT NULL,
    attachment_id BIGINT UNSIGNED NOT NULL,
    type ENUM('photo', 'document') DEFAULT 'document',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (attachment_id) REFERENCES attachments(id) ON DELETE CASCADE,
    INDEX(task_id)
);

-- FASE 5
ALTER TABLE events ADD COLUMN internal_notes TEXT NULL;
ALTER TABLE events MODIFY COLUMN event_type ENUM('meeting', 'appointment', 'visit', 'activity', 'maintenance', 'assembly', 'reservation', 'diligence', 'other');
ALTER TABLE events ADD COLUMN allows_task_creation BOOLEAN DEFAULT FALSE;
ALTER TABLE events ADD COLUMN allows_request_creation BOOLEAN DEFAULT FALSE;

CREATE TABLE event_tasks (
    event_id BIGINT UNSIGNED NOT NULL,
    task_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (event_id, task_id),
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

-- FASE 6
CREATE TABLE resource_requests (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    status ENUM('requested', 'approved', 'rejected', 'prepared', 'delivered', 'partially_returned', 'returned', 'cancelled') DEFAULT 'requested',
    requested_by BIGINT UNSIGNED NOT NULL,
    approved_by BIGINT UNSIGNED NULL,
    requestable_type VARCHAR(255),
    requestable_id BIGINT UNSIGNED,
    delivery_date DATE NULL,
    return_date DATE NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX(organization_id),
    INDEX(requested_by),
    INDEX(requestable_type, requestable_id)
);

CREATE TABLE resource_request_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    resource_request_id BIGINT UNSIGNED NOT NULL,
    inventory_item_id BIGINT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    unit VARCHAR(50),
    observations TEXT NULL,
    FOREIGN KEY (resource_request_id) REFERENCES resource_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id) ON DELETE RESTRICT,
    INDEX(resource_request_id)
);

ALTER TABLE space_reservations ADD COLUMN allows_maintenance_request BOOLEAN DEFAULT TRUE;
ALTER TABLE space_reservations ADD COLUMN allows_material_request BOOLEAN DEFAULT TRUE;

-- FASE 10
CREATE TABLE conversation_channels (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    channel_type ENUM('department', 'team', 'plan', 'task', 'request', 'space', 'event', 'general') NOT NULL,
    channelable_type VARCHAR(255) NULL,
    channelable_id BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY(slug, organization_id),
    INDEX(organization_id),
    INDEX(channelable_type, channelable_id)
);

CREATE TABLE conversation_channel_members (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    channel_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    left_at TIMESTAMP NULL,
    FOREIGN KEY (channel_id) REFERENCES conversation_channels(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY(channel_id, user_id),
    INDEX(user_id)
);

CREATE TABLE conversation_messages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    channel_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    message LONGTEXT NOT NULL,
    parent_message_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (channel_id) REFERENCES conversation_channels(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (parent_message_id) REFERENCES conversation_messages(id) ON DELETE CASCADE,
    INDEX(channel_id),
    INDEX(user_id),
    INDEX(created_at)
);

-- FASE 9
ALTER TABLE operational_plans MODIFY COLUMN status ENUM('rascunho', 'submetido', 'aprovado', 'em_planeamento', 'em_execucao', 'por_validar', 'concluido', 'cancelado', 'arquivado');
ALTER TABLE operational_plans ADD COLUMN budget_actual DECIMAL(10,2) NULL;
ALTER TABLE operational_plans ADD COLUMN budget_currency VARCHAR(3) DEFAULT 'EUR';
```

---

## 5. PLANO DE MODELS & RELATIONS

### Novos Models

#### ResourceRequest (Fase 6)
```php
class ResourceRequest extends Model {
    protected $fillable = ['organization_id', 'title', 'status', 'requested_by', 'approved_by', 'requestable_type', 'requestable_id', 'delivery_date', 'return_date', 'notes'];
    
    protected $casts = ['delivery_date' => 'date', 'return_date' => 'date'];
    
    public function organization() { return $this->belongsTo(Organization::class); }
    public function requestedBy() { return $this->belongsTo(User::class, 'requested_by'); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by'); }
    public function items() { return $this->hasMany(ResourceRequestItem::class); }
    public function requestable() { return $this->morphTo(); }
    
    public function scopeByStatus($query, $status) {}
    public function scopePending($query) {}
    public function scopeApproved($query) {}
}
```

#### ResourceRequestItem (Fase 6)
```php
class ResourceRequestItem extends Model {
    protected $fillable = ['resource_request_id', 'inventory_item_id', 'quantity', 'unit', 'observations'];
    
    public function request() { return $this->belongsTo(ResourceRequest::class, 'resource_request_id'); }
    public function inventoryItem() { return $this->belongsTo(InventoryItem::class); }
}
```

#### ConversationChannel (Fase 10)
```php
class ConversationChannel extends Model {
    protected $fillable = ['organization_id', 'name', 'slug', 'channel_type', 'channelable_type', 'channelable_id', 'created_by'];
    
    public function organization() { return $this->belongsTo(Organization::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function members() { return $this->belongsToMany(User::class, 'conversation_channel_members', 'channel_id', 'user_id'); }
    public function messages() { return $this->hasMany(ConversationMessage::class, 'channel_id'); }
    public function channelable() { return $this->morphTo(); }
}
```

#### ConversationChannelMember (Fase 10)
```php
class ConversationChannelMember extends Model {
    public $timestamps = false;
    protected $fillable = ['channel_id', 'user_id', 'joined_at', 'left_at'];
    protected $casts = ['joined_at' => 'datetime', 'left_at' => 'datetime'];
    
    public function channel() { return $this->belongsTo(ConversationChannel::class); }
    public function user() { return $this->belongsTo(User::class); }
}
```

#### ConversationMessage (Fase 10)
```php
class ConversationMessage extends Model {
    protected $fillable = ['channel_id', 'user_id', 'message', 'parent_message_id'];
    
    public function channel() { return $this->belongsTo(ConversationChannel::class, 'channel_id'); }
    public function user() { return $this->belongsTo(User::class); }
    public function parentMessage() { return $this->belongsTo(ConversationMessage::class, 'parent_message_id'); }
    public function replies() { return $this->hasMany(ConversationMessage::class, 'parent_message_id'); }
    public function attachments() { return $this->morphMany(Attachment::class, 'attachmentable'); }
}
```

### Models Existentes com Alterações

#### Ticket (Fase 1)
```php
// Adicionar:
protected $casts = ['validated_at' => 'datetime'];
protected $fillable = [..., 'type', 'validated_by', 'validation_notes', 'resolution_notes'];

public function validator() { return $this->belongsTo(User::class, 'validated_by'); }
public function operationalPlans() { return $this->morphMany(OperationalPlan::class, 'related'); }

public function scopeByType($query, $type) {}
public function scopeByStatus($query, $status) {}
public function scopePending($query) { return $query->whereIn('status', ['novo', 'em_analise', 'com_tarefas', 'em_execucao']); }
public function scopeAwaitingValidation($query) { return $query->where('status', 'aguarda_validacao'); }

public function canGenerateTasks() {}
public function canBeValidated() {}
```

#### Task (Fase 2)
```php
// Adicionar:
protected $casts = ['validated_at' => 'datetime'];
protected $fillable = [..., 'operational_plan_id', 'event_id', 'validated_by', 'validation_notes', 'observations', 'reopen_count'];

public function operationalPlan() { return $this->belongsTo(OperationalPlan::class); }
public function event() { return $this->belongsTo(Event::class); }
public function validator() { return $this->belongsTo(User::class, 'validated_by'); }
public function media() { return $this->hasMany(TaskMedia::class); }

public function scopePending($query) { return $query->where('status', 'por_iniciar'); }
public function scopeActive($query) { return $query->whereIn('status', ['em_curso', 'bloqueada']); }
public function scopeCompleted($query) { return $query->where('status', 'concluida'); }
public function scopeAwaitingValidation($query) { return $query->where('status', 'por_validar'); }

public function canStart() {}
public function canComplete() {}
public function canValidate() {}
public function canReopen() {}
```

#### Event (Fase 5)
```php
// Adicionar:
protected $fillable = [..., 'internal_notes', 'allows_task_creation', 'allows_request_creation'];

public function tasks() { return $this->belongsToMany(Task::class, 'event_tasks'); }
public function meetingMinutes() { return $this->hasMany(MeetingMinute::class); }

public function canGenerateMeetingMinute() {}
```

#### Space (Fase 6)
```php
// Adicionar:
public function resourceRequests() { return $this->morphMany(ResourceRequest::class, 'requestable'); }

public function scopeAvailable($query) {}
```

#### SpaceReservation (Fase 6)
```php
// Adicionar:
protected $fillable = [..., 'allows_maintenance_request', 'allows_material_request'];

public function resourceRequests() { return $this->morphMany(ResourceRequest::class, 'requestable'); }
```

#### OperationalPlan (Fase 9)
```php
// Adicionar:
protected $casts = ['budget_actual' => 'decimal:2'];
protected $fillable = [..., 'budget_actual', 'budget_currency'];

public function resourceRequests() { return $this->morphMany(ResourceRequest::class, 'requestable'); }

public function scopeByStatus($query, $status) {}
```

---

## 6. PLANO DE CONTROLLERS & ROUTES

### Novos Controllers

#### Ticket Management (Fase 1)
```
app/Http/Controllers/Admin/TicketValidationController.php
  - validate(Request $request, Ticket $ticket): RedirectResponse
  - cancel(Request $request, Ticket $ticket): RedirectResponse

app/Http/Controllers/Admin/TicketTaskGenerationController.php
  - store(Request $request, Ticket $ticket): RedirectResponse
```

#### Task Management (Fase 2)
```
app/Http/Controllers/Admin/TaskStartController.php
  - store(Request $request, Task $task): RedirectResponse

app/Http/Controllers/Admin/TaskValidationController.php
  - validate(Request $request, Task $task): RedirectResponse
  - reopen(Request $request, Task $task): RedirectResponse

app/Http/Controllers/Admin/TaskMediaController.php
  - store(Request $request, Task $task): RedirectResponse
  - destroy(Request $request, Task $task, TaskMedia $media): RedirectResponse
```

#### Mobile (Fase 3)
```
app/Http/Controllers/Mobile/TodayController.php
  - index(): Response

app/Http/Controllers/Mobile/MyTasksController.php
  - index(): Response
  - show(Task $task): Response

app/Http/Controllers/Mobile/MyRequestsController.php
  - index(): Response

app/Http/Controllers/Mobile/MyCalendarController.php
  - index(): Response

app/Http/Controllers/Mobile/CommunicationsController.php
  - index(): Response

app/Http/Controllers/Mobile/MoreController.php
  - index(): Response
```

#### Dashboard (Fase 4)
```
app/Http/Controllers/Admin/OperationalDashboardController.php
  - index(OperationalDashboardService $service): Response
  - refresh(): JsonResponse
```

#### Event Management (Fase 5)
```
app/Http/Controllers/Admin/EventTaskController.php
  - create(Event $event): Response
  - store(Request $request, Event $event): RedirectResponse

app/Http/Controllers/Admin/EventRequestController.php
  - create(Event $event): Response
  - store(Request $request, Event $event): RedirectResponse

app/Http/Controllers/Admin/EventMeetingMinuteController.php
  - create(Event $event): Response
  - store(Request $request, Event $event): RedirectResponse
```

#### Resource Management (Fase 6)
```
app/Http/Controllers/Admin/ResourceRequestController.php
  - index(): Response
  - create(): Response
  - store(Request $request): RedirectResponse
  - show(ResourceRequest $request): Response
  - edit(ResourceRequest $request): Response
  - update(Request $request, ResourceRequest $resourceRequest): RedirectResponse
  - destroy(ResourceRequest $request): RedirectResponse

app/Http/Controllers/Admin/ResourceRequestApprovalController.php
  - approve(Request $request, ResourceRequest $resourceRequest): RedirectResponse
  - reject(Request $request, ResourceRequest $resourceRequest): RedirectResponse

app/Http/Controllers/Admin/ResourceRequestDeliveryController.php
  - store(Request $request, ResourceRequest $resourceRequest): RedirectResponse
  - complete(Request $request, ResourceRequest $resourceRequest): RedirectResponse
```

#### Inventory Dashboard (Fase 7)
```
app/Http/Controllers/Admin/Inventory/DashboardController.php
  - index(): Response

app/Http/Controllers/Admin/Inventory/StockController.php
  - index(): Response

app/Http/Controllers/Admin/Inventory/AlertsController.php
  - index(): Response
```

#### HR Availability (Fase 8)
```
app/Http/Controllers/Admin/Hr/AvailabilityController.php
  - index(): Response
  - byDate(Request $request): JsonResponse
```

#### Planning (Fase 9)
```
app/Http/Controllers/Admin/OperationalPlanArchiveController.php
  - store(Request $request, OperationalPlan $plan): RedirectResponse
```

#### Conversations (Fase 10)
```
app/Http/Controllers/Admin/ConversationChannelController.php
  - index(): Response
  - create(): Response
  - store(Request $request): RedirectResponse
  - show(ConversationChannel $channel): Response
  - destroy(ConversationChannel $channel): RedirectResponse

app/Http/Controllers/Admin/ConversationMessageController.php
  - store(Request $request, ConversationChannel $channel): RedirectResponse
  - destroy(Request $request, ConversationChannel $channel, ConversationMessage $message): RedirectResponse
```

### Routes Mínimas

#### Fase 1
```php
// routes/web.php (admin group)
Route::name('admin.')->group(function () {
    Route::post('/tickets/{ticket}/validate', [TicketValidationController::class, 'validate'])->name('tickets.validate');
    Route::post('/tickets/{ticket}/cancel', [TicketValidationController::class, 'cancel'])->name('tickets.cancel');
    Route::post('/tickets/{ticket}/generate-tasks', [TicketTaskGenerationController::class, 'store'])->name('tickets.generate-tasks');
});
```

#### Fase 2
```php
Route::name('admin.')->group(function () {
    Route::post('/tasks/{task}/start', [TaskStartController::class, 'store'])->name('tasks.start');
    Route::post('/tasks/{task}/validate', [TaskValidationController::class, 'validate'])->name('tasks.validate');
    Route::post('/tasks/{task}/reopen', [TaskValidationController::class, 'reopen'])->name('tasks.reopen');
    Route::post('/tasks/{task}/media', [TaskMediaController::class, 'store'])->name('tasks.media.store');
    Route::delete('/tasks/{task}/media/{media}', [TaskMediaController::class, 'destroy'])->name('tasks.media.destroy');
});
```

#### Fase 3
```php
// routes/mobile.php (novo arquivo)
Route::middleware(['auth'])->name('mobile.')->group(function () {
    Route::get('/', fn() => redirect('/mobile/hoje'));
    Route::get('/hoje', [TodayController::class, 'index'])->name('today');
    Route::get('/minhas-tarefas', [MyTasksController::class, 'index'])->name('my-tasks.index');
    Route::get('/minhas-tarefas/{task}', [MyTasksController::class, 'show'])->name('my-tasks.show');
    Route::get('/pedidos', [MyRequestsController::class, 'index'])->name('requests.index');
    Route::get('/agenda', [MyCalendarController::class, 'index'])->name('calendar.index');
    Route::get('/comunicacoes', [CommunicationsController::class, 'index'])->name('communications.index');
    Route::get('/mais', [MoreController::class, 'index'])->name('more');
    
    // Direct actions
    Route::post('/tasks/{task}/start', [TaskStartController::class, 'store']);
    Route::post('/tasks/{task}/complete', [TaskCompleteController::class, 'store']);
});
```

---

## 7. PLANO DE SERVICES & ACTIONS

### Novos Actions

#### Fase 1
- `ValidateTicketAction` - validar e fechar pedido
- `GenerateTasksFromTicketAction` - gerar tarefas de pedido
- `CancelTicketAction` - cancelar pedido

#### Fase 2
- `StartTaskAction` - iniciar tarefa
- `ValidateTaskAction` - validar tarefa
- `ReopenTaskAction` - reabrir tarefa
- `AttachTaskMediaAction` - anexar fotos/docs

#### Fase 5
- `CreateTaskFromEventAction` - criar tarefa dentro evento
- `CreateTicketFromEventAction` - criar pedido dentro evento
- `GenerateMeetingMinuteFromEventAction` - gerar ata

#### Fase 6
- `CreateResourceRequestAction` - criar requisição
- `ApproveResourceRequestAction` - aprovar
- `CompleteResourceRequestAction` - entregar/devolver
- `CreateResourceRequestFromSpaceAction` - requisição de manutenção

#### Fase 9
- `ArchiveOperationalPlanAction` - arquivar plano

#### Fase 10
- `CreateConversationChannelAction` - criar canal
- `AddConversationMemberAction` - adicionar membro
- `SendConversationMessageAction` - enviar mensagem

### Novos Services

#### Fase 1
- `TicketStateTransitionService` - máquina de estados
- `TicketResolutionService` - lógica de resolução

#### Fase 2
- `TaskStateTransitionService` - máquina de estados
- `TaskProgressService` - calcular progresso
- `TaskMediaService` - gerenciar mídia

#### Fase 4
- `OperationalDashboardService` - agregar dados reais

#### Fase 5
- `EventToMeetingMinuteService` - gerar ata estruturada

#### Fase 6
- `ResourceRequestService` - lógica de requisições
- `SpaceMaintenanceService` - requisitar manutenção

#### Fase 7
- `InventoryAlertService` - alertas de stock

#### Fase 8
- `EmployeeAvailabilityService` - consultar disponibilidade

#### Fase 9
- `OperationalPlanProgressService` - calcular progresso

#### Fase 10
- `ConversationChannelService` - gerenciar canais
- `ConversationNotificationService` - notificar mensagens

---

## 8. PLANO DE PÁGINAS REACT/INERTIA

### Novos Components

#### Fase 1
- `Admin/Tickets/Show.tsx` - adicionar UI para validação

#### Fase 2
- `Admin/Tasks/Show.tsx` - refatorar com validação
- `Admin/Tasks/Mobile.tsx` - versão mobile focada

#### Fase 3 (Mobile)
- `Mobile/Layout.tsx` - layout base
- `Mobile/Today/Index.tsx`
- `Mobile/MyTasks/Index.tsx`, `Show.tsx`, `StartFlow.tsx`, `CompleteFlow.tsx`
- `Mobile/MyRequests/Index.tsx`
- `Mobile/MyCalendar/Index.tsx`
- `Mobile/Communications/Index.tsx`
- `Mobile/More/Index.tsx`

#### Fase 4
- `Admin/Dashboard/Index.tsx` - refatorar com cards clicáveis
- `Admin/OperationalDashboard/Index.tsx` - novo dashboard executivo

#### Fase 5
- `Admin/Events/Show.tsx` - adicionar botões para criar tarefas/pedidos/ata

#### Fase 6
- `Admin/ResourceRequests/Index.tsx`
- `Admin/ResourceRequests/Create.tsx`
- `Admin/ResourceRequests/Show.tsx`

#### Fase 7
- `Admin/Inventory/Dashboard.tsx`
- `Admin/Inventory/Stock.tsx`
- `Admin/Inventory/Loans.tsx`
- `Admin/Inventory/Movements.tsx`
- `Admin/Inventory/Alerts.tsx`

#### Fase 8
- `Admin/Hr/Availability.tsx` - calendário de disponibilidade

#### Fase 9
- `Admin/OperationalPlans/Show.tsx` - adicionar progresso visual

#### Fase 10
- `Admin/Conversations/Channels.tsx`
- `Admin/Conversations/Channel.tsx` - chat interface

---

## 9. PLANO DE POLICIES & PERMISSIONS

### Novos Permissions (Spatie)

#### Fase 1
- `validate-tickets` - validar pedidos
- `cancel-tickets` - cancelar pedidos
- `generate-ticket-tasks` - gerar tarefas de pedido

#### Fase 2
- `start-tasks` - iniciar tarefas
- `validate-tasks` - validar tarefas
- `reopen-tasks` - reabrir tarefas
- `attach-task-media` - anexar mídia

#### Fase 3
- `access-mobile-app` - acesso ao modo terreno

#### Fase 5
- `create-event-tasks` - criar tarefas dentro evento
- `create-event-requests` - criar pedidos dentro evento
- `generate-meeting-minutes` - gerar atas

#### Fase 6
- `create-resource-requests` - criar requisições
- `approve-resource-requests` - aprovar requisições
- `manage-resource-requests` - gerir requisições

#### Fase 9
- `archive-operational-plans` - arquivar planos

#### Fase 10
- `create-channels` - criar canais
- `manage-channels` - gerir canais
- `send-messages` - enviar mensagens

### Novas Policies

- `ResourceRequestPolicy.php` (Fase 6)
- `ConversationChannelPolicy.php` (Fase 10)
- `ConversationMessagePolicy.php` (Fase 10)

### Permissões por Papel

**Executivo:**
- view-operational-dashboard
- validate-tickets, validate-tasks
- approve-resource-requests
- approve-operational-plans
- manage-channels

**Responsável de Departamento:**
- create-tickets
- assign-tasks, validate-tasks
- approve-space-reservations
- approve-resource-requests
- create-channels (departamento)

**Técnico/Terreno:**
- access-mobile-app
- start-tasks, complete-tasks, reopen-tasks
- attach-task-media
- create-resource-requests

**Secretaria/Admin:**
- (all existing permissions)

---

## 10. PLANO DE TESTES

### Novos Testes

#### Fase 1
- `Tests/Feature/Sprint1X/TicketTypeAndStatusTest.php`
- `Tests/Feature/Sprint1X/TicketValidationFlowTest.php`
- `Tests/Feature/Sprint1X/TicketTaskGenerationTest.php`

#### Fase 2
- `Tests/Feature/Sprint1X/TaskStateTransitionTest.php`
- `Tests/Feature/Sprint1X/TaskValidationFlowTest.php`
- `Tests/Feature/Sprint1X/TaskMediaAttachmentTest.php`

#### Fase 3
- `Tests/Feature/Sprint1X/MobileTaskFlowTest.php`
- `Tests/Feature/Sprint1X/MobileTodayViewTest.php`

#### Fase 4
- `Tests/Feature/Sprint1X/OperationalDashboardServiceTest.php`
- `Tests/Feature/Sprint1X/DashboardCardsClickabilityTest.php`

#### Fase 5
- `Tests/Feature/Sprint1X/EventTaskCreationTest.php`
- `Tests/Feature/Sprint1X/MeetingMinuteGenerationTest.php`

#### Fase 6
- `Tests/Feature/Sprint1X/ResourceRequestFlowTest.php`
- `Tests/Feature/Sprint1X/ResourceRequestMorphabilityTest.php`

#### Fase 7
- `Tests/Feature/Sprint1X/InventoryAlertTest.php`
- `Tests/Feature/Sprint1X/InventoryLoanTrackingTest.php`

#### Fase 8
- `Tests/Feature/Sprint1X/EmployeeAvailabilityTest.php`

#### Fase 9
- `Tests/Feature/Sprint1X/OperationalPlanProgressTest.php`
- `Tests/Feature/Sprint1X/OperationalPlanAutoUpdateTest.php`

#### Fase 10
- `Tests/Feature/Sprint1X/ConversationChannelTest.php`
- `Tests/Feature/Sprint1X/ConversationPermissionTest.php`

---

## 11. ORDEM RECOMENDADA DE IMPLEMENTAÇÃO

### Recomendação Estratégica

```
SEMANA 1-2: FASE 1 (Pedidos/Ocorrências)
├─ Migrations para tickets (tipo, validação)
├─ Models Ticket (relações, scopes)
├─ Actions: ValidateTicketAction, GenerateTasksFromTicketAction
├─ Controllers: TicketValidationController, TicketTaskGenerationController
├─ Routes e UI
├─ Testes
└─ Deploy

SEMANA 3-4: FASE 2 (Motor Operacional - Tasks)
├─ Migrations para tasks (operacional_plan_id, event_id, validação)
├─ Models Task (relações, estados)
├─ Create TaskMedia model/table
├─ Actions: StartTaskAction, ValidateTaskAction, ReopenTaskAction
├─ Controllers: TaskStartController, TaskValidationController, TaskMediaController
├─ Routes e UI
├─ Testes
└─ Deploy

SEMANA 5-6: FASE 3 (Mobile / Modo Terreno)
├─ Criar routes/mobile.php
├─ Mobile Controllers (TodayController, MyTasksController, etc)
├─ Mobile Layout & Components
├─ Mobile Task Flow (start, complete, upload)
├─ Testes
└─ Deploy

SEMANA 7: FASE 4 (Dashboard Executivo)
├─ OperationalDashboardService
├─ Refactor Admin Dashboard
├─ Cards clicáveis com contexto
├─ Testes
└─ Deploy

SEMANA 8: FASE 5 (Agenda Evoluída)
├─ Migrations para events (permitir criação de tarefas, atas)
├─ Create event_tasks pivot
├─ Actions: CreateTaskFromEventAction, GenerateMeetingMinuteFromEventAction
├─ Controllers e UI
├─ EventToMeetingMinuteService
├─ Testes
└─ Deploy

SEMANA 9-10: FASE 6 (Espaços & Requisições)
├─ Create ResourceRequest + ResourceRequestItem models
├─ Migrations para resource_requests
├─ Alter SpaceReservation para permissões
├─ Actions e Controllers para requisições
├─ UI para criar requisições em contexto
├─ Testes
└─ Deploy

SEMANA 11: FASE 7 (Recursos Materiais - Visão Separada)
├─ InventoryAlertService
├─ Inventory Dashboard
├─ Separar Stock, Loans, Movements em tabs
├─ Testes
└─ Deploy

SEMANA 12: FASE 8 (HR Availability)
├─ EmployeeAvailabilityService
├─ Calendário de disponibilidade
├─ Consultas de ausências/férias
├─ Testes
└─ Deploy

SEMANA 13: FASE 9 (Planeamento - Evolução)
├─ Migrations para status expandidos
├─ OperationalPlanProgressService
├─ Auto-update em evento listener
├─ UI de progresso visual
├─ Testes
└─ Deploy

SEMANA 14-15: FASE 10 (Comunicação Interna)
├─ Create Conversation models
├─ Migrations
├─ Actions e Services
├─ Controllers e UI
├─ Canais morpháveis
├─ Testes
└─ Deploy
```

### Prioridades Críticas
1. **Fase 1-2:** Core do Centro Operacional (pedidos + tarefas)
2. **Fase 3:** Mobile para terreno (essencial)
3. **Fase 4:** Dashboard (visibilidade executiva)
4. **Fases 5-10:** Incremental (reforço de funcionalidades)

### Dependências entre Fases
```
Fase 1 (Tickets) 
   ↓
Fase 2 (Tasks) ← Fase 1 cria tarefas de tickets
   ↓
Fase 3 (Mobile) ← Usa Tasks + Tickets
   ↓
Fase 4 (Dashboard) ← Agrega Tickets + Tasks
   ↓
Fase 5 (Agenda) ← Pode criar Tasks + Tickets
   ↓
Fase 6 (Resources) ← Morphable para Space + Event + Task + Plan + Ticket
   ↓
Fases 7-10 ← Incrementais
```

---

## 12. RISCO & DEPENDÊNCIAS

### Riscos Identificados

| Risco | Impacto | Mitigação |
|-------|---------|-----------|
| Estado (status) ambíguo em transições | Alto | Implementar StateMachine (Spatie) ou máquina manual com validações rigorosas |
| Relações morphables complexas | Médio | Teste de permissões + constraints de BD |
| Performance com queries complexas | Médio | Eager loading, indexes, cache estratégico |
| Mobile offline capability | Médio | Phase incremental, sincronização lazy |
| Múltiplos canais de comunicação | Alto | Migração gradual, suporte ao WhatsApp em parallel |

### Dependências Externas

- ✓ Laravel 11 (já está)
- ✓ Inertia.js (já está)
- ✓ React + TypeScript (já está)
- ✓ Spatie Permissions (já está)
- ✓ Tailwind CSS (já está)
- **Consideração:** Possível State Machine library (Asana/Workflow)
- **Consideração:** Real-time notifications (Pusher/Laravel Echo)

### Pontos de Atenção

1. **Compatibilidade Retroativa:**
   - Portal do munícipe deve continuar funcional
   - Existing APIs não devem quebrar
   - Migrations reversíveis (considerar down scripts)

2. **Multi-Tenancy:**
   - organization_id em TODAS as queries
   - Policies incluem organização
   - Testes com múltiplas organizações

3. **Performance:**
   - Indexar status, created_at, organization_id
   - Avoid N+1 queries
   - Cache dashboards

4. **UX Mobile:**
   - Teste em dispositivos reais
   - Connection handling (offline)
   - Tamanho de imagens/uploads

---

## RESUMO EXECUTIVO

### O que Muda

| Aspecto | Antes | Depois |
|--------|-------|--------|
| **Foco** | Portal do munícipe | Centro Operacional interno |
| **Tickets** | Apenas do portal | Interno + externo + ocorrências |
| **Tasks** | Desconexas | Linked a pedidos, planos, eventos |
| **Agenda** | Apenas eventos | Hub de tudo: reuniões, tarefas, ausências |
| **UX** | Desktop admin | Admin + Mobile terreno |
| **Comunicação** | Email + WhatsApp | Canais contextuais integrados |

### Reutilização Comprovada

- ✓ **Modelos:** 0 novos models essenciais (apenas 4 para comunicação)
- ✓ **Tabelas:** +7 novas (tickets, tasks, resources, conversation)
- ✓ **Controllers:** +20 novos (maioria ligeira)
- ✓ **Actions:** +25 novas (padrão bem estabelecido)
- ✓ **Services:** +12 novas
- ✓ **React Pages:** +30 novas (componentes + mobile)

### Próximos Passos

1. ✅ **Análise Estruturada** (CONCLUÍDA)
2. ⏳ **Validação com Stakeholders** (próxima)
3. ⏳ **Começar Fase 1** (Tickets → Pedidos)
4. ⏳ **Iteração contínua**

---

**Preparado por:** GitHub Copilot  
**Para:** Centro Operacional da Junta - ERP OS  
**Data:** Maio 7, 2026  
**Status:** Pronto para Aprovação & Implementação
