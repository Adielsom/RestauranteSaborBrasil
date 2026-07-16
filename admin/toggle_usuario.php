<?php
require_once '../utils/session_helper.php';
require_once '../config/db.php';

verificar_perfil('admin');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: usuarios.php");
    exit();
}

if ($id == $_SESSION['usuario_id']) {
    die("Ação negada: Você não pode bloquear ou desativar a sua própria conta de administrador durante o uso. <a href='usuarios.php'>Voltar</a>");
}

try {
    $sql = "UPDATE usuarios SET ativo = IF(ativo = 1, 0, 1) WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

} catch (PDOException $e) {
    die("Erro ao alterar status do usuário: " . h($e->getMessage()));
}

header("Location: usuarios.php");
exit();