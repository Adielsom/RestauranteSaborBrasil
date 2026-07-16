
CREATE DATABASE IF NOT EXISTS restaurante_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restaurante_db;
-- 1. Tabela de Usuários (Administrador e Garçom)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    login VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    -- Armazenar com password_hash()
    perfil ENUM('admin', 'garcom') NOT NULL,
    ativo TINYINT(1) DEFAULT 1,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;

-- 2. Tabela de Mesas do Restaurante
CREATE TABLE IF NOT EXISTS mesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL UNIQUE,
    status ENUM('livre', 'ocupada', 'aguardando_pagamento') DEFAULT 'livre'
) ENGINE = InnoDB;

-- 3. Tabela do Cardápio
CREATE TABLE IF NOT EXISTS cardapio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    categoria ENUM(
        'Entradas',
        'Pratos Principais',
        'Sobremesas',
        'Bebidas'
    ) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10, 2) NOT NULL,
    disponivel TINYINT(1) DEFAULT 1
) ENGINE = InnoDB;

-- 4. Tabela de Pedidos (Um por mesa por vez)
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mesa_id INT NOT NULL,
    garcom_id INT NOT NULL,
    status ENUM('aberto', 'aguardando_pagamento', 'fechado') DEFAULT 'aberto',
    forma_pagamento ENUM('dinheiro', 'debito', 'credito', 'pix') NULL,
    total DECIMAL(10, 2) DEFAULT 0.00,
    aberto_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    fechado_em DATETIME NULL,
    FOREIGN KEY (mesa_id) REFERENCES mesas(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (garcom_id) REFERENCES usuarios(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB;

-- 5. Tabela de Itens de cada Pedido
CREATE TABLE IF NOT EXISTS pedido_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    cardapio_id INT NOT NULL,
    nome_item VARCHAR(150) NOT NULL,
    -- Salvo no momento do pedido (Histórico)
    preco_unitario DECIMAL(10, 2) NOT NULL,
    -- Salvo no momento do pedido (Histórico)
    quantidade INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (cardapio_id) REFERENCES cardapio(id) ON DELETE RESTRICT
) ENGINE = InnoDB;

INSERT INTO usuarios (nome, login, senha, perfil)
VALUES (
        'Gerente Geral',
        'admin',
        '$2y$10$T1U7P0R1rE8.z5O8T7rKNeV8MofhR7zCKe6W6H2v5CieZfFidO7U.',
        'admin'
    ),
    (
        'Garçom Lucas',
        'garcom',
        '$2y$10$lU2eB1e8Pq8Xh3K2L6T2OunKkHbe8oHgeI6K9dCieZfFidO7U.',
        'garcom'
    ) ON DUPLICATE KEY
UPDATE id = id;
-- Pré-cadastro das 10 mesas fixas recomendadas pelo professor

INSERT INTO mesas (numero, status)
VALUES (1, 'livre'),
    (2, 'livre'),
    (3, 'livre'),
    (4, 'livre'),
    (5, 'livre'),
    (6, 'livre'),
    (7, 'livre'),
    (8, 'livre'),
    (9, 'livre'),
    (10, 'livre') ON DUPLICATE KEY
UPDATE id = id;
-- Alguns itens iniciais para o Cardápio não iniciar vazio
INSERT INTO cardapio (nome, categoria, descricao, preco, disponivel)
VALUES (
        'Bruschetta Tradicional',
        'Entradas',
        'Fatias de pão italiano tostadas com tomate, alho e manjericão.',
        22.90,
        1
    ),
    (
        'Parmegiana de Filé Mignon',
        'Pratos Principais',
        'Acompanha arroz branco e batata frita crocante.',
        64.90,
        1
    ),
    (
        'Risoto de Funghi',
        'Pratos Principais',
        'Arroz arbóreo com mix de cogumelos e queijo parmesão.',
        58.00,
        1
    ),
    (
        'Pudim de Leite Condensado',
        'Sobremesas',
        'Receita clássica da casa, extremamente cremosa.',
        14.00,
        1
    ),
    (
        'Suco Natural de Laranja',
        'Bebidas',
        'Copo de 400ml, feito na hora.',
        9.50,
        1
    ),
    (
        'Refrigerante Lata',
        'Bebidas',
        'Coca-Cola ou Guaraná Antártica 350ml.',
        6.50,
        1
    ) ON DUPLICATE KEY
UPDATE id = id;