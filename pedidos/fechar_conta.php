<?php
require_once '../utils/session_helper.php';
require_once '../config/db.php';

verificar_login();

$pedido_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$pedido_id) {
    header("Location: ../mesas/mapa.php");
    exit();
}

try {
    
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) as qtd FROM pedido_itens WHERE pedido_id = :id");
    $stmtCheck->execute([':id' => $pedido_id]);
    $total_itens = $stmtCheck->fetch()['qtd'];

    if ($total_itens == 0) {
        header("Location: ver.php?id=" . $pedido_id);
        exit();
    }

    
    $sqlPedido = "SELECT p.*, m.id as mesa_id, m.numero as mesa_numero, u.nome as garcom_nome 
                  FROM pedidos p 
                  JOIN mesas m ON p.mesa_id = m.id 
                  JOIN usuarios u ON p.garcom_id = u.id 
                  WHERE p.id = :id LIMIT 1";
    $stmtP = $pdo->prepare($sqlPedido);
    $stmtP->execute([':id' => $pedido_id]);
    $pedido = $stmtP->fetch();

    if (!$pedido) {
        header("Location: ../mesas/mapa.php");
        exit();
    }

  
    if ($pedido['status'] === 'aberto') {
        $pdo->beginTransaction();
        
        $pdo->prepare("UPDATE pedidos SET status = 'aguardando_pagamento' WHERE id = :id")->execute([':id' => $pedido_id]);
        $pdo->prepare("UPDATE mesas SET status = 'aguardando_pagamento' WHERE id = :mesa_id")->execute([':mesa_id' => $pedido['mesa_id']]);
        
        $pdo->commit();
        $pedido['status'] = 'aguardando_pagamento';
    }

    $sqlItens = "SELECT * FROM pedido_itens WHERE pedido_id = :pedido_id ORDER BY id ASC";
    $stmtI = $pdo->prepare($sqlItens);
    $stmtI->execute([':pedido_id' => $pedido_id]);
    $itens = $stmtI->fetchAll();

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Erro ao carregar fechamento de conta: " . h($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fechamento - Mesa <?php echo str_pad($pedido['mesa_numero'], 2, '0', STR_PAD_LEFT); ?> - SABOR BRASIL</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .extrato-box {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 35px;
            max-width: 600px;
            margin: 20px auto;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }
        .extrato-header {
            text-align: center;
            border-bottom: 2px dashed var(--border);
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .extrato-itens {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .extrato-itens th, .extrato-itens td {
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }
        .extrato-itens th {
            text-align: left;
            color: var(--text-secondary);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .extrato-total {
            font-size: 22px;
            font-weight: 700;
            text-align: right;
            color: var(--success);
            border-top: 2px dashed var(--border);
            padding-top: 15px;
            margin-bottom: 25px;
        }
        .botoes-acao {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .btn-imprimir {
            background: transparent;
            color: var(--text-primary);
            border: 1px solid var(--border);
            padding: 12px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            flex: 1;
            font-size: 14px;
            transition: 0.2s;
        }
        .btn-imprimir:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        @media print {
            body {
                background-color: white !important;
                color: black !important;
            }
            .topbar, .botoes-acao, .form-pagamento, .no-print {
                display: none !important;
            }
            .container {
                margin: 0 !important;
                padding: 0 !important;
            }
            .extrato-box {
                border: none !important;
                box-shadow: none !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                color: black !important;
            }
            .extrato-header h2, .extrato-header p, .extrato-itens th, .extrato-itens td {
                color: black !important;
                border-color: #ccc !important;
            }
            .extrato-total {
                color: black !important;
                border-color: #000 !important;
            }
        }
    </style>
</head>
<body>

<header class="topbar no-print">
    <a href="ver.php?id=<?php echo $pedido['id']; ?>" class="topbar-brand">SABOR BRASIL <span style="font-size: 14px; font-weight: normal; color: var(--text-secondary);">| Comanda</span></a>
    <div class="topbar-user">
        <span>Operador: <strong><?php echo h($pedido['garcom_nome']); ?></strong></span>
        <span class="badge-perfil"><?php echo h($_SESSION['usuario_perfil']); ?></span>
        <a href="ver.php?id=<?php echo $pedido['id']; ?>" style="color: var(--text-secondary); text-decoration: none; font-size: 13px;">⬅ Voltar ao Pedido</a>
    </div>
</header>

<main class="container">
    <div class="extrato-box">
        <div class="extrato-header">
            <h2 style="font-size: 20px; font-weight: 700;">SABOR BRASIL 🇧🇷 · CUPOM DE CONSUMO</h2>
            <p style="font-size: 14px; margin-top: 4px; color: var(--text-primary);">Comanda da Mesa <?php echo str_pad($pedido['mesa_numero'], 2, '0', STR_PAD_LEFT); ?></p>
            <p style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">Registro #<?php echo str_pad($pedido['id'], 5, '0', STR_PAD_LEFT); ?> · Emissão: <?php echo date('d/m/Y H:i'); ?></p>
        </div>

        <table class="extrato-itens">
            <thead>
                <tr>
                    <th>Qtd</th>
                    <th>Descrição do Produto</th>
                    <th>Valor Unit.</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item): ?>
                    <tr>
                        <td style="color: var(--text-secondary);"><?php echo $item['quantidade']; ?>x</td>
                        <td><strong style="color: var(--text-primary); font-size: 14px;"><?php echo h($item['nome_item']); ?></strong></td>
                        <td style="color: var(--text-secondary);">R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                        <td style="text-align: right; color: var(--text-primary); font-weight: 600;">R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="extrato-total">
            TOTAL DO CONSUMO: R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?>
        </div>

        <div class="botoes-acao no-print">
            <button type="button" class="btn-imprimir" onclick="window.print();">
                Imprimir Cupom de Conferência
            </button>
        </div>

        <form action="confirmar_pagamento.php" method="POST" class="form-pagamento no-print">
            <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
            
            <div class="form-group">
                <label for="forma_pagamento">Forma de Pagamento Utilizada</label>
                <select name="forma_pagamento" id="forma_pagamento" class="form-control" required style="cursor: pointer; font-size: 15px; padding: 12px;">
                    <option value="">-- Selecione a modalidade de acerto --</option>
                    <option value="pix">Pix (Transferência Instantânea)</option>
                    <option value="debito">Cartão de Débito</option>
                    <option value="credito">Cartão de Crédito</option>
                    <option value="dinheiro">Dinheiro (Espécie)</option>
                </select>
            </div>

            <button type="submit" class="btn-submit" style="background: var(--success); font-size: 16px; padding: 14px; margin-top: 15px;" onclick="return confirm('Confirmar o recebimento do valor e liberar a mesa para novos atendimentos?');">
                Confirmar Recebimento e Liberar Mesa
            </button>
        </form>
    </div>
</main>

</body>
</html>