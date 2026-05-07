# Roteiro de Demonstracao JuntaOS

## 1. Objetivo da demo
Demonstrar que a JuntaOS suporta atendimento digital completo, com separacao entre administracao interna e Portal do Municipe, mantendo rastreabilidade, comunicacao e execucao operacional.

## 2. Utilizadores demo
- admin@juntaos.local / password
- cidadao@juntaos.local / password
- associacao@juntaos.local / password
- operacional@juntaos.local / password
- rh@juntaos.local / password
- armazem@juntaos.local / password

## 3. Fluxo 1 — Administracao: visao geral
1. Entrar com admin@juntaos.local.
2. Abrir o dashboard admin e explicar KPIs principais.
3. Mostrar os modulos operacionais: Pedidos, Agenda, Tarefas, Reservas, Alertas, Relatorios e Configuracoes.

## 4. Fluxo 2 — Portal: criar pedido
1. Terminar sessao e entrar com cidadao@juntaos.local.
2. No Balcao digital, clicar em Criar pedido.
3. Submeter um pedido simples e mostrar a confirmacao.

## 5. Fluxo 3 — Admin: tratar pedido
1. Voltar ao admin.
2. Abrir Pedidos e entrar no pedido Buraco na Rua Principal.
3. Mostrar estado Em tratamento e atribuicao interna.

## 6. Fluxo 4 — Comunicacao: resposta publica e nota interna
1. No detalhe do pedido, adicionar uma mensagem publica (visivel no Portal).
2. Adicionar uma nota interna (nao visivel no Portal).
3. Mostrar anexos publicos e internos.
4. Voltar ao Portal para comprovar que apenas conteudo publico aparece.

## 7. Fluxo 5 — Reservas: portal pede, admin aprova, agenda/tarefas sao criadas
1. Entrar com associacao@juntaos.local e abrir Reservas.
2. Mostrar pedido de reserva de salao.
3. No admin, aprovar reserva (ou mostrar reserva aprovada do dataset).
4. Mostrar evento criado na Agenda e tarefas de preparacao/limpeza.

## 8. Fluxo 6 — Alertas: sino e notificacoes
1. Mostrar o sino no admin e no portal.
2. Abrir Alertas e demonstrar nao lidos/lidos.
3. Clicar num alerta e navegar para o destino.

## 9. Fluxo 7 — Relatorios e dashboards
1. Abrir Relatorios no admin.
2. Mostrar uma visao de operacao (pedidos, reservas, inventario ou RH).
3. Voltar ao dashboard para reforcar monitorizacao em tempo real.

## 10. Fluxo 8 — Configuracoes: utilizadores, roles, areas funcionais
1. Abrir Configuracoes.
2. Mostrar Utilizadores, Perfis e permissões, Areas funcionais.
3. Reforcar que permissões e segregacao de contexto estao ativas.

## 11. O que dizer ao demonstrar
- A JuntaOS separa atendimento ao municipe da operacao interna.
- Cada pedido tem historico, mensagens e anexos com visibilidade controlada.
- Reservas ligam-se a agenda e execucao operacional (tarefas e alertas).
- A plataforma esta preparada para uso mobile, com foco em velocidade de atendimento.

## 12. O que evitar mostrar se ainda estiver parcial
- Areas em desenvolvimento marcadas como Em breve.
- Configuracoes tecnicas nao necessarias para o publico da demo.
- Qualquer registo incompleto ou com dados inconsistentes.

## 13. Checklist antes da demo
- Dados demo sem vazios criticos.
- Acesso admin e portal validado.
- Build frontend sem erros.
- Testes de smoke executados.
- Logs limpos e ambiente estabilizado.
