📌 SGO — Sistema de Gestão de Operações

Sistema interno para gerenciamento de vendas, agendamentos, contratos, validação de fotos e operações relacionadas a serviços funerários e manutenção de jazigos.

🚀 Tecnologias Utilizadas

PHP 8+

MySQL/MariaDB

HTML5 + CSS3 + JavaScript

Bootstrap (tema customizado)

PHPMailer

Composer

📂 Estrutura Geral do Sistema
Módulos principais

Gestão de Usuários
Perfis: Vendedor, Gestor, Sucesso do Cliente, Cobrança, Administrador.

Vendas
Cadastro de vendas com contrato, produto/serviço, pagamento e status.

Agendamentos
Fluxo completo: Pendente → Confirmado → Concluído → Validado/Enviado.

Envio e Validação de Fotos
Upload de imagens (antes/depois) + validação pelo Sucesso do Cliente.

Contratos & Mantenedores
Cadastro de responsáveis (mantenedores) e vínculo com jazigos.

Cemitérios
Cadastro das unidades/filiais com localização.

Produtos e Serviços
Catálogo de itens prestados pela empresa.

Anotações do Gestor
Módulo para salvar observações internas por gestor.

Logs de E-mail
Registro de todos os e-mails enviados ao cliente.

🗄️ Banco de Dados

O banco utiliza MariaDB com múltiplas relações entre as entidades principais.

A estrutura completa do banco está no arquivo:

🔗 database/sgo_db.sql

(Crie uma pasta chamada database no repositório e coloque o arquivo SQL dentro.)

Como importar:
DROP DATABASE IF EXISTS sgo_db;
CREATE DATABASE sgo_db;
USE sgo_db;

-- então importe o arquivo SQL pelo HeidiSQL, phpMyAdmin ou CLI

📁 Estrutura Recomendada de Diretórios
/includes
    db_connect.php
    auth_check.php
/pages
/uploads
/vendor
/assets
database/
    sgo_db.sql

🔒 Segurança

O projeto inclui um .gitignore configurado para ocultar arquivos sensíveis:

Credenciais (db_connect.php)

Autenticação (auth_check.php)

Uploads do sistema (/uploads)

Dependências (vendor)

Arquivos de ambiente (.env)

Logs, backups e diretórios temporários

🧪 Recursos do Sistema
✔ Login de usuários

Sistema autenticado com níveis de acesso.

✔ Painel administrativo

Acesso segmentado por cargo.

✔ Agendamentos integrados às vendas

Cada venda gera um agendamento vinculado.

✔ Envio de fotos antes/depois

Com validação individual por setor responsável.

✔ Geração de logs

Logs de e-mails enviados ao cliente.

✔ Notas internas

Cada gestor possui um bloco de anotações privado.

📦 Instalação
1. Clone o repositório:
git clone https://github.com/Marcos-Nasc/SGO.git

2. Instale dependências:
composer install

3. Configure o banco:

Importe database/sgo_db.sql

Configure includes/db_connect.php com suas credenciais

4. Ajuste permissões:
/uploads → escrita ativada

5. Acesse pelo navegador:
http://localhost/sgo

🧑‍💻 Autor

Desenvolvido por Marcos Nascimento
Contato: mnascimento.2506@gmail.com
