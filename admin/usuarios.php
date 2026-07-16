<?php
require_once '../utils/session_helper.php';
require_once '../config/db.php';

verificar_perfil('admin');

try {

    $stmt = $pdo->query("SELECT id, nome, login, perfil, ativo, criado_em FROM usuarios ORDER BY perfil ASC, nome ASC");
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar lista de usuários: " . h($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipe e Operadores - SABOR BRASIL</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .grid-equipe {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin-top: 20px;
        }
        @media (max-width: 850px) {
            .grid-equipe { grid-template-columns: 1fr; }
        }
        .box-card {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 25px;
        }
        .tabela-equipe {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .tabela-equipe th, .tabela-equipe td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        .tabela-equipe th {
            color: var(--text-secondary);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-admin {
            background: rgba(244, 162, 97, 0.15);
            color: #f8c390;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-garcom {
            background: rgba(42, 157, 143, 0.15);
            color: #52c7b8;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-ativo { color: var(--success); font-weight: 600; font-size: 13px; }
        .status-inativo { color: var(--danger); font-weight: 600; font-size: 13px; }
        
        .btn-acao {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            transition: 0.2s;
        }
        .btn-toggle-on {
            background: transparent;
            color: #f8b4b2;
            border: 1px solid var(--danger);
        }
        .btn-toggle-on:hover { background: var(--danger); color: white; }
        
        .btn-toggle-off {
            background: transparent;
            color: #52c7b8;
            border: 1px solid var(--success);
        }
        .btn-toggle-off:hover { background: var(--success); color: white; }
    </style>
</head>
<body>

<header class="topbar">
    <a href="dashboard.php" class="topbar-brand">SABOR BRASIL <span style="font-size: 14px; font-weight: normal; color: var(--text-secondary);">| Equipe</span></a>
    <div class="topbar-user">
        <span>Gestão: <strong><?php echo h($_SESSION['usuario_nome']); ?></strong></span>
        <span class="badge-perfil"><?php echo h($_SESSION['usuario_perfil']); ?></span>
        <a href="../logout.php" class="btn-sair">Sair</a>
    </div>
</header>

<main class="container">
    <div class="page-header">
        <div>
            <h2>Gestão de Equipe e Operadores</h2>
            <p style="color: var(--text-secondary); font-size: 13px;">Cadastre novos garçons ou administradores e gerencie o controle de acessos.</p>
        </div>
    </div>

    <div class="grid-equipe">
        
        <div class="box-card">
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 15px;">Cadastrar Novo Operador</h3>
            
            <form action="salvar_usuario.php" method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="nome">Nome Completo *</label>
                    <input type="text" id="nome" name="nome" class="form-control" placeholder="Ex: Lucas Silva" required autofocus>
                </div>

                <div class="form-group">
                    <label for="login">Nome de Usuário (Login) *</label>
                    <input type="text" id="login" name="login" class="form-control" placeholder="Ex: lucas.silva" required>
                    <small style="color: var(--text-secondary); font-size: 11px; margin-top: 4px; display: block;">Identificador único para acesso ao sistema.</small>
                </div>

                <div class="form-group">
                    <label for="senha">Senha de Acesso *</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="••••••••" required minlength="4">
                </div>

                <div class="form-group">
                    <label for="perfil">Perfil de Acesso *</label>
                    <select id="perfil" name="perfil" class="form-control" required style="cursor: pointer;">
                        <option value="garcom">Garçom (Operações do salão e comandas)</option>
                        <option value="admin">Administrador (Acesso integral ao ERP)</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit" style="margin-top: 15px; font-size: 14px;">
                    Cadastrar e Liberar Acesso
                </button>
            </form>
        </div>

        <div class="box-card">
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 15px;">Operadores e Gestores Cadastrados</h3>
            
            <table class="tabela-equipe">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Operador / Login</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?php echo str_pad($u['id'], 3, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <strong style="color: var(--text-primary); font-size: 14px;"><?php echo h($u['nome']); ?></strong><br>
                                <span style="font-size: 12px; color: var(--text-secondary);">@<?php echo h($u['login']); ?></span>
                            </td>
                            <td>
                                <?php if ($u['perfil'] === 'admin'): ?>
                                    <span class="badge-admin">Admin</span>
                                <?php else: ?>
                                    <span class="badge-garcom">Garçom</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['ativo'] == 1): ?>
                                    <span class="status-ativo">Ativo</span>
                                <?php else: ?>
                                    <span class="status-inativo">Bloqueado</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                
                                <?php if ($u['id'] == $_SESSION['usuario_id']): ?>
                                    <span style="font-size: 11px; color: var(--text-secondary); font-style: italic;">(Sua conta)</span>
                                <?php else: ?>
                                    <?php if ($u['ativo'] == 1): ?>
                                        <a href="toggle_usuario.php?id=<?php echo $u['id']; ?>" class="btn-acao btn-toggle-on" onclick="return confirm('Tem certeza que deseja bloquear o acesso do operador <?php echo h($u['nome']); ?>?');">Bloquear</a>
                                    <?php else: ?>
                                        <a href="toggle_usuario.php?id=<?php echo $u['id']; ?>" class="btn-acao btn-toggle-off">Liberar</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

</body>
</html>