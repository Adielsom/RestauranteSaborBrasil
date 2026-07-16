<?php
require_once '../utils/session_helper.php';
require_once '../config/db.php';

verificar_perfil('admin');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: listar.php");
    exit();
}

try {
   
    $sql = "UPDATE cardapio SET disponivel = IF(disponivel = 1, 0, 1) WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

} catch (PDOException $e) {
    die("Erro ao alterar disponibilidade do item: " . h($e->getMessage()));
}


header("Location: listar.php");
exit();