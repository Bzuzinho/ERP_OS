# Sprint 14 - Navigation Structure

## Admin structure

Admin navigation is internal/operational and can expose:
- Dashboard
- Pedidos
- Municipes / Entidades
- Agenda
- Tarefas
- Documentos
- Atas
- Espacos
- Recursos Materiais
- Recursos Humanos
- Planeamento
- Relatorios
- Configuracoes (only when user has accessSettings)

Mobile (bottom nav):
- Dashboard
- Pedidos
- Agenda
- Tarefas
- Mais

Rules:
- Settings entry points to `admin.settings.index`.
- In mobile More menu, Settings only appears when `auth.can.accessSettings` is true.

## Portal structure

Portal navigation is external-facing and exposes only citizen-facing modules:
- Inicio
- Pedidos
- Agenda
- Reservas
- Documentos
- Notificacoes
- Perfil
- Mais

Mobile (bottom nav):
- Inicio
- Pedidos
- Agenda
- Notificacoes
- Mais

Rules:
- Do not expose internal modules in Portal navigation (e.g. tarefas internas, RH, inventario, planeamento interno, configuracoes de administracao).
- Do not reference nonexistent routes such as `portal.tasks.*`.

## Canonical service areas route

Canonical visual route namespace:
- `admin.settings.service-areas.*`

Used in:
- Settings cards
- Service Areas pages (index/create/show/edit)
- Controller redirects after create/update/delete

## Legacy routes preserved

Legacy namespace preserved for compatibility:
- `admin.service-areas.*`

Behavior:
- Legacy GET routes redirect to canonical settings routes.
- Legacy POST/PUT/DELETE routes remain mapped to the same controller actions.
- No duplicate controller or duplicate page was introduced.

## Criteria for Admin vs Portal modules

Use Admin when the feature is internal management/operations.
Use Portal when the feature is external relationship with citizen/entity.

Portal should prioritize simple language:
- Pedidos
- Agenda
- Reservas
- Documentos
- Notificacoes
- Perfil
