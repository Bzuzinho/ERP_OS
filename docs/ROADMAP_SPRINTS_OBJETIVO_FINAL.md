# ERP_OS / JuntaOS — Gap Analysis e Roadmap de Sprints até ao Objectivo Final

**Última actualização:** 2026-06-17  
**Repositório:** `Bzuzinho/ERP_OS`  
**Base de comparação:** estado actual do código + documento técnico funcional JuntaOS  
**Documento relacionado:** `docs/ESTADO_PROGRAMACAO.md`

---

## 1. Objectivo deste documento

Este documento compara o que já está implementado no ERP_OS/JuntaOS com o objectivo final funcional da plataforma e transforma o que falta fazer num plano de sprints de programação.

A finalidade é simples: deixar de trabalhar por impulso e passar a trabalhar por fecho progressivo de produto.

A plataforma já tem uma base forte. O próximo trabalho não deve ser abrir módulos novos sem critério; deve ser consolidar, ligar, validar, preparar demonstração e transformar a solução num produto vendável.

---

## 2. Objectivo final da JuntaOS

A JuntaOS pretende ser uma plataforma operacional para juntas de freguesia, com dois ambientes principais:

1. **Administração** — backoffice interno da junta.
2. **Portal Munícipe** — balcão digital externo para munícipes, associações, empresas e entidades externas.

A lógica funcional final é:

```text
Portal cria pedidos, reservas e interações.
Administração recebe, organiza, atribui, executa e responde.
Portal acompanha estados, respostas e notificações.
```

A proposta central é:

```text
Do pedido do munícipe à execução pela junta, tudo num só lugar.
```

---

## 3. Estado actual resumido

O repositório já tem implementada uma estrutura muito acima de protótipo.

### 3.1 Já existe

- Autenticação.
- Separação Admin / Portal.
- Área Mobile operacional.
- Multi-organização.
- Roles e permissões via Spatie.
- Dashboard Admin.
- Dashboard Portal.
- Gestão de contactos.
- Tickets/pedidos.
- Comentários.
- Anexos.
- Tarefas.
- Checklists.
- Eventos.
- Espaços.
- Reservas.
- Manutenção e limpeza de espaços.
- Inventário.
- Empréstimos.
- Quebras.
- Reposições.
- Pedidos de recursos.
- Documentos.
- Versões de documentos.
- Regras de acesso.
- Atas.
- RH.
- Planeamento operacional.
- Operações recorrentes.
- Notificações.
- Relatórios.
- Exportação.
- Configurações.
- Utilizadores.
- Roles.
- Organização.
- Áreas funcionais.
- Testes por domínio/sprint.

### 3.2 Diagnóstico geral

| Área | Estado actual | Diagnóstico |
|---|---|---|
| Estrutura base | Muito avançada | A arquitectura suporta o produto final. |
| Módulos principais | Implementados | Falta consolidar regras e UX. |
| Portal | Implementado | Precisa de simplificação, visibilidade e experiência final. |
| Admin | Implementado | Precisa de dashboards e fluxos mais orientados à operação real. |
| Mobile | Implementado | Precisa de validação em campo e UX mobile-first. |
| Permissões | Parcial/implementado | Falta matriz funcional fechada e testes por perfil. |
| Notificações | Implementado | Falta garantir eventos, duplicados, rotas e experiência sino. |
| Reservas | Implementado avançado | Falta fechar sobreposição, automações e ciclo evento/tarefas. |
| Relatórios | Implementado | Falta relatório executivo, filtros finais e dashboards comerciais. |
| Produto vendável | Parcial | Faltam seeds demo, onboarding, planos SaaS, setup e documentação comercial. |

---

## 4. Gap analysis por módulo

### 4.1 Administração

#### Já existe

- Dashboard Admin.
- Gestão de pedidos/tickets.
- Gestão de tarefas.
- Gestão de eventos.
- Gestão documental.
- Atas.
- Espaços.
- Reservas.
- Inventário.
- RH.
- Planeamento.
- Relatórios.
- Notificações.
- Configurações.

#### Falta fechar

- Dashboard orientado a operação real da junta.
- Indicadores rápidos por área funcional.
- Separação clara entre módulos essenciais e módulos avançados.
- UX mais simples para demonstração.
- Ecrãs de detalhe com timeline consolidada.
- Fluxos guiados para atendimento.
- Validação visual de permissões por perfil.

---

### 4.2 Portal Munícipe

#### Já existe

- Dashboard Portal.
- Tickets próprios.
- Criação de pedidos.
- Comentários.
- Anexos.
- Eventos visíveis.
- Espaços.
- Reservas.
- Documentos.
- Atas.
- Planos operacionais visíveis.
- Notificações.

#### Falta fechar

- Portal com linguagem de munícipe, não linguagem técnica.
- Estado público do pedido separado do estado interno.
- Timeline simplificada.
- Garantia de bloqueio de notas internas.
- Garantia de bloqueio de anexos internos.
- Página “Os meus pedidos”.
- Página “As minhas reservas”.
- Formulários simples e orientados ao cidadão.
- Confirmações claras após submissão.
- Histórico de interacções.
- Perfil básico do munícipe/contacto.

---

### 4.3 Pedidos / Tickets

#### Já existe

- CRUD.
- Estados.
- Atribuição.
- Validação.
- Cancelamento.
- Comentários.
- Anexos.
- Geração de tarefas.
- Histórico de estados.
- Portal.

#### Falta fechar

- Separar estado interno de estado público.
- Automatizar encaminhamento por área funcional/categoria.
- Definir SLA ou data limite por prioridade.
- Criar respostas públicas estruturadas.
- Melhorar timeline única: estados, comentários, anexos, tarefas geradas.
- Regras de visibilidade robustas.
- Templates de pedidos por categoria.
- Regras de notificação sem duplicados.

---

### 4.4 Comentários e anexos

#### Já existe

- Comentários em tickets.
- Anexos em tickets.
- Download protegido por controllers.
- Attachments polimórficos.

#### Falta fechar

- Campo explícito de visibilidade: público/interno.
- Garantia visual de que nota interna não aparece no Portal.
- Testes de segurança específicos para anexos internos.
- Melhorar interface de timeline.
- Anexos em reservas, tarefas e documentos com política uniforme.

---

### 4.5 Áreas funcionais

#### Já existe

- Service areas.
- Associação de utilizadores a áreas de serviço.
- Ligação a tickets.

#### Falta fechar

- Matriz clara de responsabilidades.
- Encaminhamento automático por categoria/tipo de pedido.
- Dashboard por área funcional.
- Notificações automáticas para responsáveis da área.
- Permissões por área funcional.

---

### 4.6 Notificações

#### Já existe

- Modelo de notificações.
- Destinatários.
- Marcar lida.
- Marcar todas lidas.
- Admin e Portal.

#### Falta fechar

- Sino visual com contador real e popup de últimas notificações.
- Resolver destinatários por evento e área funcional.
- Evitar duplicados.
- Garantir URL correcta: Admin para internos, Portal para externos.
- Eventos completos de notificação: pedido criado, comentário, resposta, reserva, stock baixo, plano pendente, férias pendentes.
- Preferências de notificação por utilizador.

---

### 4.7 Agenda e eventos

#### Já existe

- CRUD de eventos.
- Participantes.
- Estados.
- Visibilidade Portal.
- Ligação a espaços.

#### Falta fechar

- Vista calendário real: mensal/semanal/lista.
- Criação automática de evento após reserva aprovada.
- Cancelamento/sinalização do evento quando reserva é cancelada.
- Diferenciar eventos públicos, internos e associados a reservas.
- Eventos ligados a tarefas e planos.
- Filtros por espaço, tipo e visibilidade.

---

### 4.8 Espaços e reservas

#### Já existe

- CRUD espaços.
- Estado de espaço.
- Reservas.
- Aprovar/rejeitar/concluir/cancelar.
- Manutenção.
- Limpeza.
- Portal.
- Ligação a tarefas.

#### Falta fechar

- Bloqueio rigoroso de sobreposição de reservas.
- Ver disponibilidade antes de submeter.
- Calendário por espaço.
- Aprovação cria evento automaticamente.
- Aprovação cria tarefas internas de preparação e limpeza.
- Cancelamento afecta evento e tarefas.
- Notificações internas e externas completas.
- Regras de caução/preço, se aplicável.

---

### 4.9 Tarefas

#### Já existe

- CRUD.
- Estados.
- Checklists.
- Conclusão.
- Validação.
- Reabertura.
- Mobile.
- Ligação a tickets e reservas.

#### Falta fechar

- Ligação completa a eventos e planos onde ainda não esteja reflectida na UI.
- Tarefas por área funcional.
- Dashboard “minhas tarefas”.
- Notificações por atribuição e prazo.
- SLA/prazos vencidos.
- Evidências de conclusão: fotos, anexos, observações.
- Melhor UX mobile para técnico operacional.

---

### 4.10 Gestão documental e atas

#### Já existe

- Documentos.
- Tipos.
- Versões.
- Regras de acesso.
- Downloads.
- Atas.
- Aprovação.
- Portal.

#### Falta fechar

- Publicação controlada de documentos no Portal.
- Partilha por contacto/entidade.
- Estado documental mais claro: rascunho, activo, arquivado, cancelado.
- Workflow completo de aprovação/publicação de atas.
- Pesquisa e filtros.
- Gestão de validade/revisão de documentos.

---

### 4.11 Inventário e recursos materiais

#### Já existe

- Itens.
- Categorias.
- Localizações.
- Stock.
- Movimentos.
- Empréstimos.
- Devoluções.
- Reposições.
- Quebras.
- Pedidos de recursos.

#### Falta fechar

- Alertas automáticos de stock baixo.
- Dashboard de stock crítico.
- Integração visual entre pedidos de recursos e inventário.
- Reservar recursos para eventos/reservas/tarefas.
- Histórico de utilização por recurso.
- Exportação de inventário.

---

### 4.12 Recursos Humanos

#### Já existe

- Colaboradores.
- Departamentos.
- Equipas.
- Presenças.
- Tipos de ausência.
- Pedidos de ausência.
- Aprovação/rejeição/cancelamento.

#### Falta fechar

- Calendário de ausências.
- Disponibilidade de equipas.
- Ligação entre funcionário e utilizador com UX clara.
- Alocação de equipas a tarefas/eventos.
- Relatórios de assiduidade.
- Permissões específicas RH.

---

### 4.13 Planeamento operacional

#### Já existe

- Planos operacionais.
- Estados.
- Aprovação.
- Cancelamento.
- Conclusão.
- Tarefas do plano.
- Participantes.
- Recursos.
- Operações recorrentes.
- Runs.

#### Falta fechar

- Templates de planos.
- Geração calendarizada de tarefas/eventos recorrentes.
- Dashboard de progresso.
- Indicadores de execução por plano.
- Notificações de plano pendente/aprovado/em atraso.
- Fecho operacional com relatório.

---

### 4.14 Relatórios

#### Já existe

- Relatórios por domínio.
- Exportação.

#### Falta fechar

- Dashboard executivo final.
- Indicadores comerciais de demonstração.
- Filtros por data, área, estado e responsável.
- Tempos médios de resolução.
- Pedidos por área funcional.
- Reservas por espaço.
- Tarefas vencidas.
- Stock baixo.
- Ausências.
- Planos em execução.
- Relatórios consolidados multi-organização para plano Intermunicipal.

---

### 4.15 Configurações

#### Já existe

- Organização.
- Utilizadores.
- Roles.
- Estado de utilizador.
- Reset de password.
- Avatar.
- Áreas de serviço.

#### Falta fechar

- Matriz de permissões por perfil.
- Perfis finais: `super_admin`, `admin_junta`, `executivo`, `administrativo`, `operacional`, `manutencao`, `armazem`, `rh`, `cidadao`, `associacao`, `empresa`.
- Configuração inicial assistida da junta.
- Personalização visual por organização.
- Parâmetros gerais: nome, morada, contactos, logótipo, cores, domínio/subdomínio.
- Seeds demo.

---

## 5. Roadmap de sprints

O projecto já teve testes e referências até Sprint 18. Assim, este plano continua a partir da **Sprint 19**.

---

# Sprint 19 — Auditoria Técnica, Normalização e Base de Produto

## Objectivo

Alinhar documentação, dependências, estado real do código e preparar a base para desenvolvimento controlado.

## Entregáveis

- Confirmar versão real de Laravel em execução.
- Actualizar `PROJECT_STRUCTURE_MAP.md`.
- Actualizar `docs/ESTADO_PROGRAMACAO.md`.
- Criar matriz técnica de módulos implementados.
- Executar testes completos.
- Executar build frontend.
- Registar resultado dos testes.
- Identificar rotas mortas, controllers não usados e páginas sem rota.

## Tarefas técnicas

- `composer install`.
- `npm install`.
- `php artisan migrate:fresh --seed`.
- `php artisan test`.
- `npm run build`.
- Validar `routes/web.php`, `routes/mobile.php`, `routes/admin/hr.php`, `routes/admin/planning.php`, `routes/admin/settings.php`.

## Critérios de aceitação

- Documentação e código deixam de estar desalinhados.
- Testes executados e resultado registado.
- Lista de erros reais criada.
- Roadmap confirmado.

## Prioridade

Muito alta.

---

# Sprint 20 — Matriz de Perfis, Permissões e Visibilidade

## Objectivo

Fechar a regra de quem pode ver, criar, editar, aprovar, apagar e descarregar cada recurso.

## Entregáveis

- Criar `docs/MATRIZ_PERMISSOES.md`.
- Criar roles finais.
- Criar permissões finais.
- Mapear permissões por módulo.
- Testes por perfil.
- Garantir que Portal não vê dados internos.
- Garantir isolamento por organização.

## Perfis finais

- `super_admin`.
- `admin_junta`.
- `executivo`.
- `administrativo`.
- `operacional`.
- `manutencao`.
- `armazem`.
- `rh`.
- `cidadao`.
- `associacao`.
- `empresa`.

## Critérios de aceitação

- Cada módulo tem permissões documentadas.
- Cada perfil tem capacidades claras.
- O Portal não acede a notas/anexos internos.
- Testes garantem bloqueio entre organizações.

## Prioridade

Muito alta.

---

# Sprint 21 — Portal Munícipe MVP Comercial

## Objectivo

Transformar o Portal numa experiência simples, compreensível e vendável ao munícipe.

## Entregáveis

- Dashboard Portal revisto.
- Página “Os meus pedidos”.
- Página “Novo pedido”.
- Página “As minhas reservas”.
- Página “Nova reserva”.
- Timeline simplificada do pedido.
- Estados públicos de pedido.
- Mensagens de confirmação claras.
- Perfil básico do utilizador externo.

## Regras a fechar

- Portal só mostra pedidos próprios.
- Portal só mostra reservas próprias.
- Portal só mostra documentos públicos ou partilhados.
- Portal nunca mostra comentários internos.
- Portal nunca mostra anexos internos.
- Linguagem técnica deve ser substituída por linguagem simples.

## Critérios de aceitação

- Um munícipe consegue criar pedido sem ajuda.
- Um munícipe consegue acompanhar estado.
- Um munícipe consegue anexar ficheiro.
- Um munícipe consegue pedir reserva.
- Nenhum dado interno aparece no Portal.

## Prioridade

Muito alta.

---

# Sprint 22 — Atendimento Digital: Pedidos, Comunicação e Timeline

## Objectivo

Fechar o ciclo central do produto: pedido criado, recebido, encaminhado, respondido e encerrado.

## Entregáveis

- Estado público separado do estado interno.
- Timeline administrativa completa.
- Timeline Portal simplificada.
- Comentário público vs nota interna.
- Anexo público vs anexo interno.
- Resposta pública estruturada.
- Histórico de estado claro.
- Encaminhamento manual por área funcional.
- Notificações completas para pedido e resposta.

## Fluxo alvo

```text
Munícipe cria pedido
→ Junta recebe alerta
→ Administrativo associa área funcional
→ Responsável recebe alerta
→ Responsável responde ou cria tarefa
→ Munícipe recebe resposta
→ Pedido é resolvido/fechado
```

## Critérios de aceitação

- Fluxo completo demonstrável em 5 minutos.
- Comunicação interna e pública separadas.
- Mudanças relevantes geram notificação.
- Histórico fica auditável.

## Prioridade

Muito alta.

---

# Sprint 23 — Áreas Funcionais e Encaminhamento Operacional

## Objectivo

Transformar áreas funcionais em motor real de encaminhamento, responsabilidade e notificação.

## Entregáveis

- Configuração de áreas funcionais final.
- Associação de utilizadores responsáveis.
- Associação de categorias de pedido a áreas.
- Encaminhamento automático opcional.
- Dashboard por área funcional.
- Filtros por área em tickets, tarefas e relatórios.
- Notificações por responsáveis da área.

## Critérios de aceitação

- Um pedido de determinada categoria pode sugerir/atribuir área funcional.
- Responsáveis da área são notificados.
- Admin consegue filtrar trabalho por área.
- Relatórios mostram pedidos por área.

## Prioridade

Alta.

---

# Sprint 24 — Reservas, Agenda e Tarefas Automáticas

## Objectivo

Fechar o fluxo completo de reservas de espaços, desde pedido no Portal até evento e tarefas internas.

## Entregáveis

- Bloqueio de reservas sobrepostas.
- Verificação de disponibilidade por espaço/data/hora.
- Calendário por espaço.
- Reserva aprovada cria evento.
- Reserva aprovada cria tarefas de preparação e limpeza.
- Cancelamento reflecte-se no evento.
- Cancelamento cancela ou sinaliza tarefas associadas.
- Notificações: pedido, aprovação, rejeição, cancelamento.

## Fluxo alvo

```text
Associação pede reserva
→ Admin recebe alerta
→ Admin aprova
→ Sistema cria evento
→ Sistema cria tarefa de preparação
→ Sistema cria tarefa de limpeza
→ Portal mostra reserva aprovada
```

## Critérios de aceitação

- Reservas sobrepostas são bloqueadas.
- Aprovação gera evento automaticamente.
- Aprovação gera tarefas internas.
- Cancelamento mantém consistência.
- Portal acompanha estado.

## Prioridade

Muito alta.

---

# Sprint 25 — Mobile Operacional e Execução em Campo

## Objectivo

Tornar o Mobile útil para operacionais/manutenção no terreno.

## Entregáveis

- Melhorar vista “Hoje”.
- Melhorar “Minhas tarefas”.
- Tarefa com checklist simples.
- Iniciar tarefa.
- Concluir tarefa.
- Enviar para validação.
- Adicionar foto/anexo.
- Adicionar observação.
- Mostrar prioridade e prazo.
- Mostrar origem da tarefa: pedido, reserva, evento ou plano.

## Critérios de aceitação

- Técnico abre telemóvel e vê tarefas do dia.
- Técnico inicia tarefa.
- Técnico adiciona evidência.
- Técnico conclui tarefa.
- Admin vê actualização.

## Prioridade

Alta.

---

# Sprint 26 — Notificações, Sino e Eventos Transversais

## Objectivo

Fechar o módulo de alertas para que o sistema acompanhe a operação sem depender de chamadas, emails e WhatsApp.

## Entregáveis

- Sino com contador de não lidas.
- Popup/lista de notificações recentes.
- Marcar uma como lida.
- Marcar todas como lidas.
- URL correcta por contexto.
- Deduplicação de notificações.
- Eventos de notificação normalizados.
- Testes de notificação.

## Eventos mínimos

- Pedido criado.
- Pedido atribuído.
- Pedido respondido.
- Comentário do munícipe.
- Tarefa criada.
- Tarefa atribuída.
- Tarefa vencida.
- Reserva pendente.
- Reserva aprovada.
- Reserva rejeitada.
- Reserva cancelada.
- Stock baixo.
- Plano pendente.
- Pedido de ausência pendente.

## Critérios de aceitação

- Utilizador só vê as suas notificações.
- Notificação leva ao recurso correcto.
- Não existem duplicados para o mesmo evento/destinatário.
- Portal e Admin usam rotas adequadas.

## Prioridade

Alta.

---

# Sprint 27 — Gestão Documental, Atas e Publicação Controlada

## Objectivo

Garantir que documentos e atas servem a operação interna e a transparência externa sem fuga de informação.

## Entregáveis

- Workflow final de documentos.
- Publicação controlada no Portal.
- Partilha por utilizador/contacto/entidade.
- Pesquisa e filtros.
- Validação de downloads por policy.
- Atas: rascunho, aprovada, publicada/arquivada.
- Regras claras de visibilidade.

## Critérios de aceitação

- Documento interno nunca aparece no Portal.
- Documento público aparece no Portal.
- Documento partilhado aparece apenas ao destinatário autorizado.
- Ata só aparece quando aprovada e visível.

## Prioridade

Média/Alta.

---

# Sprint 28 — Inventário, Recursos e Stock Baixo

## Objectivo

Transformar inventário e pedidos de recursos num fluxo operacional completo.

## Entregáveis

- Alertas automáticos de stock baixo.
- Dashboard de stock crítico.
- Reservar recurso para evento/reserva/tarefa/plano.
- Preparar recurso.
- Entregar recurso.
- Devolver recurso.
- Histórico por recurso.
- Relatório/exportação de inventário.

## Critérios de aceitação

- Item abaixo do mínimo gera alerta.
- Pedido de recurso pode estar ligado a ticket/tarefa/evento/reserva/plano.
- Estado do recurso é rastreável.
- Histórico mostra movimentos e empréstimos.

## Prioridade

Média/Alta.

---

# Sprint 29 — RH, Equipas e Disponibilidade Operacional

## Objectivo

Fechar RH como suporte à operação, sem transformar o produto num software pesado de recursos humanos.

## Entregáveis

- Calendário de ausências.
- Relação clara User vs Employee.
- Disponibilidade por equipa.
- Atribuição de equipas a tarefas/eventos.
- Relatório simples de presenças/ausências.
- Permissões específicas RH.

## Critérios de aceitação

- RH vê colaboradores e ausências.
- Admin sabe quem está disponível.
- Equipas podem ser usadas operacionalmente.
- Dados RH não aparecem a perfis não autorizados.

## Prioridade

Média.

---

# Sprint 30 — Relatórios Executivos e Dashboard de Demonstração

## Objectivo

Criar indicadores que vendem o produto e apoiam a decisão real da junta.

## Entregáveis

- Dashboard executivo.
- Pedidos por estado.
- Pedidos por área funcional.
- Tempos médios de resolução.
- Tarefas pendentes/vencidas.
- Reservas por espaço.
- Stock baixo.
- Ausências.
- Planos em execução.
- Exportação com filtros.
- Relatório consolidado para super admin/intermunicipal.

## Critérios de aceitação

- Dashboard mostra rapidamente a saúde operacional.
- Dados são filtráveis por data, estado, área e responsável.
- Exportação funciona.
- Existe visão consolidada para várias organizações.

## Prioridade

Alta para venda/demo.

---

# Sprint 31 — Configuração Inicial, Personalização e Seeds Demo

## Objectivo

Preparar o sistema para ser demonstrado, instalado e configurado rapidamente numa nova junta.

## Entregáveis

- Assistente de configuração inicial.
- Dados da organização.
- Logótipo e cores.
- Morada/contactos.
- Utilizadores iniciais.
- Áreas funcionais padrão.
- Roles padrão.
- Categorias de pedido padrão.
- Espaços demo.
- Inventário demo.
- Dados demo para apresentação comercial.

## Critérios de aceitação

- Uma nova organização pode ser configurada em poucos minutos.
- Demo fica pronta com dados realistas.
- Não é necessário criar tudo manualmente.

## Prioridade

Muito alta para produto comercial.

---

# Sprint 32 — UX, Smoke Tests e Preparação de Demonstração Comercial

## Objectivo

Polir a experiência e preparar uma demo linear baseada em fluxos, não em menus.

## Entregáveis

- Revisão visual Admin.
- Revisão visual Portal.
- Revisão visual Mobile.
- Smoke test de todas as rotas principais.
- Script de demonstração comercial.
- Massa de dados demo.
- Correcção de formulários, mensagens e estados vazios.
- Página de erro amigável.

## Fluxo demo obrigatório

```text
1. Munícipe cria pedido.
2. Junta recebe alerta.
3. Pedido é encaminhado para área funcional.
4. Responsável cria/conclui tarefa.
5. Munícipe recebe resposta.
6. Associação pede reserva.
7. Junta aprova reserva.
8. Sistema cria evento e tarefas.
9. Operacional executa tarefa no mobile.
10. Dashboard mostra indicadores.
```

## Critérios de aceitação

- Demo executável sem falhas críticas.
- Todas as páginas principais carregam.
- Formulários principais funcionam.
- Experiência é compreensível por alguém de fora do projecto.

## Prioridade

Muito alta.

---

# Sprint 33 — SaaS, Multi-organização e Plano Intermunicipal

## Objectivo

Preparar a plataforma para venda como SaaS e/ou solução multi-organização.

## Entregáveis

- Revisão do isolamento por organização.
- Super admin com visão global controlada.
- Relatórios consolidados.
- Gestão centralizada de organizações.
- Parametrização por plano: Base, Profissional, Premium, Intermunicipal.
- Limites por plano, se aplicável.
- Seeds por plano.
- Checklist de onboarding.

## Critérios de aceitação

- Uma organização não vê dados de outra.
- Super admin consegue gerir organizações.
- Relatórios consolidados funcionam.
- Funcionalidades podem ser agrupadas por pacote comercial.

## Prioridade

Alta para monetização.

---

# Sprint 34 — Deploy, Backups, Segurança e Operação

## Objectivo

Preparar a aplicação para ambiente real com segurança e manutenção mínima.

## Entregáveis

- Checklist de deploy.
- Configuração `.env.example` revista.
- Backups.
- Logs.
- Filas/queues.
- Storage de documentos/anexos.
- Políticas de upload.
- Limites de ficheiros.
- Protecção contra acesso indevido a ficheiros.
- Hardening de permissões.
- Plano de rollback.

## Critérios de aceitação

- Aplicação instala e corre com instruções claras.
- Upload/download seguros.
- Backups definidos.
- Erros ficam registados.
- Existe plano mínimo de operação.

## Prioridade

Muito alta antes de piloto real.

---

# Sprint 35 — Piloto Real e Fecho de Produto MVP

## Objectivo

Colocar a JuntaOS em condições de piloto com uma junta ou ambiente simulado realista.

## Entregáveis

- Ambiente piloto.
- Organização configurada.
- Utilizadores reais ou demo.
- Perfis finais.
- Áreas funcionais.
- Fluxos testados.
- Feedback recolhido.
- Bugs classificados.
- Plano pós-piloto.

## Critérios de aceitação

- Junta consegue usar atendimento digital.
- Junta consegue gerir pedidos e tarefas.
- Junta consegue gerir reservas.
- Munícipe consegue acompanhar pedidos/reservas.
- Admin consegue ver relatórios básicos.
- Produto está pronto para proposta comercial.

## Prioridade

Final de MVP.

---

## 6. Ordem recomendada de execução

A ordem proposta não deve ser alterada sem boa razão.

1. Sprint 19 — Auditoria e normalização.
2. Sprint 20 — Permissões e visibilidade.
3. Sprint 21 — Portal MVP.
4. Sprint 22 — Atendimento digital.
5. Sprint 23 — Áreas funcionais.
6. Sprint 24 — Reservas, agenda e tarefas automáticas.
7. Sprint 25 — Mobile operacional.
8. Sprint 26 — Notificações.
9. Sprint 27 — Documentos e atas.
10. Sprint 28 — Inventário e recursos.
11. Sprint 29 — RH e equipas.
12. Sprint 30 — Relatórios executivos.
13. Sprint 31 — Configuração inicial e seeds demo.
14. Sprint 32 — UX e demo comercial.
15. Sprint 33 — SaaS e intermunicipal.
16. Sprint 34 — Deploy, backups e segurança.
17. Sprint 35 — Piloto real.

---

## 7. Classificação por pacote comercial

### 7.1 Plano Base — Atendimento Digital

Necessário fechar até Sprint 22:

- Portal.
- Pedidos.
- Comentários.
- Anexos.
- Alertas.
- Dashboard básico.
- Configuração de utilizadores.

### 7.2 Plano Profissional — Gestão Operacional

Necessário fechar até Sprint 26:

- Tudo do Base.
- Tarefas.
- Agenda.
- Áreas funcionais.
- Reservas.
- Mobile operacional.
- Notificações completas.

### 7.3 Plano Premium — Gestão Completa da Junta

Necessário fechar até Sprint 31:

- Tudo do Profissional.
- Documentos.
- Atas.
- Inventário.
- RH.
- Planeamento.
- Relatórios.
- Seeds/configuração assistida.

### 7.4 Plano Intermunicipal

Necessário fechar até Sprint 33:

- Multi-organização consolidada.
- Super admin.
- Relatórios globais.
- Gestão centralizada.
- Perfis avançados.

---

## 8. MVP recomendado

Para vender ou demonstrar rapidamente, o MVP não deve tentar mostrar tudo.

### MVP comercial mínimo

- Admin.
- Portal.
- Pedidos.
- Comentários públicos/internos.
- Anexos públicos/internos.
- Áreas funcionais.
- Notificações.
- Tarefas.
- Reservas de espaços.
- Agenda.
- Dashboard simples.
- Utilizadores/permissões.

### MVP operacional forte

Acrescentar:

- Mobile.
- Inventário básico.
- Documentos.
- Relatórios.

### MVP completo

Acrescentar:

- RH.
- Planeamento.
- Operações recorrentes.
- Relatórios consolidados.
- Multi-organização comercial.

---

## 9. Riscos principais

### Risco 1 — Produto demasiado grande

A aplicação já tem muitos módulos. O risco agora é tentar acabar tudo ao mesmo tempo.

**Mitigação:** fechar por pacote comercial.

### Risco 2 — Portal demasiado técnico

O Portal pode herdar linguagem interna do Admin.

**Mitigação:** Sprint 21 deve ser focada em UX de munícipe.

### Risco 3 — Permissões incompletas

Sem matriz de permissões, há risco de exposição de dados internos.

**Mitigação:** Sprint 20 antes de qualquer demo séria.

### Risco 4 — Notificações ruidosas ou duplicadas

Alertas mal calibrados irritam mais do que ajudam.

**Mitigação:** deduplicação e regras por evento.

### Risco 5 — Demo sem dados realistas

Um ERP vazio parece sempre pior do que está.

**Mitigação:** Sprint 31 com seeds demo.

### Risco 6 — Módulos implementados mas pouco ligados

O código pode ter CRUDs bons, mas fluxos de ponta a ponta incompletos.

**Mitigação:** Sprints 22, 24, 25 e 32 devem validar fluxos reais.

---

## 10. Decisão estratégica recomendada

Não abrir novos grandes módulos até fechar:

1. Permissões.
2. Portal.
3. Atendimento digital.
4. Reservas + agenda + tarefas automáticas.
5. Notificações.
6. Demo comercial.

O que existe já chega para construir um produto forte. O que falta é disciplina de fecho, não imaginação.

---

## 11. Resumo executivo

A JuntaOS já tem a maior parte da arquitectura e dos módulos funcionais implementados. O objectivo final continua válido e está bem alinhado com o código existente.

O trabalho que falta divide-se em cinco blocos:

1. **Consolidação técnica:** documentação, testes, permissões, isolamento e segurança.
2. **Fecho do core funcional:** Portal, pedidos, comunicação, áreas funcionais e notificações.
3. **Fecho operacional:** reservas, agenda, tarefas, mobile, inventário, documentos e RH.
4. **Produto vendável:** dashboards, relatórios, seeds demo, UX e script comercial.
5. **Escala:** SaaS, multi-organização, deploy, backups, segurança e piloto real.

O plano recomendado vai da Sprint 19 à Sprint 35, com prioridade máxima nas Sprints 19 a 24, porque são estas que transformam o sistema de “muitos módulos implementados” em “produto funcional demonstrável”.

---

## 12. Registo de actualizações

| Data | Autor | Alteração |
|---|---|---|
| 2026-06-17 | ChatGPT | Criação inicial do gap analysis e roadmap de sprints face ao objectivo final. |
