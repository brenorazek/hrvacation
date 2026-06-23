Afastamentos / Bloqueio de acessos — Plugin GLPI

Plugin para o GLPI onde o RH cadastra afastamentos de colaboradores
(férias, licenças, etc.) num calendário e, com base nas datas, o sistema abre
chamados automaticamente:


um chamado de bloqueio dos acessos no início do afastamento;
um chamado de liberação dos acessos no retorno.


Cada chamado já nasce com tarefas separadas (bloquear AD, Sectra, Office 365,
redirecionar e-mail, etc.), totalmente configuráveis pela interface.


Compatível com GLPI 10.0.x e GLPI 11.0.x.




Índice


Recursos
Requisitos
Instalação
Instalação em Docker
Configuração
Uso
Como os chamados são abertos
Cancelamento ao excluir
Estrutura de arquivos
Notas técnicas
Changelog
Licença



Recursos


Cadastro de afastamentos (colaborador + data de início + data de término + observações).
Calendário mensal com quem está afastado em cada dia.
Linha do tempo (Gantt) com todos os afastamentos da janela, em barras por colaborador, para enxergar sobreposições.
Listagem com filtros, ordenação e exportação (motor de busca do GLPI).
Abertura automática de chamados de bloqueio (início) e liberação (retorno).
Tarefas automáticas por chamado, configuráveis (uma por linha), com lista de bloqueio e o espelho de liberação.
Antecedência configurável para abrir cada chamado (ex.: liberar acessos 1 dia antes do retorno).
Abertura imediata ao cadastrar afastamentos de hoje ou retroativos; o cron cuida dos futuros.
Cancelamento automático dos chamados vinculados ao excluir um afastamento.
Categoria, grupo responsável e tipo dos chamados definidos na configuração.



Requisitos

ItemVersãoGLPI10.0.0+ ou 11.0.xPHP7.4+


Instalação


Copie a pasta hrvacation para o diretório de plugins do GLPI
(plugins/ ou marketplace/):


   <glpi>/plugins/hrvacation/setup.php


Ajuste o dono dos arquivos para o usuário do servidor web:


bash   chown -R www-data:www-data <glpi>/plugins/hrvacation


No GLPI, vá em Configurar › Plugins, localize
Afastamentos / Bloqueio de acessos e clique em Instalar e Ativar.
Conceda o direito aos perfis que vão usar o plugin em
Administração › Perfis. Na instalação, apenas o Super-Admin recebe
acesso total.



Instalação em Docker

Se você tem a pasta de plugins mapeada por volume, basta colocar a pasta
hrvacation no diretório mapeado do host (a pasta direto dentro de plugins/
ou marketplace/, sem nível extra) e ajustar o dono:

bashdocker exec -u root SEU_CONTAINER \
  chown -R www-data:www-data /var/www/html/glpi/marketplace/hrvacation


Ajuste o caminho conforme a sua imagem (/var/www/html/glpi, /var/glpi, etc.).



Como o GLPI 11 suspende a execução dos plugins ao detectar mudança de arquivos,
após uma atualização pode ser necessário retomar a execução:

bashdocker exec -u www-data SEU_CONTAINER php bin/console plugin:resume_execution

Comandos úteis via CLI:

bashdocker exec -u www-data SEU_CONTAINER php bin/console plugin:install hrvacation
docker exec -u www-data SEU_CONTAINER php bin/console plugin:activate hrvacation


Configuração

Acesse a configuração do plugin (engrenagem em Configurar › Plugins) e defina:

CampoFunçãoAntecedência do chamado de bloqueio (dias)Quantos dias antes do início abrir o chamado. 0 = no próprio dia.Antecedência do chamado de liberação (dias)Quantos dias antes do término abrir o chamado. 0 = no último dia.Categoria do chamado de bloqueioCategoria ITIL aplicada ao chamado de bloqueio.Categoria do chamado de liberaçãoCategoria ITIL aplicada ao chamado de liberação.Grupo responsávelGrupo técnico atribuído aos chamados.Tipo do chamadoIncidente ou Requisição (padrão: Requisição).Tarefas do chamado de bloqueioUma tarefa por linha. Cada linha vira uma tarefa "a fazer".Tarefas do chamado de liberaçãoIdem, já pré-preenchido com o espelho do bloqueio.

As tarefas padrão de bloqueio:

Bloquear acesso Active Directory
Bloquear acesso sectra Razek
Bloquear acesso sectra SmartMed
Bloquear acesso sectra Medfield
Bloquear acesso Office 365
Configurar mensagem de férias Office 365
Redirecionar Email

E o espelho de liberação (desbloquear / remover redirecionamento, etc.).


Uso


Menu Ferramentas › Afastamentos.
Botões no topo: Calendário e Linha do tempo.
Cadastrar afastamento: botão "+ Adicionar", o botão "Cadastrar afastamento"
no calendário/linha do tempo, ou clicando num dia do calendário.
Na listagem, clique no ID para abrir o afastamento.



Como os chamados são abertos

A abertura é baseada nas datas, não no momento do cadastro (exceto retroativos):


Bloqueio: abre quando o início chega (hoje, dentro da antecedência ou
retroativo) e o afastamento ainda não terminou.
Liberação: abre quando o término entra na janela de antecedência.


Dois gatilhos trabalham juntos:


Ao cadastrar — se o afastamento já começou ou começa hoje, o chamado de
bloqueio abre na hora do salvamento.
Tarefa automática diária (vacationTickets) — cuida dos afastamentos
futuros, abrindo cada chamado quando a data chega. Roda em modo GLPI
(interno), durante o uso normal do sistema.



Para timing preciso em produção, recomenda-se agendar
php bin/console glpi:cron no servidor e trocar a tarefa para modo CLI em
Configurar › Ações automáticas. Para testar na hora, use o botão
Executar nessa mesma tela.



Cada chamado é criado uma única vez — os IDs ficam gravados no afastamento,
evitando duplicação.


Cancelamento ao excluir

Ao excluir um afastamento, os chamados que já tinham sido abertos são
cancelados automaticamente (recebem uma solução com o motivo, indo para o
status Solucionado). Chamados já solucionados/fechados são ignorados. Se o
afastamento for excluído antes do retorno, o chamado de liberação não chega a
ser aberto.


Estrutura de arquivos

hrvacation/
├── setup.php                 # registro, init, versão, requisitos
├── hook.php                  # instalação/desinstalação, tabelas, direitos, cron
├── README.md
├── src/
│   ├── Period.php            # itemtype Afastamento + formulário + calendário + timeline + cron
│   └── Config.php            # configuração (linha única)
└── front/
    ├── period.php            # listagem
    ├── period.form.php       # formulário (exibe e processa)
    ├── calendar.php          # calendário mensal
    ├── timeline.php          # linha do tempo (Gantt)
    └── config.form.php       # página de configuração

Tabelas criadas: glpi_plugin_hrvacation_periods e glpi_plugin_hrvacation_configs.


Notas técnicas


Segue as convenções do GLPI: classes em /src com namespace
GlpiPlugin\Hrvacation (PSR-4), tabelas glpi_plugin_hrvacation_*, sem chaves
estrangeiras.
Consultas via query builder do GLPI (sem SQL cru) e saída HTML escapada —
compatível com as mudanças de segurança do GLPI 11.
O calendário e a linha do tempo são renderizados em PHP puro, sem dependências
de JavaScript externo.
A camada front/ é mantida (suportada pelo GLPI 11 por compatibilidade); pode
ser migrada para Controllers no futuro.



Changelog

VersãoMudanças1.6.1Ícone alterado para período de ausência (ti ti-calendar-off).1.6.0Renomeado de "férias" para "afastamento"; reordenação dos campos do formulário.1.5.1Correção do salvamento da configuração ("XML not well formed").1.5.0Abertura imediata de afastamentos de hoje/retroativos; cron em modo GLPI; ID clicável na lista.1.4.1Correção de idempotência na atualização (direitos de perfil duplicados).1.4.0Cancelamento automático dos chamados vinculados ao excluir um afastamento.1.3.0Tarefas automáticas configuráveis por chamado (bloqueio + espelho de liberação).1.2.1Correção do roteamento das telas (botões de adicionar/abrir).1.2.0Visão de linha do tempo (Gantt).1.1.0Compatibilidade com GLPI 11.1.0.0Versão inicial (GLPI 10): cadastro, calendário e abertura automática de chamados.


Licença

GPLv3+ — mesma licença do GLPI.


Desenvolvido por TI Razek.
