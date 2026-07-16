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
    $sqlPedido = "SELECT p.*, m.numero as mesa_numero, u.nome as garcom_nome 
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

    $sqlItens = "SELECT * FROM pedido_itens WHERE pedido_id = :pedido_id ORDER BY id DESC";
    $stmtI = $pdo->prepare($sqlItens);
    $stmtI->execute([':pedido_id' => $pedido_id]);
    $itens_pedido = $stmtI->fetchAll();

    $sqlCardapio = "SELECT id, nome, categoria, preco FROM cardapio WHERE disponivel = 1 ORDER BY categoria ASC, nome ASC";
    $stmtC = $pdo->query($sqlCardapio);
    $cardapio_disponivel = $stmtC->fetchAll();

} catch (PDOException $e) {
    die("Erro ao carregar os dados do pedido: " . h($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atendimento Mesa <?php echo str_pad($pedido['mesa_numero'], 2, '0', STR_PAD_LEFT); ?> - SABOR BRASIL</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .painel-atendimento {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .painel-atendimento {
                grid-template-columns: 1fr;
            }
        }

        .box-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 22px;
        }

        .tabela-itens {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .tabela-itens th,
        .tabela-itens td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .tabela-itens th {
            color: var(--text-secondary);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-remover {
            background: transparent;
            color: #f8b4b2;
            border: 1px solid var(--danger);
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            font-size: 11px;
            transition: 0.2s;
        }

        .btn-remover:hover {
            background: var(--danger);
            color: white;
        }

        .btn-acao-grande {
            display: block;
            width: 100%;
            padding: 14px;
            background: var(--success);
            color: white;
            text-align: center;
            font-size: 15px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            margin-top: 20px;
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-acao-grande:hover {
            background: var(--accent-hover);
        }
    </style>
</head>

<body>

    <header class="topbar">
        <a href="../mesas/mapa.php" class="topbar-brand">SABOR BRASIL <span
                style="font-size: 14px; font-weight: normal; color: var(--text-secondary);">| Atendimento</span></a>
        <div class="topbar-user">
            <span>Operador: <strong><?php echo h($pedido['garcom_nome']); ?></strong></span>
            <span class="badge-perfil"><?php echo h($_SESSION['usuario_perfil']); ?></span>
            <a href="../mesas/mapa.php" style="color: var(--text-secondary); text-decoration: none; font-size: 13px;">⬅
                Voltar ao Salão</a>
        </div>
    </header>

    <main class="container">
        <div class="page-header">
            <div>
                <h2>Comanda · Mesa <?php echo str_pad($pedido['mesa_numero'], 2, '0', STR_PAD_LEFT); ?></h2>
                <p style="color: var(--text-secondary); font-size: 13px;">Registro de Pedido
                    #<?php echo str_pad($pedido['id'], 5, '0', STR_PAD_LEFT); ?> · Aberto em:
                    <?php echo date('d/m/Y H:i', strtotime($pedido['aberto_em'])); ?></p>
            </div>
            <div>
                <span style="font-size: 24px; font-weight: 700; color: var(--success);">
                    Total: R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?>
                </span>
            </div>
        </div>

        <div class="painel-atendimento">

           
            <div class="box-card">
                <h3 style="margin-bottom: 15px; font-size: 16px; font-weight: 700;">Adicionar Produto ao Pedido</h3>

                <form action="adicionar_item.php" method="POST">
                    <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">

                    <div class="form-group">
                        <label for="cardapio_id">Selecione o Item do Catálogo</label>
                        <select name="cardapio_id" id="cardapio_id" class="form-control" required
                            style="cursor: pointer;">
                            <option value="">-- Escolha um produto --</option>
                            <?php
                            $categoria_atual = "";
                            foreach ($cardapio_disponivel as $item):
                                if ($categoria_atual !== $item['categoria']) {
                                    if ($categoria_atual !== "")
                                        echo "</optgroup>";
                                    $categoria_atual = $item['categoria'];
                                    echo "<optgroup label='" . h($categoria_atual) . "'>";
                                }
                                ?>
                                <option value="<?php echo $item['id']; ?>">
                                    <?php echo h($item['nome']) . " (R$ " . number_format($item['preco'], 2, ',', '.') . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if ($categoria_atual !== "")
                                echo "</optgroup>"; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-top: 15px;">
                        <label for="quantidade">Quantidade</label>
                        <input type="number" name="quantidade" id="quantidade" class="form-control" value="1" min="1"
                            max="50" required>
                    </div>

                    <button type="submit" class="btn-submit" style="margin-top: 15px; font-size: 14px;">Adicionar à
                        Comanda</button>
                </form>

                <?php
                
                if ($pedido['status'] === 'aberto' && !empty($itens_pedido)):
                    ?>
                    <a href="fechar_conta.php?id=<?php echo $pedido['id']; ?>" class="btn-acao-grande">
                        Encerrar Comanda e Fechar Conta
                    </a>
                <?php endif; ?>
            </div>

            
            <div class="box-card">
                <h3 style="margin-bottom: 15px; font-size: 16px; font-weight: 700;">Extrato de Consumo da Mesa</h3>

                <?php if (empty($itens_pedido)): ?>
                    <p style="color: var(--text-secondary); padding: 30px 0; text-align: center; font-size: 13px;">Nenhum
                        item foi lançado para esta mesa ainda.</p>
                <?php else: ?>
                    <table class="tabela-itens">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Qtd</th>
                                <th>Preço Unit.</th>
                                <th>Subtotal</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itens_pedido as $item): ?>
                                <tr>
                                    <td><strong
                                            style="color: var(--text-primary); font-size: 14px;"><?php echo h($item['nome_item']); ?></strong>
                                    </td>
                                    <td style="color: var(--text-secondary);"><?php echo $item['quantidade']; ?>x</td>
                                    <td style="color: var(--text-secondary);">R$
                                        <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                    <td style="color: var(--success); font-weight: 600;">R$
                                        <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                                    <td>
                                        <a href="remover_item.php?id=<?php echo $item['id']; ?>&pedido_id=<?php echo $pedido['id']; ?>"
                                            class="btn-remover"
                                            onclick="return confirm('Tem certeza que deseja remover este item da comanda?');">Remover</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        </div>
    </main>

</body>

</html>