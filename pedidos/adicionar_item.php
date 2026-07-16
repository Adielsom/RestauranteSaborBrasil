<?php
require_once '../utils/session_helper.php';
require_once '../config/db.php';

verificar_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedido_id = filter_input(INPUT_POST, 'pedido_id', FILTER_VALIDATE_INT);
    $cardapio_id = filter_input(INPUT_POST, 'cardapio_id', FILTER_VALIDATE_INT);
    $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);

    if (!$pedido_id || !$cardapio_id || !$quantidade || $quantidade <= 0) {
        if ($pedido_id) {
            header("Location: ver.php?id=" . $pedido_id);
        } else {
            header("Location: ../mesas/mapa.php");
        }
        exit();
    }

    try {
        $stmtCardapio = $pdo->prepare("SELECT nome, preco FROM cardapio WHERE id = :id LIMIT 1");
        $stmtCardapio->execute([':id' => $cardapio_id]);
        $produto = $stmtCardapio->fetch();

        if (!$produto) {
            header("Location: ver.php?id=" . $pedido_id);
            exit();
        }

        $preco_unitario = $produto['preco'];
        $subtotal = $preco_unitario * $quantidade;

        $pdo->beginTransaction();

        $sqlInsert = "INSERT INTO pedido_itens (pedido_id, cardapio_id, nome_item, preco_unitario, quantidade, subtotal) 
                      VALUES (:pedido_id, :cardapio_id, :nome_item, :preco_unitario, :quantidade, :subtotal)";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([
            ':pedido_id' => $pedido_id,
            ':cardapio_id' => $cardapio_id,
            ':nome_item' => $produto['nome'],
            ':preco_unitario' => $preco_unitario,
            ':quantidade' => $quantidade,
            ':subtotal' => $subtotal
        ]);

        $sqlUpdateTotal = "UPDATE pedidos 
                           SET total = (SELECT IFNULL(SUM(subtotal), 0) FROM pedido_itens WHERE pedido_id = :pedido_id) 
                           WHERE id = :id";
        $stmtUpdateTotal = $pdo->prepare($sqlUpdateTotal);
        $stmtUpdateTotal->execute([
            ':pedido_id' => $pedido_id,
            ':id' => $pedido_id
        ]);

        $sqlMesa = "UPDATE mesas SET status = 'ocupada' WHERE id = (SELECT mesa_id FROM pedidos WHERE id = :pedido_id)";
        $pdo->prepare($sqlMesa)->execute([':pedido_id' => $pedido_id]);

        $pdo->commit();

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Erro ao adicionar item na mesa: " . h($e->getMessage()));
    }

    header("Location: ver.php?id=" . $pedido_id);
    exit();
} else {
    header("Location: ../mesas/mapa.php");
    exit();
}