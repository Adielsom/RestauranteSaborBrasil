<?php
require_once '../utils/session_helper.php';
require_once '../config/db.php';

verificar_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedido_id = filter_input(INPUT_POST, 'pedido_id', FILTER_VALIDATE_INT);
    $forma_pagamento = trim($_POST['forma_pagamento'] ?? '');

    $formas_aceitas = ['dinheiro', 'debito', 'credito', 'pix'];

    if (!$pedido_id || !in_array($forma_pagamento, $formas_aceitas)) {
        if ($pedido_id) {
            header("Location: fechar_conta.php?id=" . $pedido_id);
        } else {
            header("Location: ../mesas/mapa.php");
        }
        exit();
    }

    try {
        
        $stmtP = $pdo->prepare("SELECT mesa_id FROM pedidos WHERE id = :id LIMIT 1");
        $stmtP->execute([':id' => $pedido_id]);
        $pedido = $stmtP->fetch();

        if (!$pedido) {
            header("Location: ../mesas/mapa.php");
            exit();
        }

        
        $pdo->beginTransaction();

        
        $sqlUpdatePedido = "UPDATE pedidos 
                            SET status = 'fechado', 
                                forma_pagamento = :forma_pagamento, 
                                fechado_em = NOW() 
                            WHERE id = :id";
        $stmtUpdateP = $pdo->prepare($sqlUpdatePedido);
        $stmtUpdateP->execute([
            ':forma_pagamento' => $forma_pagamento,
            ':id' => $pedido_id
        ]);

       
        $sqlUpdateMesa = "UPDATE mesas SET status = 'livre' WHERE id = :mesa_id";
        $stmtUpdateM = $pdo->prepare($sqlUpdateMesa);
        $stmtUpdateM->execute([':mesa_id' => $pedido['mesa_id']]);

        $pdo->commit(); 

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Erro ao confirmar o pagamento: " . h($e->getMessage()));
    }

    header("Location: ../mesas/mapa.php");
    exit();

} else {
    header("Location: ../mesas/mapa.php");
    exit();
}