<?php
require_once '../utils/session_helper.php';
require_once '../config/db.php';

verificar_perfil('admin');

$id = "";
$nome = "";
$categoria = "Pratos Principais";
$descricao = "";
$preco = "";
$titulo_tela = "Novo Produto no Catálogo";

if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM cardapio WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $item = $stmt->fetch();

            if ($item) {
                $nome = $item['nome'];
                $categoria = $item['categoria'];
                $descricao = $item['descricao'];
                $preco = $item['preco'];
                $titulo_tela = "Editar Produto #00" . $id;
            } else {
                header("Location: listar.php");
                exit();
            }
        } catch (PDOException $e) {
            die("Erro ao carregar produto para edição: " . h($e->getMessage()));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($titulo_tela); ?> - SABOR BRASIL</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .form-box {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 35px;
            max-width: 600px;
            margin: 20px auto;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }
    </style>
</head>

<body>

    <header class="topbar">
        <a href="listar.php" class="topbar-brand">SABOR BRASIL <span
                style="font-size: 14px; font-weight: normal; color: var(--text-secondary);">| Catálogo</span></a>
        <div class="topbar-user">
            <span>Gestão: <strong><?php echo h($_SESSION['usuario_nome']); ?></strong></span>
            <span class="badge-perfil"><?php echo h($_SESSION['usuario_perfil']); ?></span>
        </div>
    </header>

    <main class="container">
        <div class="page-header" style="max-width: 600px; margin: 0 auto 20px auto; border: none; padding: 0;">
            <div>
                <h2><?php echo h($titulo_tela); ?></h2>
                <p style="color: var(--text-secondary); font-size: 13px;">Preencha as informações do produto para
                    disponibilização no sistema.</p>
            </div>
        </div>

        <div class="form-box">
            <form action="salvar.php" method="POST">
                <input type="hidden" name="id" value="<?php echo h($id); ?>">

                <div class="form-group">
                    <label for="nome">Nome do Produto *</label>
                    <input type="text" id="nome" name="nome" class="form-control"
                        placeholder="Ex: Picanha na Grelha 500g" value="<?php echo h($nome); ?>" required autofocus>
                </div>

                <div class="form-group">
                    <label for="categoria">Categoria *</label>
                    <select id="categoria" name="categoria" class="form-control" required style="cursor: pointer;">
                        <option value="Entradas" <?php if ($categoria === 'Entradas')
                            echo 'selected'; ?>>Entradas
                        </option>
                        <option value="Pratos Principais" <?php if ($categoria === 'Pratos Principais')
                            echo 'selected'; ?>>Pratos Principais</option>
                        <option value="Sobremesas" <?php if ($categoria === 'Sobremesas')
                            echo 'selected'; ?>>Sobremesas
                        </option>
                        <option value="Bebidas" <?php if ($categoria === 'Bebidas')
                            echo 'selected'; ?>>Bebidas</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="preco">Preço de Venda (R$) *</label>
                    <input type="number" id="preco" name="preco" class="form-control" placeholder="Ex: 45.90"
                        step="0.01" min="0.01" value="<?php echo h($preco); ?>" required>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição dos Ingredientes ou Detalhes (Opcional)</label>
                    <textarea id="descricao" name="descricao" class="form-control" rows="3"
                        placeholder="Ex: Acompanha arroz branco, farofa artesanal e vinagrete."><?php echo h($descricao); ?></textarea>
                </div>

                <div style="display: flex; gap: 15px; margin-top: 25px;">
                    <a href="listar.php"
                        style="flex: 1; text-align: center; padding: 12px; border: 1px solid var(--border); border-radius: 6px; color: var(--text-secondary); text-decoration: none; font-size: 14px; font-weight: 600;">Cancelar</a>
                    <button type="submit" class="btn-submit" style="flex: 2; margin: 0; font-size: 15px;">
                        Salvar Produto
                    </button>
                </div>
            </form>
        </div>
    </main>

</body>

</html>