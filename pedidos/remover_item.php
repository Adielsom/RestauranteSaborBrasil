<?php

require_once '../utils/session_helper.php';
require_once '../config/db.php';

verificar_login();

$item_id   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$pedido_id = filter_input(INPUT_GET, 'pedido_id', FILTER_VALIDATE_INT);

if (!$item_id || !$pedido_id) {
    header("Location: ../mesas/mapa.php");
    exit();
}

try {
    $pdo->beginTransaction();

   
    $stmtDelete = $pdo->prepare("DELETE FROM pedido_itens WHERE id = :id AND pedido_id = :pedido_id");
    $stmtDelete->execute([
        ':id'        => $item_id,
        ':pedido_id' => $pedido_id
    ]);

    
    $sqlUpdateTotal = "UPDATE pedidos 
                       SET total = (SELECT IFNULL(SUM(subtotal), 0) FROM pedido_itens WHERE pedido_id = :pedido_id) 
                       WHERE id = :id";
    $stmtUpdate = $pdo->prepare($sqlUpdateTotal);
    $stmtUpdate->execute([
        ':pedido_id' => $pedido_id,
        ':id'        => $pedido_id
    ]);

   
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) as qtd FROM pedido_itens WHERE pedido_id = :pedido_id");
    $stmtCheck->execute([':pedido_id' => $pedido_id]);
    $res = $stmtCheck->fetch();

   
    if ($res['qtd'] == 0) {
        $sqlMesa = "UPDATE mesas SET status = 'livre' WHERE id = (SELECT mesa_id FROM pedidos WHERE id = :pedido_id)";
        $pdo->prepare($sqlMesa)->execute([':pedido_id' => $pedido_id]);
    }

    $pdo->commit();

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Erro ao remover item do pedido: " . h($e->getMessage()));
}

header("Location: ver.php?id=" . $pedido_id);
exit();