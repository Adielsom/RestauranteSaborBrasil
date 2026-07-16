<?php
require_once 'utils/session_helper.php';
require_once 'config/db.php';

if (isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_perfil'])) {
    if ($_SESSION['usuario_perfil'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: mesas/mapa.php");
    }
    exit();
}

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($login) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        try {
           
            $sql = "SELECT id, nome, login, senha, perfil, ativo FROM usuarios WHERE login = :login LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':login', $login, PDO::PARAM_STR);
            $stmt->execute();

            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha'])) {

               
                if ($usuario['ativo'] == 0) {
                    $erro = "Seu acesso foi desativado. Consulte o administrador.";
                } else {
                    
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nome'] = $usuario['nome'];
                    $_SESSION['usuario_login'] = $usuario['login'];
                    $_SESSION['usuario_perfil'] = $usuario['perfil'];

                   
                    if ($usuario['perfil'] === 'admin') {
                        header("Location: admin/dashboard.php");
                    } else {
                        header("Location: mesas/mapa.php");
                    }
                    exit();
                }
            } else {
                $erro = "Login ou senha incorretos.";
            }
        } catch (PDOException $e) {
            $erro = "Erro no sistema ao tentar fazer login.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso - SABOR BRASIL</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="login-container">
        <div class="login-card">
            <h1>SABOR BRASIL 🇧🇷</h1>
            <p>Sistema de Gestão Gastronômica & Salão</p>

            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger">
                    ⚠️ <?php echo h($erro); ?>
                </div>
            <?php endif; ?>

            <form action="index.php" method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="login">Usuário de Acesso</label>
                    <input type="text" id="login" name="login" class="form-control" placeholder="Digite seu login"
                        required autofocus>
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-submit">Entrar no Sistema</button>
            </form>

            <div class="login-footer">
                <strong>SABOR BRASIL · Operações & PDV</strong><br>
                Disciplina de Programação para Internet I
            </div>
        </div>
    </div>

</body>

</html>