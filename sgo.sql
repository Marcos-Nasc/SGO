-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           10.4.32-MariaDB - mariadb.org binary distribution
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Copiando estrutura do banco de dados para sgo_db
DROP DATABASE IF EXISTS `sgo_db`;
CREATE DATABASE IF NOT EXISTS `sgo_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `sgo_db`;

-- Copiando estrutura para tabela sgo_db.agendamentos
DROP TABLE IF EXISTS `agendamentos`;
CREATE TABLE IF NOT EXISTS `agendamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venda_id` int(11) NOT NULL,
  `gestor_id` int(11) NOT NULL,
  `data_agendada` datetime NOT NULL,
  `status` enum('Pendente de Contato','Confirmado','Concluído - Aguardando Validação','Finalizado e Enviado','Finalizado Internamente','Rejeitado','Reagendamento Solicitado') NOT NULL,
  `observacoes` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_agendamentos_vendas_idx` (`venda_id`),
  KEY `fk_agendamentos_usuarios_idx` (`gestor_id`),
  CONSTRAINT `fk_agendamentos_usuarios` FOREIGN KEY (`gestor_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_agendamentos_vendas` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sgo_db.cemiterios
DROP TABLE IF EXISTS `cemiterios`;
CREATE TABLE IF NOT EXISTS `cemiterios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL COMMENT 'Nome da Filial',
  `localizacao` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sgo_db.contratos
DROP TABLE IF EXISTS `contratos`;
CREATE TABLE IF NOT EXISTS `contratos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mantenedor_id` int(11) NOT NULL,
  `cemiterio_id` int(11) NOT NULL,
  `numero` varchar(100) NOT NULL COMMENT 'Número do Contrato',
  `jazigo` varchar(100) DEFAULT NULL,
  `quadra` varchar(100) DEFAULT NULL,
  `bloco` varchar(100) DEFAULT NULL COMMENT 'Novo campo adicionado',
  `observacao` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_contratos_mantenedores_idx` (`mantenedor_id`),
  KEY `fk_contratos_cemiterios_idx` (`cemiterio_id`),
  CONSTRAINT `fk_contratos_cemiterios` FOREIGN KEY (`cemiterio_id`) REFERENCES `cemiterios` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_contratos_mantenedores` FOREIGN KEY (`mantenedor_id`) REFERENCES `mantenedores` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sgo_db.fotos_servico
DROP TABLE IF EXISTS `fotos_servico`;
CREATE TABLE IF NOT EXISTS `fotos_servico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agendamento_id` int(11) NOT NULL,
  `enviado_por` int(11) NOT NULL COMMENT 'Usuário (Gestor) que enviou',
  `tipo` enum('antes','depois') NOT NULL,
  `caminho_arquivo` varchar(255) NOT NULL COMMENT 'Caminho/URL da foto',
  `data_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_validacao` enum('Pendente','Validado','Invalidado') NOT NULL DEFAULT 'Pendente',
  `obs_validacao` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_fotos_agendamentos_idx` (`agendamento_id`),
  KEY `fk_fotos_usuarios_idx` (`enviado_por`),
  CONSTRAINT `fk_fotos_agendamentos` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_fotos_usuarios` FOREIGN KEY (`enviado_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sgo_db.gestor_notas
DROP TABLE IF EXISTS `gestor_notas`;
CREATE TABLE IF NOT EXISTS `gestor_notas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gestor_id` int(11) NOT NULL COMMENT 'ID do usuário (gestor) da tabela de usuarios',
  `conteudo` text DEFAULT NULL COMMENT 'O texto da anotação',
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `gestor_id_UNIQUE` (`gestor_id`),
  CONSTRAINT `fk_notas_gestor` FOREIGN KEY (`gestor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sgo_db.log_emails_clientes
DROP TABLE IF EXISTS `log_emails_clientes`;
CREATE TABLE IF NOT EXISTS `log_emails_clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venda_id` int(11) NOT NULL,
  `email_destino` varchar(255) NOT NULL,
  `assunto` varchar(255) NOT NULL,
  `mensagem` text NOT NULL,
  `status_envio` enum('Enviado','Falha') NOT NULL,
  `data_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_log_emails_vendas_idx` (`venda_id`),
  CONSTRAINT `fk_log_emails_vendas` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sgo_db.mantenedores
DROP TABLE IF EXISTS `mantenedores`;
CREATE TABLE IF NOT EXISTS `mantenedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `status` enum('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sgo_db.produtos_servicos
DROP TABLE IF EXISTS `produtos_servicos`;
CREATE TABLE IF NOT EXISTS `produtos_servicos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `tipo` varchar(255) NOT NULL,
  `status` enum('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sgo_db.usuarios
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL COMMENT 'Armazenar como HASH (ex: bcrypt)',
  `cpf` varchar(14) NOT NULL COMMENT 'Formato: 000.000.000-00',
  `nivel` enum('Vendedor','Gestor','Sucesso do Cliente','Setor de Cobrança','Administrador') NOT NULL,
  `status` enum('Ativo','Inativo') NOT NULL DEFAULT 'Ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `cpf_UNIQUE` (`cpf`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sgo_db.validacoes_log
DROP TABLE IF EXISTS `validacoes_log`;
CREATE TABLE IF NOT EXISTS `validacoes_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foto_id` int(11) NOT NULL,
  `sucesso_cliente_id` int(11) NOT NULL,
  `acao` enum('validar','invalidar') NOT NULL,
  `data_acao` timestamp NOT NULL DEFAULT current_timestamp(),
  `comentario` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_validacoes_fotos_idx` (`foto_id`),
  KEY `fk_validacoes_usuarios_idx` (`sucesso_cliente_id`),
  CONSTRAINT `fk_validacoes_fotos` FOREIGN KEY (`foto_id`) REFERENCES `fotos_servico` (`id`),
  CONSTRAINT `fk_validacoes_usuarios` FOREIGN KEY (`sucesso_cliente_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela sgo_db.vendas
DROP TABLE IF EXISTS `vendas`;
CREATE TABLE IF NOT EXISTS `vendas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendedor_id` int(11) NOT NULL,
  `mantenedor_id` int(11) NOT NULL,
  `contrato_id` int(11) NOT NULL,
  `produto_servico_id` int(11) NOT NULL,
  `familia_comparecer` tinyint(1) NOT NULL DEFAULT 0,
  `numero_os` varchar(100) DEFAULT NULL,
  `numero_nf` varchar(100) DEFAULT NULL,
  `data_venda` date NOT NULL,
  `condicao_pagamento` enum('A Vista','A Prazo') NOT NULL,
  `valor_final` decimal(10,2) NOT NULL,
  `valor_entrada` decimal(10,2) DEFAULT NULL,
  `qtde_parcelas` int(11) DEFAULT NULL,
  `valor_parcela` decimal(10,2) DEFAULT NULL COMMENT 'Valor individual de cada parcela',
  `data_previsao_cobranca` date DEFAULT NULL COMMENT 'Data para verificação do setor de cobrança',
  `status` enum('Pendente','Aguardando Cobrança','Aprovado para Agendamento','Agendado','Concluído') NOT NULL DEFAULT 'Pendente',
  `observacao` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `fk_vendas_usuarios_idx` (`vendedor_id`) USING BTREE,
  KEY `fk_vendas_mantenedores_idx` (`mantenedor_id`) USING BTREE,
  KEY `fk_vendas_contratos_idx` (`contrato_id`) USING BTREE,
  KEY `fk_vendas_produtos_servicos_idx` (`produto_servico_id`) USING BTREE,
  CONSTRAINT `fk_vendas_contratos` FOREIGN KEY (`contrato_id`) REFERENCES `contratos` (`id`),
  CONSTRAINT `fk_vendas_mantenedores` FOREIGN KEY (`mantenedor_id`) REFERENCES `mantenedores` (`id`),
  CONSTRAINT `fk_vendas_produtos_servicos` FOREIGN KEY (`produto_servico_id`) REFERENCES `produtos_servicos` (`id`),
  CONSTRAINT `fk_vendas_usuarios` FOREIGN KEY (`vendedor_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exportação de dados foi desmarcado.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
