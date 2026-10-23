-- =======================================================
-- LISTO - Plataforma Prática de Listas Compartilhadas
-- Script de Banco de Dados MySQL (compatível com PDO)
-- =======================================================

CREATE DATABASE IF NOT EXISTS `listo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `listo`;

SET FOREIGN_KEY_CHECKS = 0;

-- Tabela: usuarios
DROP TABLE IF EXISTS `itens_lista`;
DROP TABLE IF EXISTS `listas`;
DROP TABLE IF EXISTS `membros_grupo`;
DROP TABLE IF EXISTS `grupos`;
DROP TABLE IF EXISTS `usuarios`;

CREATE TABLE `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(150) NOT NULL,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL,
  `avatar_cor` VARCHAR(30) DEFAULT 'indigo',
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: grupos (Famílias / Grupos de compartilhamento)
CREATE TABLE `grupos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nome` VARCHAR(150) NOT NULL,
  `descricao` TEXT NULL,
  `codigo_convite` VARCHAR(32) NOT NULL UNIQUE,
  `criado_por` INT NOT NULL,
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_grupos_usuario` FOREIGN KEY (`criado_por`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: membros_grupo (Relacionamento N:N entre usuários e grupos)
CREATE TABLE `membros_grupo` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `grupo_id` INT NOT NULL,
  `usuario_id` INT NOT NULL,
  `papel` ENUM('admin', 'membro') DEFAULT 'membro',
  `entrou_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_grupo_usuario` (`grupo_id`, `usuario_id`),
  CONSTRAINT `fk_mg_grupo` FOREIGN KEY (`grupo_id`) REFERENCES `grupos`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mg_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: listas (Listas pessoais ou de grupos)
CREATE TABLE `listas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(200) NOT NULL,
  `descricao` TEXT NULL,
  `tipo` ENUM('supermercado', 'filmes', 'receitas', 'tarefas', 'viagens', 'outros') NOT NULL DEFAULT 'tarefas',
  `icone` VARCHAR(50) DEFAULT 'check-square',
  `cor` VARCHAR(30) DEFAULT 'indigo',
  `usuario_id` INT NOT NULL,
  `grupo_id` INT NULL,
  `status` ENUM('ativa', 'arquivada', 'lixeira') DEFAULT 'ativa',
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_listas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_listas_grupo` FOREIGN KEY (`grupo_id`) REFERENCES `grupos`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: itens_lista (Itens específicos de acordo com a categoria)
CREATE TABLE `itens_lista` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `lista_id` INT NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `concluido` TINYINT(1) DEFAULT 0,
  `ordem` INT DEFAULT 0,
  
  -- Campos para Supermercado
  `quantidade` DECIMAL(10,2) DEFAULT 1.00,
  `unidade` VARCHAR(30) DEFAULT 'un',
  `preco_estimado` DECIMAL(10,2) DEFAULT 0.00,
  `preco_real` DECIMAL(10,2) DEFAULT 0.00,
  
  -- Campos para Filmes / Séries
  `genero` VARCHAR(100) NULL,
  `plataforma` VARCHAR(100) NULL,
  `nota` INT DEFAULT NULL,
  
  -- Campos para Receitas
  `rendimento` VARCHAR(100) NULL,
  `tempo_preparo` VARCHAR(100) NULL,
  `ingredientes` MEDIUMTEXT NULL,
  `modo_preparo` MEDIUMTEXT NULL,
  
  -- Campos para Tarefas / Viagens / Geral
  `prioridade` ENUM('baixa', 'media', 'alta', 'urgente') DEFAULT 'media',
  `data_limite` DATE NULL,
  `notas` TEXT NULL,
  
  -- Controle de lixeira / histórico do item
  `status` ENUM('ativo', 'lixeira') DEFAULT 'ativo',
  `criado_por` INT NOT NULL,
  `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT `fk_itens_lista` FOREIGN KEY (`lista_id`) REFERENCES `listas`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_itens_usuario` FOREIGN KEY (`criado_por`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =======================================================
-- DADOS INICIAIS DE DEMONSTRAÇÃO (Senha padrão: 123456)
-- Hash gerado por password_hash('123456', PASSWORD_DEFAULT)
-- =======================================================
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `avatar_cor`) VALUES
(1, 'Usuário Demonstração', 'demo@listo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'indigo'),
(2, 'Maria Silva', 'maria@listo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'emerald');

-- Grupo de exemplo
INSERT INTO `grupos` (`id`, `nome`, `descricao`, `codigo_convite`, `criado_por`) VALUES
(1, 'Família Silva', 'Lista compartilhada de compras e tarefas da casa', 'SILVA2026', 1);

INSERT INTO `membros_grupo` (`grupo_id`, `usuario_id`, `papel`) VALUES
(1, 1, 'admin'),
(1, 2, 'membro');

-- Listas de exemplo para cada categoria
INSERT INTO `listas` (`id`, `titulo`, `descricao`, `tipo`, `icone`, `cor`, `usuario_id`, `grupo_id`, `status`) VALUES
(1, 'Compras da Semana', 'Supermercado e feira da casa', 'supermercado', 'shopping-cart', 'emerald', 1, 1, 'ativa'),
(2, 'Maratona de Filmes e Séries', 'O que queremos assistir no fim de semana', 'filmes', 'film', 'rose', 1, 1, 'ativa'),
(3, 'Receitas Favoritas', 'Cardápio e receitas testadas da família', 'receitas', 'utensils', 'amber', 1, 1, 'ativa'),
(4, 'Tarefas da Casa & Trabalho', 'Organização de prioridades da semana', 'tarefas', 'check-square', 'indigo', 1, NULL, 'ativa'),
(5, 'Mala de Viagem - Praia', 'Checklist essencial para as férias', 'viagens', 'plane', 'sky', 1, NULL, 'ativa');

-- Itens da Lista 1 (Supermercado)
INSERT INTO `itens_lista` (`lista_id`, `titulo`, `concluido`, `ordem`, `quantidade`, `unidade`, `preco_estimado`, `preco_real`, `criado_por`) VALUES
(1, 'Arroz Tipo 1 (5kg)', 1, 1, 1.00, 'pct', 28.50, 27.90, 1),
(1, 'Feijão Carioca (1kg)', 1, 2, 2.00, 'pct', 8.50, 8.20, 1),
(1, 'Azeite de Oliva Extra Virgem', 0, 3, 1.00, 'un', 39.90, 0.00, 2),
(1, 'Leite Integral', 0, 4, 6.00, 'litros', 5.20, 0.00, 1),
(1, 'Maçãs Gala', 0, 5, 1.50, 'kg', 11.00, 0.00, 2);

-- Itens da Lista 2 (Filmes/Séries)
INSERT INTO `itens_lista` (`lista_id`, `titulo`, `concluido`, `ordem`, `genero`, `plataforma`, `nota`, `notas`, `criado_por`) VALUES
(2, 'Interestelar', 1, 1, 'Ficção Científica', 'Max / HBO', 5, 'Obra de arte emocionante!', 1),
(2, 'Duna: Parte 2', 0, 2, 'Ficção Científica', 'Max / Prime Video', 5, 'Assistir com som alto na sala', 2),
(2, 'Stranger Things', 0, 3, 'Suspense / Mistério', 'Netflix', 4, 'Esperando a próxima temporada', 1),
(2, 'O Menu', 1, 4, 'Suspense / Culinária', 'Disney+', 4, 'Muito criativo e tenso', 2);

-- Itens da Lista 3 (Receitas)
INSERT INTO `itens_lista` (`lista_id`, `titulo`, `concluido`, `ordem`, `rendimento`, `tempo_preparo`, `ingredientes`, `modo_preparo`, `notas`, `criado_por`) VALUES
(3, 'Bolo de Cenoura com Cobertura de Chocolate', 1, 1, '8 porções', '50 min', 
'MASSA:
- 3 cenouras médias raladas
- 4 ovos
- 1 xícara de óleo de milho
- 2 xícaras de açúcar
- 2 e 1/2 xícaras de farinha de trigo
- 1 colher (sopa) de fermento em pó

COBERTURA:
- 1 colher de manteiga
- 3 colheres de chocolate em pó 50%
- 1 xícara de açúcar
- 1 xícara de leite',
'1. Bata no liquidificador as cenouras, os ovos e o óleo até ficar bem homogêneo.
2. Numa tigela, misture o açúcar e a farinha. Adicione a mistura batida e mexa bem.
3. Adicione o fermento e misture delicadamente.
4. Despeje em forma untada e asse em forno pré-aquecido a 180°C por cerca de 40 minutos.
5. Para a cobertura, misture todos os ingredientes numa panela e leve ao fogo até engrossar. Despeje quente sobre o bolo.',
'O queridinho do café da tarde!', 1);

-- Itens da Lista 4 (Tarefas)
INSERT INTO `itens_lista` (`lista_id`, `titulo`, `concluido`, `ordem`, `prioridade`, `data_limite`, `notas`, `criado_por`) VALUES
(4, 'Pagar boleto da internet', 1, 1, 'urgente', '2026-10-25', 'Vencimento dia 25 sem juros', 1),
(4, 'Agendar consulta dentista', 0, 2, 'media', '2026-10-30', 'Ligar no período da tarde', 1),
(4, 'Revisar relatório do projeto', 0, 3, 'alta', '2026-10-28', 'Verificar requisitos e checklist', 1);

-- Itens da Lista 5 (Viagens)
INSERT INTO `itens_lista` (`lista_id`, `titulo`, `concluido`, `ordem`, `prioridade`, `notas`, `criado_por`) VALUES
(5, 'Passaporte e RG atualizados', 1, 1, 'urgente', 'Manter na bolsa de mão', 1),
(5, 'Protetor solar e repelente', 0, 2, 'alta', 'FPS 50+', 1),
(5, 'Carregador portátil e adaptador de tomada', 0, 3, 'alta', 'Carregar 100% na véspera', 1),
(5, 'Trajes de banho e toalha rápida', 0, 4, 'media', '2 peças cada', 1);
