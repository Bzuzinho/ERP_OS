# Portal do Munícipe — Documentação

## Objetivo

O Portal do Munícipe é o canal digital de atendimento ao cidadão da Junta de Freguesia. Funciona como um **balcão digital** onde os munícipes podem:

- Submeter pedidos e acompanhar o seu estado
- Consultar a agenda de eventos públicos
- Pesquisar e descarregar documentos publicados
- Pedir reservas de espaços
- Receber alertas sobre as suas interações

## Portal vs Admin — Separação de conceitos

| Conceito | Portal (Cidadão) | Admin (Interno) |
|---|---|---|
| Utilizadores | `cidadao`, `associacao`, `empresa` | `admin_junta`, `administrativo` |
| Pedidos | "Os meus pedidos" | Tickets com prioridade, equipa, departamento |
| Estado de pedido | "Em análise", "Em tratamento", "A aguardar", "Resolvido" | `novo`, `aberto`, `em_execucao`, `pendente`, `resolvido`, `fechado` |
| Comentários | Apenas públicos (`visibility = 'public'`) | Internos + públicos |
| Reservas | Estado simplificado | `requested`, `approved`, `rejected`, `cancelled`, `completed` |
| Notificações | "Alertas" | Notificações internas |
| Documentos | Disponíveis para consulta | Com regras de acesso e visibilidade |
| Agenda | Eventos públicos | Eventos com tipo, contacto relacionado, ticket relacionado |

## Navegação do Portal

```
Dashboard (/)
├── Os meus pedidos (/portal/tickets)
│   ├── Criar pedido (/portal/tickets/create)
│   └── Ver pedido (/portal/tickets/{id})
├── Agenda (/portal/events)
│   └── Ver evento (/portal/events/{id})
├── Documentos (/portal/documents)
│   └── Ver documento (/portal/documents/{id})
├── Reservas (/portal/space-reservations)
│   ├── Pedir reserva (/portal/space-reservations/create)
│   └── Ver reserva (/portal/space-reservations/{id})
├── Espaços (/portal/spaces)
│   └── Ver espaço (/portal/spaces/{id})
├── Alertas (/portal/notifications)
└── Perfil (/profile)
```

## Linguagem pública vs interna

### Estados de pedido (Ticket)

| Estado interno | Rótulo público |
|---|---|
| `novo`, `aberto` | Em análise |
| `em_execucao`, `em_tratamento` | Em tratamento |
| `pendente` | A aguardar informação |
| `resolvido`, `fechado` | Resolvido / Encerrado |

### Estados de reserva (SpaceReservation)

| Estado interno | Rótulo público |
|---|---|
| `requested` | Pedido recebido |
| `approved` | Aprovado |
| `rejected` | Rejeitado |
| `cancelled` | Cancelado |
| `completed` | Concluído |

## Fluxo principal: Criar pedido → Acompanhar → Responder → Resolver

1. **Cidadão cria pedido** (`portal.tickets.create`)
   - Campos: Assunto, Descrição, Localização (opcional)
   - `visibility` fixo: `public`, `source` fixo: `portal`
   - Sem prioridade, sem categoria técnica, sem contacto

2. **Pedido aparece em "Os meus pedidos"** (`portal.tickets.index`)
   - Estado público exibido com `PublicTicketStatusBadge`
   - Cidadão vê apenas os seus próprios pedidos

3. **Cidadão acompanha e responde** (`portal.tickets.show`)
   - Secções: Dados do pedido, Atualizações da Junta, As suas mensagens, Anexos
   - Comentários internos (`visibility = 'internal'`) filtrados — nunca visíveis
   - Linha do tempo com estado público e data (sem nome de técnico)

4. **Pedido resolvido** — estado muda para "Resolvido / Encerrado"
   - Cidadão recebe alerta em `/portal/notifications`

## O que NUNCA deve aparecer no Portal

- Nomes de técnicos/funcionários (assigned_to, decided_by, approved_by)
- Estados técnicos internos (`novo`, `em_execucao`, etc.) em texto direto
- Notas internas (`internal_notes`, comentários com `visibility = 'internal'`)
- Identificadores de departamento, equipa, área de serviço
- Prioridades técnicas (low, normal, high, urgent)
- Tipos de evento internos (`event_type`)
- Regras de acesso a documentos (`DocumentAccessRule`)
- Registos de manutenção/limpeza de espaços

## Componentes públicos (Portal/)

| Componente | Ficheiro | Uso |
|---|---|---|
| `PublicTicketStatusBadge` | `Components/Portal/PublicTicketStatusBadge.tsx` | Badge de estado de pedido |
| `PublicReservationStatusBadge` | `Components/Portal/PublicReservationStatusBadge.tsx` | Badge de estado de reserva |
| `PublicReservationTimeline` | `Components/Portal/PublicReservationTimeline.tsx` | Linha do tempo de reserva sem decisor |
| `publicTicketStatus.ts` | `Components/Portal/publicTicketStatus.ts` | Helpers de mapeamento de estados |

## Isolamento multi-tenant

- Todos os dados são filtrados por `organization_id` via `OrganizationScope`
- Portal users só veem os seus próprios tickets e reservas (filtro `created_by`)
- Documentos e eventos públicos são partilhados dentro da organização
- Roles do Portal: `cidadao`, `associacao`, `empresa`

## Segurança — Backend controllers

Os controllers do Portal (`app/Http/Controllers/Portal/`) limitam explicitamente os campos retornados:

- `TicketController::show()` — sem `assigned_to`, `department_id`, `team_id`, `priority`
- `SpaceReservationController::show()` — sem `internal_notes`, `approved_by`, `rejected_by`, `cancelled_by`
- `EventController::index/show()` — sem `event_type`, `related_contact_id`, `related_ticket_id`
- `DocumentController` — sem campos de regras de acesso internas
- `SpaceController` — sem `maintenance_records`, `cleaning_records`

## URLs de notificação

As notificações geradas pelo sistema usam `action_url` com rotas admin. O middleware `HandleInertiaRequests` converte automaticamente estas URLs para rotas Portal quando o utilizador autenticado é um portal user (método `resolvePortalFriendlyUrl()`).
