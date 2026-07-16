<?php
require_once '../utils/session_helper.php';
require_once '../config/db.php';

verificar_perfil('admin');

$data_inicio = filter_input(INPUT_GET, 'data_inicio') ?? date('Y-m-01');
$data_fim    = filter_input(INPUT_GET, 'data_fim') ?? date('Y-m-d');

try {
    $sqlGeral = "SELECT 
                    COUNT(*) as total_pedidos, 
                    IFNULL(SUM(total), 0) as faturamento_total, 
                    COUNT(DISTINCT mesa_id) as total_mesas 
                 FROM pedidos 
                 WHERE status = 'fechado' 
                 AND DATE(fechado_em) BETWEEN :inicio AND :fim";
    $stmtGeral = $pdo->prepare($sqlGeral);
    $stmtGeral->execute([':inicio' => $data_inicio, ':fim' => $data_fim]);
    $stats = $stmtGeral->fetch();

    $sqlPgto = "SELECT forma_pagamento, COUNT(*) as qtd 
                FROM pedidos 
                WHERE status = 'fechado' 
                AND DATE(fechado_em) BETWEEN :inicio AND :fim 
                GROUP BY forma_pagamento 
                ORDER BY qtd DESC LIMIT 1";
    $stmtPgto = $pdo->prepare($sqlPgto);
    $stmtPgto->execute([':inicio' => $data_inicio, ':fim' => $data_fim]);
    $pgto_top = $stmtPgto->fetch();

    $sqlGarcom = "SELECT u.nome as garcom, IFNULL(SUM(p.total), 0) as total_vendido, COUNT(p.id) as pedidos_atendidos
                  FROM pedidos p 
                  JOIN usuarios u ON p.garcom_id = u.id 
                  WHERE p.status = 'fechado' 
                  AND DATE(p.fechado_em) BETWEEN :inicio AND :fim 
                  GROUP BY u.id, u.nome 
                  ORDER BY total_vendido DESC LIMIT 1";
    $stmtGarcom = $pdo->prepare($sqlGarcom);
    $stmtGarcom->execute([':inicio' => $data_inicio, ':fim' => $data_fim]);
    $garcom_top = $stmtGarcom->fetch();

    $sqlItens = "SELECT pi.nome_item, SUM(pi.quantidade) as total_qtd, SUM(pi.subtotal) as total_faturado 
                 FROM pedido_itens pi 
                 JOIN pedidos p ON pi.pedido_id = p.id 
                 WHERE p.status = 'fechado' 
                 AND DATE(p.fechado_em) BETWEEN :inicio AND :fim 
                 GROUP BY pi.nome_item 
                 ORDER BY total_qtd DESC LIMIT 10";
    $stmtItens = $pdo->prepare($sqlItens);
    $stmtItens->execute([':inicio' => $data_inicio, ':fim' => $data_fim]);
    $itens_vendidos = $stmtItens->fetchAll();

    $sqlCat = "SELECT c.categoria, IFNULL(SUM(pi.subtotal), 0) as total_categoria 
               FROM pedido_itens pi 
               JOIN pedidos p ON pi.pedido_id = p.id 
               JOIN cardapio c ON pi.cardapio_id = c.id 
               WHERE p.status = 'fechado' 
               AND DATE(p.fechado_em) BETWEEN :inicio AND :fim 
               GROUP BY c.categoria";
    $stmtCat = $pdo->prepare($sqlCat);
    $stmtCat->execute([':inicio' => $data_inicio, ':fim' => $data_fim]);
    $categorias_grafico = $stmtCat->fetchAll();

    $labels_cat = [];
    $dados_cat  = [];
    foreach ($categorias_grafico as $cat) {
        $labels_cat[] = $cat['categoria'];
        $dados_cat[]  = (float) $cat['total_categoria'];
    }

} catch (PDOException $e) {
    die("Erro ao gerar relatórios: " . h($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios de Vendas - SABOR BRASIL</title>
    <link rel="stylesheet" href="../css/style.css">
    <!-- Biblioteca Chart.js para renderizar o gráfico sem instalar nada -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .filtro-box {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: flex-end;
            gap: 15px;
            flex-wrap: wrap;
        }
        .filtro-box .form-group {
            margin-bottom: 0;
            flex: 1;
            min-width: 150px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            text-align: left;
            border-left: 4px solid var(--border);
        }
        .stat-card.destaque-verde { border-left-color: var(--success); }
        .stat-card.destaque-azul { border-left-color: var(--accent); }
        .stat-card.destaque-amarelo { border-left-color: var(--warning); }

        .stat-card h3 {
            font-size: 11px;
            color: var(--text-secondary);
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        .stat-card .value {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
        }
        .stat-card .value.green { color: var(--success); }
        .stat-card .value.yellow { color: var(--warning); }
        .stat-card .value.blue { color: var(--accent); }
        
        .grid-relatorios {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        @media (max-width: 900px) {
            .grid-relatorios { grid-template-columns: 1fr; }
        }
        .box-tabela {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 22px;
        }
        .tabela-relatorio {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .tabela-relatorio th, .tabela-relatorio td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        .tabela-relatorio th {
            color: var(--text-secondary);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        @media print {
            body { background: white !important; color: black !important; }
            .topbar, .filtro-box, .no-print { display: none !important; }
            .stat-card, .box-tabela { border: 1px solid #ccc !important; box-shadow: none !important; color: black !important; }
            .stat-card .value, .tabela-relatorio th, .tabela-relatorio td { color: black !important; }
        }
    </style>
</head>
<body>

<header class="topbar no-print">
    <a href="dashboard.php" class="topbar-brand">SABOR BRASIL <span style="font-size: 14px; font-weight: normal; color: var(--text-secondary);">| Auditoria</span></a>
    <div class="topbar-user">
        <span>Gestão: <strong><?php echo h($_SESSION['usuario_nome']); ?></strong></span>
        <span class="badge-perfil"><?php echo h($_SESSION['usuario_perfil']); ?></span>
        <button onclick="window.print();" class="btn-submit" style="width: auto; margin: 0; padding: 6px 15px; background: transparent; border: 1px solid var(--border); color: var(--text-primary);">Imprimir Relatório</button>
        <a href="../logout.php" class="btn-sair">Sair</a>
    </div>
</header>

<main class="container">
    <div class="page-header">
        <div>
            <h2>Relatório Analítico de Vendas</h2>
            <p style="color: var(--text-secondary); font-size: 13px;">Auditoria de faturamento, desempenho por operador e curva ABC de produtos.</p>
        </div>
    </div>

    <form action="relatorio.php" method="GET" class="filtro-box no-print">
        <div class="form-group">
            <label for="data_inicio">Data Inicial</label>
            <input type="date" id="data_inicio" name="data_inicio" class="form-control" value="<?php echo h($data_inicio); ?>" required>
        </div>
        <div class="form-group">
            <label for="data_fim">Data Final</label>
            <input type="date" id="data_fim" name="data_fim" class="form-control" value="<?php echo h($data_fim); ?>" required>
        </div>
        <div>
            <button type="submit" class="btn-submit" style="width: auto; margin: 0; padding: 12px 25px; font-size: 14px;">Filtrar Período</button>
        </div>
    </form>

    <div class="stats-grid">
        <div class="stat-card destaque-verde">
            <h3>Receita no Período</h3>
            <div class="value green">R$ <?php echo number_format($stats['faturamento_total'], 2, ',', '.'); ?></div>
        </div>
        <div class="stat-card">
            <h3>Contas Encerradas</h3>
            <div class="value"><?php echo $stats['total_pedidos']; ?></div>
        </div>
        <div class="stat-card destaque-azul">
            <h3>Mesas Atendidas</h3>
            <div class="value blue"><?php echo $stats['total_mesas']; ?></div>
        </div>
        <div class="stat-card destaque-amarelo">
            <h3>Pagamento Predominante</h3>
            <div class="value yellow" style="font-size: 20px;">
                <?php 
                    if ($pgto_top) {
                        echo strtoupper(h($pgto_top['forma_pagamento'])) . " (" . $pgto_top['qtd'] . "x)";
                    } else {
                        echo "Nenhum";
                    }
                ?>
            </div>
        </div>
    </div>

    <?php if ($garcom_top): ?>
    <div style="background: rgba(42, 157, 143, 0.08); border: 1px solid var(--success); padding: 18px 22px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div>
            <span style="font-size: 16px; color: var(--text-primary);"><strong>Operador Destaque do Período:</strong> <?php echo h($garcom_top['garcom']); ?></span>
            <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">Responsável pelo atendimento de <?php echo $garcom_top['pedidos_atendidos']; ?> contas no salão.</div>
        </div>
        <div style="font-size: 20px; font-weight: 700; color: var(--success);">
            R$ <?php echo number_format($garcom_top['total_vendido'], 2, ',', '.'); ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid-relatorios">
        
        <div class="box-tabela">
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 6px;">Produtos Mais Vendidos</h3>
            <p style="font-size: 12px; color: var(--text-secondary);">Itens com maior volume de saída no período filtrado.</p>

            <?php if (empty($itens_vendidos)): ?>
                <p style="color: var(--text-secondary); text-align: center; padding: 35px 0; font-size: 13px;">Nenhuma venda registrada neste intervalo de datas.</p>
            <?php else: ?>
                <table class="tabela-relatorio">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Volume</th>
                            <th>Total Gerado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itens_vendidos as $item): ?>
                            <tr>
                                <td><strong style="color: var(--text-primary);"><?php echo h($item['nome_item']); ?></strong></td>
                                <td style="color: var(--text-secondary);"><?php echo $item['total_qtd']; ?>x</td>
                                <td style="color: var(--success); font-weight: 600;">R$ <?php echo number_format($item['total_faturado'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="box-tabela">
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 6px;">Faturamento por Categoria</h3>
            <p style="font-size: 12px; color: var(--text-secondary);">Participação na receita entre Bebidas, Pratos Principais, etc.</p>
            
            <?php if (empty($dados_cat)): ?>
                <p style="color: var(--text-secondary); text-align: center; padding: 35px 0; font-size: 13px;">Sem dados suficientes para processar o gráfico.</p>
            <?php else: ?>
                <div style="position: relative; height: 260px; width: 100%; margin-top: 20px;">
                    <canvas id="graficoCategorias"></canvas>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php if (!empty($dados_cat)): ?>
<script>
    const ctx = document.getElementById('graficoCategorias').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($labels_cat); ?>,
            datasets: [{
                data: <?php echo json_encode($dados_cat); ?>,
                backgroundColor: [
                    '#2a9d8f', 
                    '#e76f51', 
                    '#f4a261', 
                    '#264653', 
                    '#8a939e'  
                ],
                borderWidth: 2,
                borderColor: '#1e2124'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#f0f2f5', font: { size: 12 } }
                }
            }
        }
    });
</script>
<?php endif; ?>

</body>
</html>