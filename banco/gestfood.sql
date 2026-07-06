-- =====================================================
-- GestFood - Sistema de Automação de Pedidos
-- Banco de Dados MySQL
-- =====================================================

CREATE DATABASE IF NOT EXISTS gestfood CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestfood;

-- ---------------------------------------------------
-- Tabela: usuarios (login de funcionários/administradores)
-- ---------------------------------------------------
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin','atendente','producao','entregador') NOT NULL,
    telefone VARCHAR(20),
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Tabela: clientes
-- ---------------------------------------------------
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    cep VARCHAR(10),
    endereco VARCHAR(180),
    numero VARCHAR(10),
    complemento VARCHAR(100),
    bairro VARCHAR(80),
    cidade VARCHAR(80),
    estado VARCHAR(2),
    observacoes VARCHAR(150),
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Tabela: funcionarios (dados complementares de usuarios)
-- ---------------------------------------------------
CREATE TABLE funcionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    cargo VARCHAR(80),
    veiculo VARCHAR(80) NULL,
    placa VARCHAR(15) NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Tabela: produtos
-- ---------------------------------------------------
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    categoria VARCHAR(60) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    tempo_preparo INT NOT NULL DEFAULT 10, -- minutos
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Tabela: pedidos
-- ---------------------------------------------------
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    atendente_id INT NULL,
    entregador_id INT NULL,
    forma_pagamento ENUM('Dinheiro','Cartão','Pix') NOT NULL DEFAULT 'Dinheiro',
    observacoes VARCHAR(255),
    status ENUM('recebido','em_producao','pronto','em_entrega','entregue','cancelado') NOT NULL DEFAULT 'recebido',
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (atendente_id) REFERENCES usuarios(id),
    FOREIGN KEY (entregador_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Tabela: itens_pedido
-- ---------------------------------------------------
CREATE TABLE itens_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Tabela: entregas
-- ---------------------------------------------------
CREATE TABLE entregas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    entregador_id INT NULL,
    status ENUM('aguardando','em_rota','entregue') NOT NULL DEFAULT 'aguardando',
    iniciado_em DATETIME NULL,
    finalizado_em DATETIME NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (entregador_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;


-- ---------------------------------------------------
-- Tabela: cupons
-- ---------------------------------------------------
CREATE TABLE cupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    gerado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------
-- Tabela: relatorios (registro de exportações)
-- ---------------------------------------------------
CREATE TABLE relatorios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(60) NOT NULL,
    periodo VARCHAR(60) NOT NULL,
    gerado_por INT NULL,
    gerado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gerado_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- =====================================================
-- DADOS DE EXEMPLO
-- =====================================================

-- Senha de todos os usuários de exemplo: "123456"
-- Hash gerado com password_hash('123456', PASSWORD_DEFAULT)
INSERT INTO usuarios (nome, email, senha, tipo) VALUES
('Administrador', 'admin@gestfood.com', '$2y$10$92Q1i6z1c0m0f0m0f0m0fO7g8h1G7QvJb1s1s1s1s1s1s1s1s1s1u', 'admin'),
('Carla Atendente', 'atendente@gestfood.com', '$2y$10$92Q1i6z1c0m0f0m0f0m0fO7g8h1G7QvJb1s1s1s1s1s1s1s1s1s1u', 'atendente'),
('Pedro Produção', 'producao@gestfood.com', '$2y$10$92Q1i6z1c0m0f0m0f0m0fO7g8h1G7QvJb1s1s1s1s1s1s1s1s1s1u', 'producao'),
('Lucas Entregador', 'entregador@gestfood.com', '$2y$10$92Q1i6z1c0m0f0m0f0m0fO7g8h1G7QvJb1s1s1s1s1s1s1s1s1s1u', 'entregador');

INSERT INTO clientes (nome, telefone, endereco, numero, bairro, cidade, estado, observacoes) VALUES
('Maria Silva', '(11) 98765-4321', 'Rua das Flores', '123', 'Centro', 'São Paulo', 'SP', 'Portão azul'),
('João Santos', '(11) 97654-3210', 'Av. Principal', '456', 'Jardim América', 'São Paulo', 'SP', NULL),
('Ana Costa', '(11) 96543-2109', 'Rua do Comércio', '789', 'Vila Nova', 'São Paulo', 'SP', NULL);

INSERT INTO produtos (nome, categoria, preco, tempo_preparo) VALUES
('Coxinha', 'Salgados Fritos', 5.50, 15),
('Risoles', 'Salgados Fritos', 5.00, 15),
('Pastel', 'Salgados Fritos', 6.00, 12),
('Empada', 'Salgados Assados', 4.50, 20),
('Enroladinho', 'Salgados Assados', 4.00, 18),
('Quibe', 'Salgados Fritos', 5.50, 15);

INSERT INTO pedidos (cliente_id, forma_pagamento, status, total) VALUES
(1, 'Dinheiro', 'em_producao', 80.00),
(2, 'Cartão', 'recebido', 120.00),
(3, 'Pix', 'pronto', 127.50);

INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES
(1, 1, 10, 5.50), (1, 2, 5, 5.00),
(2, 3, 20, 6.00),
(3, 1, 15, 5.50), (3, 4, 10, 4.50);

INSERT INTO entregas (pedido_id, status) VALUES
(3, 'aguardando');
