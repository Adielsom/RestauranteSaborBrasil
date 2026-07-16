<?php
require_once '../utils/session_helper.php';
require_once '../config/db.php';

verificar_perfil('admin');

try {
    $stmt = $pdo->query("SELECT * FROM cardapio ORDER BY categoria ASC, nome ASC");
    $itens = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar o cardápio: " . h($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Produtos - SABOR BRASIL</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .tabela-cardapio {
            width: 100%;
            border-collapse: collapse;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
        }

        .tabela-cardapio th,
        .tabela-cardapio td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .tabela-cardapio th {
            background: rgba(0, 0, 0, 0.2);
            color: var(--text-secondary);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-categoria {
            background: rgba(42, 157, 143, 0.15);
            color: #52c7b8;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-ativo {
            color: var(--success);
            font-weight: 600;
            font-size: 13px;
        }

        .status-inativo {
            color: var(--danger);
            font-weight: 600;
            font-size: 13px;
        }

        .btn-acao {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            margin-right: 6px;
            transition: 0.2s;
        }

        .btn-editar {
            background: transparent;
            color: #f8c390;
            border: 1px solid var(--warning);
        }

        .btn-editar:hover {
            background: var(--warning);
            color: #141618;
        }

        .btn-toggle-on {
            background: transparent;
            color: #f8b4b2;
            border: 1px solid var(--danger);
        }

        .btn-toggle-on:hover {
            background: var(--danger);
            color: white;
        }

        .btn-toggle-off {
            background: transparent;
            color: #52c7b8;
            border: 1px solid var(--success);
        }

        .btn-toggle-off:hover {
            background: var(--success);
            color: white;
        }
    </style>
</head>

<body>

    <header class="topbar">
        <a href="../admin/dashboard.php" class="topbar-brand">SABOR BRASIL <span
                style="font-size: 14px; font-weight: normal; color: var(--text-secondary);">| Catálogo</span></a>
        <div class="topbar-user">
            <span>Gestão: <strong><?php echo h($_SESSION['usuario_nome']); ?></strong></span>
            <span class="badge-perfil"><?php echo h($_SESSION['usuario_perfil']); ?></span>
            <a href="../logout.php" class="btn-sair">Sair</a>
        </div>
    </header>

    <main class="container">
        <div class="page-header">
            <div>
                <h2>Catálogo de Produtos e Menu</h2>
                <p style="color: var(--text-secondary); font-size: 13px;">Cadastre, edite ou gerencie a disponibilidade
                    dos itens no salão em tempo real.</p>
            </div>
            <div>
                <a href="form.php" class="btn-submit"
                    style="display: inline-block; width: auto; text-decoration: none; padding: 10px 18px; margin: 0; font-size: 14px;">
                    Novo Produto
                </a>
            </div>
        </div>

        <table class="tabela-cardapio">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome do Produto</th>
                    <th>Categoria</th>
                    <th>Preço</th>
                    <th>Status no Salão</th>
                    <th>Ações Gerenciais</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($itens)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 35px;">Nenhum
                            produto cadastrado no catálogo ainda.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($itens as $item): ?>
                        <tr>
                            <td><?php echo str_pad($item['id'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <strong
                                    style="font-size: 15px; color: var(--text-primary);"><?php echo h($item['nome']); ?></strong><br>
                                <span
                                    style="font-size: 12px; color: var(--text-secondary);"><?php echo h($item['descricao']); ?></span>
                            </td>
                            <td><span class="badge-categoria"><?php echo h($item['categoria']); ?></span></td>
                            <td><strong style="color: var(--text-primary);">R$
                                    <?php echo number_format($item['preco'], 2, ',', '.'); ?></strong></td>
                            <td>
                                <?php if ($item['disponivel'] == 1): ?>
                                    <span class="status-ativo">Disponível</span>
                                <?php else: ?>
                                    <span class="status-inativo">Indisponível (Em falta)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="form.php?id=<?php echo $item['id']; ?>" class="btn-acao btn-editar">Editar</a>

                                <?php if ($item['disponivel'] == 1): ?>
                                    <a href="toggle_status.php?id=<?php echo $item['id']; ?>" class="btn-acao btn-toggle-on"
                                        onclick="return confirm('Deseja retirar este produto da tela de pedidos dos garçons temporariamente?');">Desativar</a>
                                <?php else: ?>
                                    <a href="toggle_status.php?id=<?php echo $item['id']; ?>"
                                        class="btn-acao btn-toggle-off">Ativar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

</body>

</html>