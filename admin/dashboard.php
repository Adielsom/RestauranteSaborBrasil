<?php
require_once '../utils/session_helper.php';
require_once '../config/db.php';

verificar_perfil('admin');

try {
    $stmtFat = $pdo->query("SELECT IFNULL(SUM(total), 0) as faturamento FROM pedidos WHERE status = 'fechado' AND DATE(fechado_em) = CURDATE()");
    $faturamento_hoje = $stmtFat->fetch()['faturamento'];

    $stmtPed = $pdo->query("SELECT COUNT(*) as qtd FROM pedidos WHERE status = 'fechado' AND DATE(fechado_em) = CURDATE()");
    $pedidos_hoje = $stmtPed->fetch()['qtd'];

    $stmtMesas = $pdo->query("SELECT COUNT(*) as qtd FROM mesas WHERE status != 'livre'");
    $mesas_ativas = $stmtMesas->fetch()['qtd'];

    $stmtCard = $pdo->query("SELECT COUNT(*) as qtd FROM cardapio WHERE disponivel = 1");
    $itens_cardapio = $stmtCard->fetch()['qtd'];

} catch (PDOException $e) {
    die("Erro ao carregar os dados do painel: " . h($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Gerencial - SABOR BRASIL</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .stat-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 22px;
            text-align: left;
            border-left: 4px solid var(--border);
        }

        .stat-card.destaque-verde {
            border-left-color: var(--success);
        }

        .stat-card.destaque-azul {
            border-left-color: var(--accent);
        }

        .stat-card h3 {
            font-size: 11px;
            color: var(--text-secondary);
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .stat-card .value {
            font-size: 26px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-card .value.green {
            color: var(--success);
        }

        .stat-card .value.blue {
            color: var(--accent);
        }

        .modulos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .modulo-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 25px;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 150px;
        }

        .modulo-card:hover {
            transform: translateY(-3px);
            border-color: var(--accent);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        .modulo-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modulo-desc {
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.5;
        }
    </style>
</head>

<body>

    <header class="topbar">
        <a href="dashboard.php" class="topbar-brand">SABOR BRASIL <span
                style="font-size: 14px; font-weight: normal; color: var(--text-secondary);">| Gestão</span></a>
        <div class="topbar-user">
            <span>Gestor: <strong><?php echo h($_SESSION['usuario_nome']); ?></strong></span>
            <span class="badge-perfil"><?php echo h($_SESSION['usuario_perfil']); ?></span>

            <a href="../mesas/mapa.php"
                style="color: var(--accent); text-decoration: none; font-weight: 600; font-size: 13px;">Ver Salão ➔</a>

            <a href="../logout.php" class="btn-sair">Sair</a>
        </div>
    </header>

    <main class="container">
        <div class="page-header">
            <div>
                <h2>Painel de Controle e Operações</h2>
                <p style="color: var(--text-secondary); font-size: 13px;">Acompanhe o desempenho do restaurante hoje
                    (<?php echo date('d/m/Y'); ?>).</p>
            </div>
        </div>

    
        <div class="stats-grid">
            <div class="stat-card destaque-verde">
                <h3>Faturamento Hoje</h3>
                <div class="value green">R$ <?php echo number_format($faturamento_hoje, 2, ',', '.'); ?></div>
            </div>
            <div class="stat-card">
                <h3>Pedidos Fechados</h3>
                <div class="value"><?php echo $pedidos_hoje; ?></div>
            </div>
            <div class="stat-card destaque-azul">
                <h3>Mesas em Atendimento</h3>
                <div class="value blue"><?php echo $mesas_ativas; ?> <span
                        style="font-size: 16px; color: var(--text-secondary); font-weight: normal;">/ 10</span></div>
            </div>
            <div class="stat-card">
                <h3>Pratos no Cardápio</h3>
                <div class="value"><?php echo $itens_cardapio; ?> ativos</div>
            </div>
        </div>

        <h3
            style="margin-bottom: 18px; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary);">
            Módulos do Sistema</h3>

        <div class="modulos-grid">
            <a href="../cardapio/listar.php" class="modulo-card">
                <div>
                    <div class="modulo-title"><span style="color: var(--accent);">■</span> Catálogo de Produtos</div>
                    <div class="modulo-desc">Cadastre novos pratos e bebidas, altere preços e ative/desative itens do
                        menu no salão.</div>
                </div>
            </a>

            <a href="relatorio.php" class="modulo-card">
                <div>
                    <div class="modulo-title"><span style="color: var(--accent);">■</span> Relatórios de Vendas</div>
                    <div class="modulo-desc">Consulte faturamento por período, itens mais vendidos e o garçom destaque
                        do dia.</div>
                </div>
            </a>

            <a href="usuarios.php" class="modulo-card">
                <div>
                    <div class="modulo-title"><span style="color: var(--accent);">■</span> Equipe e Acessos</div>
                    <div class="modulo-desc">Cadastre novos garçons ou administradores e gerencie as permissões de
                        login.</div>
                </div>
            </a>

            <a href="../mesas/mapa.php" class="modulo-card">
                <div>
                    <div class="modulo-title"><span style="color: var(--accent);">■</span> Mapa do Salão (PDV)</div>
                    <div class="modulo-desc">Acesse a visualização das mesas para supervisionar os atendimentos em tempo
                        real.</div>
                </div>
            </a>
        </div>
    </main>

</body>

</html>