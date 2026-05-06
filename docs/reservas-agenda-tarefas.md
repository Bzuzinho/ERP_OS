# Sprint 17: Reservas, Agenda e Tarefas Ligadas

## Visão Geral

Este documento descreve a integração entre os módulos de Reservas de Espaços, Agenda de Eventos e Tarefas no JuntaOS. O objetivo é consolidar o fluxo de trabalho desde o pedido de reserva até a sua aprovação, passando pela criação automática de eventos de agenda e tarefas internas de preparação e limpeza.

## Fluxo Principal

### 1. Pedido de Reserva (Portal do Munícipe)

O munícipe ou associação acede ao Portal do JuntaOS e solicita uma reserva de espaço:

- **Acesso**: `portal.space-reservations.create`
- **Dados**: Espaço, data/hora início, data/hora fim, finalidade, observações
- **Resultado**: Reserva criada com status `requested`
- **Segurança**: Apenas pode reservar espaços públicos da sua organização

```
POST /portal/space-reservations
{
  "space_id": 123,
  "start_at": "2026-05-10 10:00:00",
  "end_at": "2026-05-10 12:00:00",
  "purpose": "Reunião de departamento",
  "notes": "Observações públicas"
}
```

### 2. Revisão pela Administração

Um administrador vê a reserva pendente na administração:

- **Acesso**: `admin.space-reservations.index` (lista) / `admin.space-reservations.show` (detalhe)
- **Dados Visíveis**: 
  - Requerente
  - Espaço e localização
  - Data/hora
  - Finalidade
  - Observações públicas
  - Histórico de aprovações

### 3. Aprovação da Reserva

Quando o admin aprova uma reserva, o sistema executa automaticamente:

#### a. Validação
- Verifica se existe conflito com outra reserva aprovada no mesmo período
- Se conflito encontrado, rejeita com mensagem clara

#### b. Atualização do Status
- Reserva passa para `approved`
- Regista `approved_by`, `approved_at`

#### c. Criação de Evento
- Novo evento criado automaticamente na Agenda
- **Dados do Evento**:
  - `title`: "Reserva de Espaço: {nome_do_espaço}"
  - `description`: Finalidade da reserva
  - `event_type`: "reservation"
  - `status`: "confirmed"
  - `start_at`, `end_at`: Mesmos da reserva
  - `space_id`: Ligação direta ao espaço
  - `visibility`: "internal" (não visível no portal por defeito)
  - `location_text`: Localização do espaço

#### d. Criação de Tarefas Internas
- **Tarefa 1 - Preparação**:
  - `title`: "Preparar espaço: {nome_do_espaço}"
  - `description`: "Preparar o espaço para a reserva: {finalidade}"
  - `due_date`: Dia anterior à reserva, hora anterior ao início
  - `status`: "pending"
  - `priority`: "normal"
  - `assigned_to`: Gestor do espaço (se existir)
  
- **Tarefa 2 - Limpeza**:
  - `title`: "Limpeza após reserva: {nome_do_espaço}"
  - `description`: "Proceder à limpeza do espaço após a reserva: {finalidade}"
  - `due_date`: Dia da reserva, 2 horas após o fim
  - `status`: "pending"
  - `priority`: "normal"
  - `assigned_to`: Gestor do espaço (se existir)

#### e. Criação de Registo de Limpeza (se aplicável)
- Se o espaço tem `has_cleaning_required = true`, um registo de limpeza é criado

#### f. Notificações
- **Para o requerente**: "Sua reserva foi aprovada"
- **Para gestores de espaço**: Notificação de nova tarefa (opcional)

### 4. Rejeição da Reserva

Quando o admin rejeita uma reserva:

- Reserva passa para `rejected`
- Regista `rejected_by`, `rejected_at`, `rejection_reason`
- **Notificação enviada ao requerente**: Motivo da rejeição
- Nenhum evento ou tarefa criados

### 5. Cancelamento da Reserva

Quando uma reserva aprovada é cancelada:

- Reserva passa para `cancelled`
- Regista `cancelled_by`, `cancelled_at`, `cancellation_reason`
- **Evento associado**: Status alterado para `cancelled` (não é eliminado)
- **Tarefas associadas**: Status alterado para `cancelled` (não são eliminadas)
- **Notificações**:
  - Requerente é notificado
  - Gestores de espaço são notificados

## Modelos de Dados

### SpaceReservation

```php
id
organization_id          // Isolamento por organização
space_id
requested_by_user_id     // User que criou a reserva
contact_id               // Contact (opcional, para contato de empresa/associação)
event_id                 // LIGAÇÃO: Evento criado automáticamente
status                   // requested, approved, rejected, cancelled, completed
start_at
end_at
purpose
notes                    // Observações públicas
internal_notes           // Observações internas (não vistas no Portal)
approved_by
approved_at
rejected_by
rejected_at
rejection_reason
cancelled_by
cancelled_at
cancellation_reason
created_at
updated_at
deleted_at               // SoftDeletes
```

### Event

```php
id
organization_id
space_id                 // NOVO: Ligação direta ao espaço
title
description
event_type               // 'reservation' quando criado de uma reserva
status                   // scheduled, confirmed, cancelled, completed
start_at
end_at
location_text
created_by
related_ticket_id
related_contact_id
visibility               // public, internal, restricted
created_at
updated_at
deleted_at
```

### Task

```php
id
organization_id
ticket_id                // Para tarefas de tickets
space_reservation_id     // NOVO: Ligação para reserva (tarefas internas)
assigned_to
created_by
title
description
status                   // pending, in_progress, waiting, done, cancelled
priority                 // low, normal, high, urgent
start_date
due_date
completed_at
completed_by
created_at
updated_at
deleted_at
```

### Relationships

**SpaceReservation**:
```php
belongsTo(Event::class)           // event_id
hasMany(Task::class)              // Tarefas internas
```

**Event**:
```php
belongsTo(Space::class)           // space_id
hasMany(SpaceReservation::class)  // Reservas (agora pode ser 1:1 ou 1:n)
```

**Task**:
```php
belongsTo(SpaceReservation::class) // space_reservation_id
```

## Serviços e Actions

### Actions

#### ApproveSpaceReservationAction
- Valida disponibilidade do espaço
- Atualiza status para `approved`
- Cria evento via `SpaceReservationService::ensureReservationEvent()`
- Cria tarefas via `SpaceReservationService::createReservationTasks()`
- Cria registo de limpeza se aplicável
- Envia notificações
- Regista activity log

#### RejectSpaceReservationAction
- Atualiza status para `rejected`
- Envia notificação ao requerente
- Regista activity log

#### CancelSpaceReservationAction
- Atualiza status para `cancelled`
- Cancela evento associado
- Cancela tarefas associadas
- Envia notificações
- Regista activity log

### Serviços

#### SpaceReservationService

**Métodos**:
- `ensureReservationEvent(SpaceReservation, User)` - Cria/retorna evento
- `cancelReservationEvent(SpaceReservation, User)` - Cancela evento
- `ensureCleaningRecord(SpaceReservation, User)` - Cria registo de limpeza
- `createReservationTasks(SpaceReservation, User)` - Cria tarefas internas
- `cancelReservationTasks(SpaceReservation, User)` - Cancela tarefas

#### SpaceReservationNotificationService

**Métodos**:
- `notifyReservationRequested(SpaceReservation, User)` - Nova reserva pendente
- `notifyReservationApproved(SpaceReservation, User)` - Reserva aprovada
- `notifyReservationRejected(SpaceReservation, User)` - Reserva rejeitada
- `notifyReservationCancelled(SpaceReservation, User)` - Reserva cancelada
- `notifyTaskCreated(Task, User)` - Nova tarefa

#### SpaceAvailabilityService

Já existente. Usado para validar conflitos:
- `hasApprovedConflict(int $spaceId, datetime $start, datetime $end, ?int $ignoreReservationId)` - Verifica se existe conflito

## Segurança e Isolamento

### Organização (Multi-tenancy)
- Todas as operações são scoped à `organization_id`
- Usuários só veem dados da sua organização (exceto `super_admin`)
- Espaços de outras organizações não podem ser reservados

### Roles e Permissões
- `cidadao`, `associacao`, `empresa`: Podem criar reservas no Portal
- `admin`, `administrativo`: Podem aprovar/rejeitar/cancelar reservas
- `super_admin`: Bypass de todas as restrições

### Visibilidade no Portal
Portal **não mostra**:
- Tarefas internas
- Notas internas
- Nomes de responsáveis internos
- Eventos internos (salvo se `visibility = public`)
- Dados de aprovação detalhados (apenas timeline de ações)

Portal **mostra**:
- Estado público: Pendente, Aprovado, Rejeitado, Cancelado, Concluído
- Finalidade (propósito)
- Observações públicas
- Timeline de ações (aprovado, rejeitado, cancelado)

## Notificações

### Tipos de Notificação

1. **reservation_requested** (Nova reserva)
   - **Para**: Admins, gestores de espaço
   - **Mensagem**: "Nova reserva solicitada para {espaço} em {data/hora}"
   - **Prioridade**: Alta

2. **reservation_approved** (Aprovação)
   - **Para**: Requerente
   - **Mensagem**: "Sua reserva para {espaço} foi aprovada"
   - **Prioridade**: Alta

3. **reservation_rejected** (Rejeição)
   - **Para**: Requerente
   - **Mensagem**: "Sua reserva foi rejeitada. Motivo: {motivo}"
   - **Prioridade**: Alta

4. **reservation_cancelled** (Cancelamento)
   - **Para**: Requerente, gestores de espaço
   - **Mensagem**: "A reserva para {espaço} foi cancelada"
   - **Prioridade**: Alta

5. **task_created** (Nova tarefa)
   - **Para**: Responsável da tarefa
   - **Mensagem**: "Tarefa: {título}"
   - **Prioridade**: Normal

### Destinatários Automáticos

- **Requerente**: User indicado em `requested_by_user_id` ou `contact.user_id`
- **Gestor de espaço**: `space.managed_by` (se existir)
- **Admin**: Users com permissão `spaces.approve_reservation`

## Validação e Regras

### Conflitos de Horário
- Não é possível aprovar uma reserva se existir outra **aprovada** no mesmo horário
- Sobrecarga: `start < other_end AND end > other_start`
- Estados considerados bloqueadores: `approved`, `confirmed`, `scheduled`
- Estados não bloqueadores: `requested`, `rejected`, `cancelled`, `completed`

### Permissões
- **Criar reserva (Portal)**: `spaces.reserve` ou roles `cidadao`, `associacao`, `empresa`
- **Aprovar reserva**: `spaces.approve_reservation`
- **Rejeitar reserva**: `spaces.approve_reservation`
- **Cancelar reserva**: `spaces.cancel_reservation`
- **Ver reserva (Admin)**: `spaces.view` ou `spaces.approve_reservation`
- **Ver reserva (Portal)**: Apenas se é requerente ou contact

## Compatibilidade e Dados

### Compatibilidade de Banco de Dados
- PostgreSQL/Neon ✓
- SQLite (desenvolvimento local) ✓

### Migrações Criadas
1. `2026_05_06_add_space_id_to_events_table.php`
   - Adiciona campo `space_id` (foreignId) a `events`
   - Nullable, com `nullOnDelete`

2. `2026_05_06_add_space_reservation_id_to_tasks_table.php`
   - Adiciona campo `space_reservation_id` (foreignId) a `tasks`
   - Nullable, com `nullOnDelete`

### Reuso de Código Existente
- `SpaceAvailabilityService` - Validação de conflitos já existia
- `ApproveSpaceReservationAction` - Action existia, estendida com tarefas e notificações
- `RejectSpaceReservationAction` - Action existia, adicionadas notificações
- `CancelSpaceReservationAction` - Action existia, adicionada cancela ção de tarefas e notificações
- `NotificationService` - Existente, reutilizado via `SpaceReservationNotificationService`
- `ActivityLogger` - Existente, reutilizado para audit trail

## Exemplo de Uso

### 1. Portal User cria pedido
```php
$reservation = $action->execute($user, [
    'space_id' => 123,
    'start_at' => '2026-05-10 10:00:00',
    'end_at' => '2026-05-10 12:00:00',
    'purpose' => 'Reunião',
    'notes' => 'Obs públicas'
]);
// Resultado: SpaceReservation com status 'requested'
```

### 2. Admin aprova
```php
$action->execute($reservation, $adminUser, 'Notas aprovação');
// Resultado:
// - Reserva: status = 'approved'
// - Evento: criado com type 'reservation'
// - Tarefas: 2 criadas (preparação e limpeza)
// - Notificações: enviadas
```

### 3. Admin cancela
```php
$action->execute($reservation, $adminUser, 'Motivo do cancelamento', 'Notas');
// Resultado:
// - Reserva: status = 'cancelled'
// - Evento: status = 'cancelled'
// - Tarefas: status = 'cancelled'
// - Notificações: enviadas
```

## Testes

Consulte `tests/Feature/SpaceReservations/SpaceReservationFlowTest.php` para:
- Cenários de criação de reserva
- Validação de conflitos
- Fluxo de aprovação/rejeição/cancelamento
- Criação de eventos e tarefas
- Segurança de isolamento por organização
- Notificações
- Restrições de acesso do Portal

## Desenvolvimento Futuro

Possíveis melhorias:
1. Webhook para integração com sistemas externos
2. Recorrência de reservas
3. Aprovação em múltiplos níveis
4. Templates de tarefas por tipo de espaço
5. Relatórios de utilizações de espaços
6. Sincronização com calendários externos (Google Calendar, Outlook)
7. Autocompletar tarefas baseado em localização GPS

## Suporte e Troubleshooting

### Problema: Reserva não é aprovada mesmo sem conflito
**Solução**: Verificar logs de `ActivityLog`. Possível erro de permissões ou organização.

### Problema: Tarefas não são criadas
**Solução**: Verificar se `SpaceReservationService::createReservationTasks()` está sendo chamada em `ApproveSpaceReservationAction`.

### Problema: Notificações não chegam
**Solução**: Verificar `Notification` e `NotificationRecipient`. Pode ser que destinatários não estejam corretos ou que o queue esteja parado.

### Problema: Portal user vê tarefas internas
**Solução**: Verificar se Portal está filtrando corretamente. Portal deve usar queries que limitam a `space_reservation_id = null` ou usar policy adequada.
