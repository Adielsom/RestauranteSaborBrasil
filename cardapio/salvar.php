<?php
require_once '../utils/session_helper.php';
require_once '../config/db.php';

verificar_perfil('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $nome = trim($_POST['nome'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $preco = filter_input(INPUT_POST, 'preco', FILTER_VALIDATE_FLOAT);
    $descricao = trim($_POST['descricao'] ?? '');

    
    if (empty($nome) || empty($categoria) || !$preco || $preco <= 0) {
        die("Erro: Por favor, preencha todos os campos obrigatórios com valores válidos. <a href='javascript:history.back()'>Voltar</a>");
    }

    
    $categorias_validas = ['Entradas', 'Pratos Principais', 'Sobremesas', 'Bebidas'];
    if (!in_array($categoria, $categorias_validas)) {
        die("Erro: Categoria inválida.");
    }

    try {
        if ($id) {
            
            $sql = "UPDATE cardapio SET nome = :nome, categoria = :categoria, preco = :preco, descricao = :descricao WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':categoria' => $categoria,
                ':preco' => $preco,
                ':descricao' => $descricao,
                ':id' => $id
            ]);
        } else {
            
            $sql = "INSERT INTO cardapio (nome, categoria, preco, descricao, disponivel) VALUES (:nome, :categoria, :preco, :descricao, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':categoria' => $categoria,
                ':preco' => $preco,
                ':descricao' => $descricao
            ]);
        }
    } catch (PDOException $e) {
        die("Erro no banco de dados ao salvar item: " . h($e->getMessage()));
    }

    header("Location: listar.php");
    exit();
} else {
    header("Location: listar.php");
    exit();
}