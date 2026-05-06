# Auditoria JuntaOS — Administração e Portal

Data da auditoria: 2026-05-06  
Âmbito: análise técnica e funcional em modo read-only (sem alterações de código), com base em rotas, páginas Inertia React, controllers, requests, services, policies, seeders e testes.

## 1. Resumo executivo
- Estado geral: a aplicação apresenta boa base arquitetural (Laravel + Inertia + TypeScript + Spatie), com separação clara entre Administração e Portal ao nível de layout, páginas e parte da lógica.
- Principais conclusões:
  - A cobertura funcional é elevada no lado Administração (módulos principais com CRUD e ações operacionais).
  - O Portal está funcional para cenários essenciais (pedidos, agenda, documentos, reservas), mas com lacunas de simplicidade, consistência e isolamento de dados por organização em alguns fluxos.
  - Há incoerências de navegação (especialmente mobile no Portal) e duplicação de rotas de áreas funcionais.
  - Há riscos críticos de segurança/permissões (P0) relacionados com ausência de filtro organization_id em controladores específicos e validações insuficientes de pertença organizacional em tickets.
- Risco global: Médio-Alto (por causa de riscos P0 de isolamento de dados).
- Maturidade do produto: Média-Alta em backend e modelação; Média em UX/UI global; Média em testes automatizados (boa base, cobertura desigual por módulo).

## 2. Administração
### Pontos fortes
- Navegação principal completa por domínio: pedidos, agenda, tarefas, documentos, espaços, inventário, RH, planeamento, relatórios, configurações.
- Forte cobertura de páginas e fluxos CRUD na maioria dos módulos críticos.
- Uso consistente de componentes comuns (layout, cards, badges, flash messages, headers).
- Serviços dedicados de dashboard e relatórios por domínio.

### Problemas
- TicketController admin com queries sem filtro explícito por organização em listagem e dropdowns de utilizadores/contactos (potencial fuga cross-org).
- Duplicação de rotas para áreas funcionais:
  - admin.service-areas.*
  - admin.settings.service-areas.*
- Inconsistências de navegação/ícones (ex.: Dashboard e Relatórios com o mesmo ícone).
- Algumas páginas com semântica incompleta no ciclo create/edit/show em submódulos (casos pontuais e intencionais nuns módulos transacionais).

### Oportunidades
- Consolidar a gestão de áreas funcionais exclusivamente em Configurações.
- Aplicar escopo organizacional de forma centralizada (query scopes ou policies mais estritas por organização).
- Melhorar UX mobile da área admin para listas densas e filtros.

### Prioridades
- P0: correção de isolamento organizacional em tickets e outros controladores sem scoping.
- P1: normalização de rotas e navegação; clarificação da arquitetura de settings.
- P2: consistência visual e de ações por módulo.

## 3. Portal
### Pontos fortes
- Fluxos essenciais implementados: pedidos (criar/acompanhar), eventos, documentos, reservas e notificações.
- Separação de comentários/anexos públicos vs internos no detalhe de ticket do Portal.
- Dashboard dedicado com KPIs orientados ao munícipe (pedidos ativos, eventos, reservas, documentos).

### Problemas
- Navegação mobile com item Tarefas a apontar para dashboard (incoerente e potencialmente enganador).
- Controladores Portal com ausência de filtro organization_id em alguns módulos (documentos, eventos, espaços, atas, planos operacionais).
- Portal demasiado próximo do paradigma interno em alguns termos/estruturas (linguagem e arquitetura de informação ainda pouco "munícipe-first").
- Falta de redução de complexidade e de copy mais orientada ao utilizador final não técnico.

### Oportunidades
- Simplificar topologia do Portal para 4-5 ações nucleares.
- Reforçar isolamento por organização e por relação efetiva do utilizador aos dados.
- Reescrever labels e mensagens para linguagem de serviço público.

### Prioridades
- P0: isolamento de dados no Portal por organização.
- P1: correção da navegação mobile e simplificação de fluxos de acompanhamento de pedido.
- P2: melhorias de linguagem, empty states e guidance contextual.

## 4. Navegação
### Tabela de rotas críticas
| Área | Rota | Nome | Estado | Observações |
|---|---|---|---|---|
| Admin Dashboard | /admin | admin.dashboard | Funcional | Serviço dedicado de KPIs |
| Portal Dashboard | /portal | portal.dashboard | Funcional | KPIs orientados ao utilizador |
| Tickets Admin | /admin/tickets | admin.tickets.* | Funcional com risco | Falta de scoping organizacional em queries do controller |
| Tickets Portal | /portal/tickets | portal.tickets.index/create/store/show | Funcional | Sem edit/update (aparentemente intencional) |
| Settings Users | /admin/settings/users | admin.settings.users.* | Funcional | Gestão de estado/roles/reset |
| Settings Roles | /admin/settings/roles | admin.settings.roles.* | Funcional | Sem create dedicado |
| Service Areas (legacy) | /admin/service-areas | admin.service-areas.* | Funcional (duplicado) | Sobreposição com settings |
| Service Areas (settings) | /admin/settings/service-areas | admin.settings.service-areas.* | Funcional (preferível) | Deve ser canónica |
| Notifications Admin | /admin/notifications | admin.notifications.* | Funcional | Leitura e marcar lidas |
| Notifications Portal | /portal/notifications | portal.notifications.* | Funcional | Filtros por estado |
| Reports | /admin/reports/* | admin.reports.* | Funcional | Páginas por domínio existem |

### Rotas funcionais
- A maioria das rotas admin.* e portal.* está funcional e com correspondência de página Inertia.
- Settings, HR, planning e reports têm estrutura consistente de nomeação.

### Rotas órfãs ou potencialmente mal posicionadas
- Duplicação clara entre admin.service-areas.* e admin.settings.service-areas.*.
- Padrão de navegação mobile no Portal inclui activePatterns portal.tasks.* sem rotas portal.tasks.*.

### Links quebrados prováveis
- Portal mobile: item Tarefas aponta para dashboard e não para módulo real.
- Risco de confusão de back routes em páginas relacionadas com áreas funcionais devido à coexistência de namespaces de rota.

### Recomendações
- Tornar admin.settings.service-areas.* o único namespace público para áreas funcionais.
- Introduzir redirecionamentos explícitos de compatibilidade e descontinuar gradualmente rotas legacy.
- Corrigir menu mobile Portal para refletir apenas destinos reais.

## 5. Funcionalidades por módulo

### 5.1 Pedidos/Tickets
- Estado atual do fluxo:
  - Admin: CRUD completo, atribuição, mudança de estado, comentários, anexos, timeline e notificação de criação/atribuição.
  - Portal: index/create/store/show + comentários/anexos; sem edit/update.
- Falhas:
  - Validações Store/Update de ticket aceitam exists sem restrição por organização (contact_id, assigned_to, service_area_id, etc.).
  - TicketController admin index/create/edit/show usa listas e queries sem filtro organizacional uniforme.
  - notifyTicketStatusChanged e notifyTicketCommentAdded estão reservados para sprint futura.
- Fluxo ideal recomendado:
  - Ticket sempre scoped à organização do utilizador autenticado.
  - assigned_to/contact/service_area/departamento/equipa validados dentro da organização.
  - Notificações completas por evento de negócio (criação, atribuição, estado, comentário público/interno).
- Prioridade: P0.

### 5.2 Agenda/Eventos
- Estado atual:
  - Admin: CRUD completo + participantes + estado.
  - Portal: index/show com visibilidade e participação.
- Lacunas:
  - No Portal, EventController sem filtro organization_id explícito.
  - Dependência de regras de visibilidade sem camada adicional de isolamento por organização.
- Risco de duplicação:
  - Eventos, reservas e planeamento coexistem com interseções conceptuais pouco explícitas para o utilizador.
- Prioridade: P0 (isolamento) + P2 (coerência funcional).

### 5.3 Tarefas
- Estado atual:
  - Admin: módulo robusto (CRUD, estado, conclusão, checklists, itens).
  - Portal: não existe módulo próprio (apesar de referência indireta no menu mobile).
- Lacunas:
  - Coerência de comunicação para utilizador final (Portal não deve mostrar tarefas internas, mas menu sugere existência).
- Prioridade: P1.

### 5.4 Documentos e Atas
- Estado atual:
  - Admin: CRUD de documentos, tipos, versões, regras de acesso, download.
  - Portal: index/show/download com política de acesso.
  - Atas: admin com aprovação; portal com leitura de atas aprovadas.
- Riscos:
  - DocumentAccessService não impõe organização explicitamente antes de avaliar visibilidade/rules.
  - Portal DocumentController index não filtra por organização antes do filtro por policy.
  - MeetingMinuteController portal também sem filtro organizacional explícito.
- Melhorias:
  - Impor organization_id em query base + confirmação de organização na policy/serviço.
  - Garantir que regras por role_name/contact_id só operam dentro da organização.
- Prioridade: P0.

### 5.5 Espaços e Reservas
- Estado atual:
  - Admin: espaços + reservas + aprovação/rejeição/completar/cancelar + manutenção + limpeza.
  - Portal: listar espaços, pedir reserva, acompanhar, cancelar.
- Lacunas:
  - SpaceController portal sem filtro organization_id explícito.
  - Integração automática reserva->evento/tarefa de limpeza não é claramente garantida no código analisado.
- Prioridade: P1.

### 5.6 Inventário / Recursos materiais
- Estado atual:
  - Admin: itens, categorias, localizações, movimentos, empréstimos, quebras, reposições e ações de ciclo.
- Pontos de maturidade:
  - Modelo operacional amplo e com ações adequadas para operações de armazém.
- Lacunas:
  - Necessário confirmar por testes automáticos robustos prevenção de stock negativo e conflito de empréstimos.
- Prioridade: P2.

### 5.7 Recursos Humanos
- Estado atual:
  - Admin: funcionários, departamentos, equipas, assiduidade, tipos de ausência, pedidos de férias.
- Riscos/lacunas:
  - Possível duplicação conceptual entre User, Employee e Contact (mapeamentos nem sempre claros para utilizador funcional).
  - Falta de visibilidade clara no dashboard sobre impacto de férias/ausências por equipa/departamento.
- Prioridade: P2.

### 5.8 Planeamento operacional
- Estado atual:
  - Admin: planos operacionais e operações recorrentes com ações de aprovação/cancelamento/conclusão e ligação a recursos/tarefas.
  - Portal: leitura de planos visíveis.
- Riscos:
  - Portal OperationalPlanController sem authorize e sem filtro organization_id explícito.
  - Possível exposição de planos de outras organizações se estado/visibilidade coincidirem.
- Prioridade: P0.

### 5.9 Relatórios
- Estado atual:
  - Páginas de relatório por domínio existem e usam services dedicados.
- Lacunas:
  - Necessária validação de permissões finas por relatório em testes de matriz de roles.
  - Export deve ser validado contra scoping organizacional em todos os datasets.
- Prioridade: P1.

### 5.10 Configurações
- Estado atual:
  - Settings index + users + roles + organization + service areas.
- Problemas:
  - Convivência de estrutura antiga e nova para service areas.
  - Alguns blocos do settings funcionam como placeholders/Em breve.
- Proposta de estrutura final:
  - Configurações
    - Organização
    - Utilizadores
    - Perfis e Permissões
    - Áreas Funcionais
    - Parâmetros globais
    - Notificações
- Prioridade: P1.

### 5.11 Alertas/Notificações
- Estado atual do fluxo:
  - NotificationService cria por utilizador e marca leitura individual/global.
  - Resolver de destinatários para tickets considera atribuição, criador, área funcional, departamento, equipa e participantes.
- Riscos/lacunas:
  - TicketNotificationService ainda sem notificação de mudança de estado/comentário.
  - Necessidade de validar prevenção de duplicados em cenários de multi-critério no resolver.
  - Shared props carregam notificações recentes em todas as páginas autenticadas (avaliar impacto em escala).
- Prioridade: P1.

## 6. Permissões e segurança
### Riscos críticos (P0)
- Ausência de filtro organization_id explícito em controladores críticos:
  - Admin TicketController (queries e listagens auxiliares).
  - Portal DocumentController, EventController, SpaceController, MeetingMinuteController, OperationalPlanController.
- StoreTicketRequest/UpdateTicketRequest validam chaves por existência global e não por organização.
- DocumentAccessService não valida organização no início da decisão.

### Riscos médios (P1)
- Duplicação de rotas de áreas funcionais aumenta risco de inconsistência de autorização/UX.
- Falta de authorize explícito em alguns fluxos Portal (ex.: planos operacionais).
- Dependência elevada de convenções implícitas em vez de guard rails centralizados (scopes globais/multi-tenant).

### Verificações positivas
- Middleware EnsureUserIsActive está registado na stack web.
- Separação admin via middleware permission:admin.access.
- Policies registadas centralmente com Gate::policy para principais modelos.
- NotificationPolicy limita leitura ao próprio recipient.

### Recomendações
- Aplicar regra transversal: toda query de entidade multi-tenant inicia com where organization_id = user.organization_id (exceto super_admin com bypass controlado).
- Endurecer Requests com Rule::exists(...)->where(fn...) para organização.
- Reforçar policies com validação explícita de organização quando aplicável.

## 7. Alertas e responsabilidades
### Fluxo atual
- Criação de notificação por evento de ticket (criação e atribuição).
- Marcação de leitura por utilizador com endpoints distintos admin/portal.

### Melhorias recomendadas
- Completar eventos em falta:
  - ticket.status_changed
  - ticket.comment_added (com distinção público/interno)
- Definir matriz de destinatários por tipo de evento e canal (in-app agora, email/SMS futuro).
- Introduzir idempotência para reduzir duplicados quando utilizador pertence a múltiplos grupos resolvidos.

### Alertas futuros recomendados
- SLA de ticket próximo de vencimento/ultrapassado.
- Reserva com conflito ou pendente há demasiado tempo.
- Stock baixo e rutura iminente.
- Aprovações pendentes (atas, reposições, reservas, férias).

## 8. UI/UX
### Problemas visuais e de usabilidade
- Inconsistência no menu mobile do Portal (Tarefas sem módulo real).
- Duplicação de ícones sem diferenciação semântica em alguns blocos de navegação.
- Linguagem do Portal ainda próxima de backoffice em partes da interface.
- Potenciais tabelas densas em mobile sem evidência suficiente de simplificação contextual por cartão.

### Melhorias rápidas (P1/P2)
- Corrigir menu mobile Portal e labels ambíguas.
- Uniformizar EmptyState com CTA primário contextual (ex.: Criar pedido, Marcar evento).
- Uniformizar cabeçalhos de páginas com ação principal clara por módulo.

### Melhorias estruturais (P2/P3)
- Design system de densidade adaptativa para mobile.
- Revisão de microcopy e terminologia "munícipe-first".
- Normalização de padrões de formulários (erros, ajuda, validação visual em tempo útil).

## 9. Dados demo e relatórios
### Estado atual
- Existe conjunto relevante de seeders (roles/permissions, organização, super admin, demo users e demo data).
- Relatórios por domínio implementados com páginas dedicadas.

### Lacunas
- Necessário validar se dados demo cobrem cenários cruzados completos (ex.: ticket->tarefa->plano->documento->notificação).
- Verificação de export por permissões e escopo organizacional deve ser reforçada por testes.

### Recomendações
- Criar dataset demo orientado a demonstração funcional ponta-a-ponta por jornada:
  - Atendimento munícipe
  - Operação interna
  - Aprovação executiva
  - Auditoria e reporting

## 10. Bugs prováveis
- Exposição de registos de outras organizações no Portal/Admin em módulos sem filtro organization_id explícito.
- Menu mobile Portal com destino incoerente (Tarefas -> dashboard).
- Ambiguidade de roteamento e back navigation por duplicação de service areas.
- Inconsistências de dados selecionáveis em formulários de ticket (contactos/utilizadores fora da organização).

## 11. Funcionalidades em falta
- Notificações de mudança de estado e comentário de ticket (já previstas em serviço, ainda não implementadas).
- Consolidação final da área de Configurações (parâmetros globais ainda parcial/placeholder).
- Possível ausência de fluxo explícito de ligação reserva->evento->tarefas de limpeza/manutenção (dependente de ações internas não totalmente evidenciadas no percurso auditado).
- Testes de segurança e isolamento multi-tenant abrangentes por módulo.

## 12. Recomendações por prioridade
### P0 — bloqueante/crítico
- Aplicar scoping organizacional obrigatório em todos os controladores Portal/Admin multi-tenant.
- Endurecer validações de Requests (exists scoped por organization_id).
- Rever DocumentAccessService e policies para bloquear qualquer acesso cross-org.
- Adicionar testes automáticos de isolamento por organização para tickets, documentos, eventos, espaços, atas e planeamento.

### P1 — importante antes de demonstração
- Corrigir navegação mobile Portal (remover/ajustar Tarefas).
- Consolidar rotas de áreas funcionais em settings e manter aliases legacy apenas com redirecionamento controlado.
- Completar notificações de status/comentários em tickets.
- Garantir consistência de ação principal em PageHeader e estados vazios com CTA.

### P2 — melhoria relevante
- Refinar linguagem do Portal para contexto de munícipe.
- Melhorar densidade/legibilidade mobile em listagens extensas.
- Clarificar fronteiras funcionais entre eventos, reservas e planeamento.
- Reforçar cobertura de testes em inventário, RH, espaços e relatórios.

### P3 — futuro
- Métricas de performance por query (N+1, custo de shared props, caching seletivo).
- Alertas multicanal (email/SMS) e regras avançadas por SLA.
- Evolução de design system com variantes específicas admin vs portal.

## 13. Proposta de próximas sprints
### Sprint A — Segurança e isolamento (P0)
- Objetivo: eliminar risco de exposição cross-org.
- Entregáveis:
  - Scoping organization_id em controladores e services críticos.
  - Request rules com exists scoped.
  - Testes de isolamento por organização (feature tests).

### Sprint B — Navegação e coerência estrutural (P1)
- Objetivo: reduzir fricção de uso e incoerências de IA.
- Entregáveis:
  - Correção menu mobile Portal.
  - Estratégia única para service areas em settings.
  - Revisão de back routes e breadcrumbs.

### Sprint C — Estabilização do Portal do Munícipe (P1)
- Objetivo: simplificar experiência e reforçar confiança.
- Entregáveis:
  - Linguagem e labels orientados ao cidadão.
  - Empty states com CTA claros.
  - Revisão de visibilidade de comentários/anexos e estados de pedido.

### Sprint D — Notificações e responsabilidade operacional (P1)
- Objetivo: completar circuito de comunicação interna/externa.
- Entregáveis:
  - Eventos de notificação para status/comentários.
  - Prevenção de duplicados e matriz de destinatários.
  - Testes de notificações por papel e área funcional.

### Sprint E — Consolidação funcional por domínio (P2)
- Objetivo: fechar lacunas transversais em módulos operacionais.
- Entregáveis:
  - Clarificação eventos vs reservas vs planeamento.
  - Reforço de fluxos de inventário e RH com testes.
  - Validação de relatórios/export com permissões.

### Sprint F — Polimento UX mobile e consistência visual (P2/P3)
- Objetivo: elevar qualidade percebida e eficiência de uso.
- Entregáveis:
  - Ajustes de layout mobile para tabelas/listas.
  - Uniformização de ícones, headers, botões principais e feedback visual.
  - Ajustes finais para demonstração pública.

---

## Anexo A — Estado atual da visualização Administração
- PageHeader: presente de forma consistente na maioria das páginas admin.
- Botão principal: geralmente presente em index (ex.: criar) e ações contextuais em detalhe.
- EmptyState: componente usado em dashboards e várias listagens.
- Desktop: estrutura de sidebar + conteúdo principal sólida.
- Mobile: existe bottom nav simplificada + ecrã Mais; precisa de revisão de densidade em algumas listagens.
- Filtros/pesquisa: presentes em módulos nucleares (tickets, relatórios e outros).
- Ações ver/editar/criar/eliminar: abrangentes no admin.
- Flash/errors: mecanismo partilhado no layout.
- Coesão visual: boa, com algumas incoerências menores de ícones/semântica.

## Anexo B — Estado atual da visualização Portal
- Interface simplificada face ao admin, mas ainda com elementos semânticos internos em alguns pontos.
- Fluxos principais disponíveis: pedidos, eventos, documentos, reservas, notificações.
- Navegação mobile com incoerência relevante (item Tarefas).
- Estrutura geral mobile-first razoável, mas com margem de simplificação da linguagem e priorização de tarefas do munícipe.

## Anexo C — Rotas órfãs, funcionais e riscos de ligação
### Funcionais
- Grande maioria das rotas admin.* e portal.* com página/controller correspondente.

### Órfãs ou incoerentes
- Active pattern portal.tasks.* sem rotas/páginas.
- Namespace duplicado de service areas.

### Links quebrados prováveis
- Navegação que sugere tarefas no Portal sem módulo real.

## Anexo D — Cobertura de testes (síntese)
- Existe cobertura em segurança/smoke, dashboard/reports, tickets, eventos portal, planeamento e notificações.
- Lacunas persistem em cenários de isolamento por organização e em matrizes completas de permissões por módulo.
