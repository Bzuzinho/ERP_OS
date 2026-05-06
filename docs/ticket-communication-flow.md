# Ticket Communication Flow

## Objetivo
Completar o ciclo de comunicacao entre Junta e municipe nos pedidos, mantendo isolamento organizacional e separacao entre conteudo interno e publico.

## Comentarios: nota interna vs resposta publica
- Nota interna:
  - visibility = internal
  - Visivel apenas no Admin
  - Nao aparece no Portal
- Resposta ao municipe:
  - visibility = public
  - Visivel no Portal e no Admin
  - Gera notificacao para o municipe associado ao ticket
- Mensagem do municipe:
  - visibility = public
  - Criada no Portal
  - Gera notificacao interna para responsaveis

## Fluxo Admin
1. Criar pedido no Admin.
2. Atribuir responsavel/area funcional.
3. Mudar estado tecnico do pedido.
4. Adicionar nota interna (internal).
5. Adicionar resposta ao municipe (public).
6. Carregar anexos internos ou publicos.
7. Consultar historico completo na timeline admin.

## Fluxo Portal
1. Criar pedido no Portal.
2. Consultar estado publico do pedido.
3. Ver apenas comunicacao publica.
4. Acrescentar informacao publica ao pedido.
5. Carregar anexos publicos.
6. Consultar timeline simplificada.

## Regras de notificacao
- Admin resposta publica:
  - Notifica municipe associado ao ticket.
  - action_url aponta para portal.tickets.show.
- Admin nota interna:
  - Nao notifica municipe.
  - Pode notificar equipa interna.
- Municipe comenta:
  - Notifica assigned_to, utilizadores da area funcional e fallback admin_junta/administrativo.
  - action_url aponta para admin.tickets.show.
- Mudanca de estado:
  - Notifica municipe apenas quando a mudanca e relevante publicamente.
  - Historico de estado e sempre registado em ticket_status_histories.

## Regras de visibilidade
- Portal nunca ve:
  - Comentarios internos
  - Anexos internos
  - Metadados internos de atribuicao/equipa/departamento/area funcional
- Admin ve comunicacao completa.
- Policies e requests reforcam as regras por papel e por organizacao.

## Regras de anexos
- Admin pode definir visibility internal/public ao carregar.
- Portal so pode carregar/publicar anexos public.
- Download sempre passa por controller + policy.
- Download interno no Portal devolve 403.
- Cross-organization devolve 403/404 conforme o fluxo.

## Estados publicos vs internos
Mapeamento para exibicao no Portal:
- novo, aberto, new -> Recebido
- em_analise, in_analysis -> Em analise
- encaminhado, assigned, em_execucao, in_progress, agendado -> Em tratamento
- aguarda_informacao, waiting_info -> A aguardar informacao
- resolvido, resolved -> Resolvido
- fechado, closed, indeferido -> Encerrado
- cancelado, cancelled -> Cancelado

Admin continua a trabalhar com estado tecnico interno.
