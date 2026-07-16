<?php
require_once '../utils/session_helper.php';
require_once '../config/db.php';

verificar_perfil('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $perfil = trim($_POST['perfil'] ?? '');

    if (empty($nome) || empty($login) || empty($senha) || empty($perfil)) {
        die("Erro: Todos os campos são obrigatórios. <a href='javascript:history.back()'>Voltar</a>");
    }

    if (!in_array($perfil, ['admin', 'garcom'])) {
        die("Erro: Perfil de usuário inválido.");
    }

    try {
        
        $stmtCheck = $pdo->prepare("SELECT id FROM usuarios WHERE login = :login LIMIT 1");
        $stmtCheck->execute([':login' => $login]);

        if ($stmtCheck->fetch()) {
            die("Erro: O login '" . h($login) . "' já está sendo utilizado por outro usuário. Escolha outro login. <a href='javascript:history.back()'>Voltar</a>");
        }

        $senha_criptografada = password_hash($senha, PASSWORD_DEFAULT);

        $sqlInsert = "INSERT INTO usuarios (nome, login, senha, perfil, ativo, criado_em) VALUES (:nome, :login, :senha, :perfil, 1, NOW())";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([
            ':nome' => $nome,
            ':login' => $login,
            ':senha' => $senha_criptografada,
            ':perfil' => $perfil
        ]);

    } catch (PDOException $e) {
        die("Erro no banco de dados ao cadastrar usuário: " . h($e->getMessage()));
    }

    header("Location: usuarios.php");
    exit();
} else {
    header("Location: usuarios.php");
    exit();
}