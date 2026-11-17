# SGO — Sistema de Gestão Operacional

Sistema interno da **Concessionária Reviver** para controlar os serviços vendidos nos cemitérios (reforma, adornos, zeladoria, exumação, destinação de cinzas e plantio), da venda até a execução em campo.

Ele acompanha cada venda por todas as etapas: cadastro, cobrança das parcelas, liberação para agendamento, agendamento, confirmação com a família, execução com fotos e assinaturas da OS, validação e conclusão.

PHP + MySQL, sem framework. Funciona em hospedagem compartilhada comum e foi pensado para ser usado também no celular.

## Perfis de acesso

Cada perfil abre numa tela inicial própria e vê só o menu dele. Quem pode abrir cada tela está definido em um único lugar: `includes/permissoes.php`.

| Perfil | Papel no fluxo |
|---|---|
| Administrador | Acesso total. Também é o único que corrige vendas direto pelo Monitor geral. |
| Auditor | Acompanha e confere. Gerencia usuários. |
| Gestor | Agenda os serviços, anexa as fotos e acompanha a OS digital da unidade. |
| Sucesso do Cliente | Confirma a data com a família, faz os reagendamentos, valida os serviços e cuida das cartas de ausência. |
| Setor de Cobrança | Dá baixa e estorna parcelas e libera a venda para agendamento quando chega a 50% pago. |
| Administrativo | Cadastra as vendas. No banco de dados o nível continua se chamando `Vendedor`. |
| Operacional | Equipe de campo. Vê a OS e assina a etapa dela pelo celular. |
| Visualizador | Só consulta. |

## Módulos

- **Vendas:** cadastro em etapas, importação por planilha e triagem.
- **Cobrança:** parcelas pendentes, liberadas e concluídas, com baixa, estorno e histórico.
- **Agendamentos:** agenda, confirmação, reagendamento, envio e validação de fotos.
- **OS digital:** a ordem de serviço é assinada etapa por etapa pelo login de quem executou. Quem não executou uma etapa pode marcá-la como "não se aplica". O documento final sai em PDF com carimbo de data, hora e responsável em cada etapa.
- **Conversas:** chat interno com conversas individuais e grupos, fotos e encaminhamento de OS.
- **Monitor geral:** visão de todas as vendas, com edição direta para o Administrador.
- **Relatórios e dashboards:** comercial, financeiro e operacional, com filtros.
- **Cadastros:** cemitérios, contratos, jazigos, tomadores (`mantenedores`), produtos e serviços, usuários, processos e exumações.
- **Sistema:** backup do banco e configuração de e-mail.

## Requisitos

- PHP 7.4 ou mais novo (testado no 8.3), com as extensões `mysqli`, `fileinfo`, `mbstring` e `zip`.
- MySQL 5.7 ou mais novo, ou MariaDB 10.4 ou mais novo.
- Apache com `.htaccess` habilitado. As pastas de upload dependem disso para bloquear a execução de scripts.
- Composer, só para instalar o PHPMailer.

## Instalação

1. **Código:** clone o repositório na pasta pública do servidor.
2. **Dependências:** rode `composer install` para instalar o PHPMailer na pasta `vendor/`. O PHPExcel já vem em `includes/PHPExcel`.
3. **Configuração:** copie `includes/config.local.example.php` para `includes/config.local.php` e preencha os dados do banco, o `url_base` (endereço público, usado nos links de "Esqueci minha senha" e da OS) e, se quiser, a `senha_padrao_usuario`.
4. **Banco de dados:** importe um backup do banco. A estrutura e os dados não ficam no repositório.
5. **Scripts das fases 1.3 e 1.4:** se o banco ainda não tiver as tabelas novas, rode na aba SQL do phpMyAdmin os scripts da pasta `docs/sql` (veja abaixo).
6. **Permissões:** dê permissão de escrita ao PHP na pasta `uploads/`.
7. **E-mail:** configure em **Sistema, E-mail**. Isso gera o arquivo `includes/config_email.php`.

> **Nunca versione** `includes/config.local.php` nem `includes/config_email.php`. Eles guardam senhas e já estão no `.gitignore`. A `senha_padrao_usuario` nunca deve ser igual à senha do banco.

## Mudanças no banco de dados

Toda mudança de estrutura vem num script separado, com outro script para desfazer. Faça um backup (Sistema, Backup) antes de rodar qualquer um.

| Script | O que faz | Para desfazer |
|---|---|---|
| `docs/sql/os_digital_aplicar.sql` | Cria a tabela `os_assinaturas` e o nível de acesso `Operacional` | `os_digital_reverter.sql` |
| `docs/sql/chat_aplicar.sql` | Cria `chat_conversas`, `chat_participantes` e `chat_mensagens` | `chat_reverter.sql` |

Sem esses scripts, o sistema funciona normalmente e só esconde a OS digital e as conversas.

## Estrutura

```
index.php               Roteador: index.php?page=<módulo>&action=<tela>
login.php, logout.php   Acesso
includes/               Configuração, conexão, permissões, menu e regras de cada módulo
pages/<módulo>/         Telas (view.php...) e ações (actions.php, api.php)
assets/css/sgo.css      Tema (tokens de cor, claro e escuro) e componentes
assets/js/              Scripts por tela, carregados por includes/assets.php
uploads/                Fotos, cartas e anexos (fora do git, sem execução de scripts)
docs/                   Documentação de cada fase e scripts de banco
```

As telas só abrem pelo `index.php`. Acessar um arquivo de `pages/` direto pelo endereço dá 404. Cada endpoint confere o perfil no servidor com `sgo_proteger_endpoint()`.

## Histórico da modernização

Cada fase tem um documento com o que mudou, como instalar e o que foi testado:

| Fase | Documento |
|---|---|
| 0: Segurança e correções | [docs/FASE0_SEGURANCA.md](docs/FASE0_SEGURANCA.md) |
| 1: Base visual (tema, menu e celular) | [docs/FASE1_VISUAL.md](docs/FASE1_VISUAL.md) |
| 1.1: Relatórios e tela inicial por perfil | [docs/FASE1_1_RELATORIOS.md](docs/FASE1_1_RELATORIOS.md) |
| 1.2: Cobrança e Monitor geral | [docs/FASE1_2_COBRANCA_MONITOR.md](docs/FASE1_2_COBRANCA_MONITOR.md) |
| 1.3: OS digital com assinaturas | [docs/FASE1_3_OS_DIGITAL.md](docs/FASE1_3_OS_DIGITAL.md) |
| 1.4: Conversas internas | [docs/FASE1_4_CONVERSAS.md](docs/FASE1_4_CONVERSAS.md) |

## Deploy

O envio para o servidor é por FTP, substituindo os arquivos. Nunca envie `includes/config.local.php`, `includes/config_email.php` nem a pasta `uploads/` por cima da produção.

---

Desenvolvido por Marcos Nascimento — Inovação, Concessionária Reviver.
