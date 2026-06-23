# Plugin Férias RH / Bloqueio de acessos (GLPI)

Plugin para o GLPI onde o **RH cadastra as férias dos colaboradores** num
calendário e, com base nas datas lançadas, o sistema **abre automaticamente**:

1. Um **chamado de bloqueio** dos acessos no início das férias.
2. Um **chamado de liberação** dos acessos no retorno.

Compatível com **GLPI 10.0.x e 11.0.x**.

---

## Instalação

1. Copie a pasta `hrvacation` para o diretório `plugins/` da sua instalação GLPI:

   ```
   /caminho/do/glpi/plugins/hrvacation/
   ```

2. No GLPI, acesse **Configurar > Plugins**, localize "Férias RH / Bloqueio de
   acessos" e clique em **Instalar** e depois em **Ativar**.

3. Conceda o direito aos perfis que devem usar o plugin (ex.: um perfil "RH"):
   **Administração > Perfis > [perfil] > aba do plugin**. Por padrão, apenas o
   perfil **Super-Admin** recebe acesso total na instalação.

---

## Configuração

Acesse **Configurar > Plugins > Férias RH (engrenagem)** ou o link de
configuração do plugin e defina:

| Campo | Função |
|-------|--------|
| Antecedência do chamado de **bloqueio** | Quantos dias antes do início das férias o chamado é aberto. `0` = no próprio dia. |
| Antecedência do chamado de **liberação** | Quantos dias antes do término das férias o chamado é aberto. `0` = no último dia. |
| Categoria do chamado de bloqueio | Categoria ITIL aplicada ao chamado de bloqueio. |
| Categoria do chamado de liberação | Categoria ITIL aplicada ao chamado de liberação. |
| Grupo responsável | Grupo técnico atribuído aos dois chamados. |
| Tipo do chamado | Incidente ou Requisição (padrão: Requisição). |

---

## Uso

- Menu **Ferramentas > Períodos de férias**.
- Use o ícone de **calendário** no menu para ver o mês e cadastrar férias.
- Ao cadastrar um período (colaborador + início + término), os campos de
  chamado ficam vazios até o cron abri-los no momento certo. Depois de abertos,
  aparecem como links clicáveis no formulário do período.

---

## Como os chamados são abertos (tarefa automática / cron)

O plugin registra uma **ação automática diária** chamada `vacationTickets`
(em **Configurar > Ações automáticas**). A cada execução ela:

- **Bloqueio:** abre o chamado quando faltam até *N* dias (antecedência
  configurada) para o **início** das férias e o período ainda não terminou.
- **Liberação:** abre o chamado quando faltam até *N* dias para o **término**
  das férias.

Cada chamado é criado **uma única vez** — os IDs ficam gravados no período
(`block_ticket_id` / `unblock_ticket_id`), evitando duplicação.

> **Importante:** para o disparo automático funcionar no horário esperado,
> configure o GLPI em **modo CLI** de cron (recomendado), agendando
> `php bin/console glpi:cron` (ou `front/cron.php`) no crontab do servidor,
> idealmente para rodar de manhã cedo. No modo "GLPI" (web) o cron só roda
> quando alguém acessa o sistema.

O colaborador entra como **requerente** dos chamados. Para alterar esse
comportamento (ex.: usá-lo como observador e o RH como requerente), edite o
método `openTicket()` em `src/Period.php`.

---

## Estrutura de arquivos

```
hrvacation/
├── setup.php                 # registro, init, versão, requisitos
├── hook.php                  # instalação/desinstalação, tabelas, direitos, cron
├── README.md
├── src/
│   ├── Period.php            # itemtype "período de férias" + formulário + calendário + cron
│   └── Config.php            # configuração (linha única)
└── front/
    ├── period.php            # exibe o formulário do período
    ├── period.form.php       # trata gravar/editar/excluir
    ├── calendar.php          # página do calendário mensal
    └── config.form.php       # página de configuração
```

---

## Notas técnicas / pontos de ajuste

- As tabelas seguem o padrão `glpi_plugin_hrvacation_*` e **não usam chaves
  estrangeiras** (convenção do GLPI).
- O calendário é renderizado em PHP puro (sem libs JS externas) para máxima
  compatibilidade. Se quiser uma experiência mais rica (arrastar, cores por
  status), dá para trocar pela biblioteca FullCalendar que já vem no GLPI.
- **GLPI 11:** o plugin continua usando a camada `front/`, que o GLPI 11 ainda
  suporta por compatibilidade. As consultas ao banco usam o query builder nativo
  (sem SQL cru), e a saída HTML é escapada — atendendo às mudanças de
  segurança do GLPI 11 (fim do auto-sanitize de `$_POST`/`$_GET`). Se um dia
  quiser modernizar, dá para migrar `front/` para Controllers.
