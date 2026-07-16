<?php
require_once '../utils/session_helper.php';
require_once '../config/db.php';

verificar_login();

$mesa_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$mesa_id) {
    header("Location: mapa.php");
    exit();
}

try {
    
    $stmtMesa = $pdo->prepare("SELECT id, numero, status FROM mesas WHERE id = :id LIMIT 1");
    $stmtMesa->execute([':id' => $mesa_id]);
    $mesa = $stmtMesa->fetch();

    if (!$mesa) {
        header("Location: mapa.php");
        exit();
    }

  
    $stmtPedido = $pdo->prepare("SELECT id FROM pedidos WHERE mesa_id = :mesa_id AND status = 'aberto' LIMIT 1");
    $stmtPedido->execute([':mesa_id' => $mesa_id]);
    $pedidoExistente = $stmtPedido->fetch();

    if ($pedidoExistente) {
       
        header("Location: ../pedidos/ver.php?id=" . $pedidoExistente['id']);
        exit();
    }

   
    $stmtInsert = $pdo->prepare("INSERT INTO pedidos (mesa_id, garcom_id, status, total, aberto_em) VALUES (:mesa_id, :garcom_id, 'aberto', 0.00, NOW())");
    $stmtInsert->execute([
        ':mesa_id' => $mesa_id,
        ':garcom_id' => $_SESSION['usuario_id']
    ]);

    $novo_pedido_id = $pdo->lastInsertId();

   
    header("Location: ../pedidos/ver.php?id=" . $novo_pedido_id);
    exit();

} catch (PDOException $e) {
    die("Erro ao tentar abrir a mesa: " . h($e->getMessage()));
}