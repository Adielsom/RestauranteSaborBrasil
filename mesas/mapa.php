<?php
require_once '../utils/session_helper.php';
require_once '../config/db.php';

verificar_login();

try {
    $stmt = $pdo->query("SELECT * FROM mesas ORDER BY numero ASC");
    $mesas = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar as mesas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salão de Mesas - SABOR BRASIL</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <header class="topbar">
        <a href="mapa.php" class="topbar-brand">SABOR BRASIL <span
                style="font-size: 14px; font-weight: normal; color: var(--text-secondary);">| Salão</span></a>
        <div class="topbar-user">
            <span>Olá, <strong><?php echo h($_SESSION['usuario_nome']); ?></strong></span>
            <span class="badge-perfil"><?php echo h($_SESSION['usuario_perfil']); ?></span>

            <?php if ($_SESSION['usuario_perfil'] === 'admin'): ?>
                <a href="../admin/dashboard.php"
                    style="color: var(--accent); text-decoration: none; font-weight: bold; font-size: 13px;">⚙️ Painel
                    Admin</a>
            <?php endif; ?>

            <a href="../logout.php" class="btn-sair">Sair</a>
        </div>
    </header>

   
    <main class="container">
        <div class="page-header">
            <div>
                <h2>Mapa de Mesas do Salão</h2>
                <p style="color: var(--text-secondary); font-size: 13px;">Selecione uma mesa para abrir ou gerenciar um
                    pedido.</p>
            </div>
        </div>

        
        <div class="mesas-grid">
            <?php foreach ($mesas as $mesa): ?>
                <?php
                
                if ($mesa['status'] === 'livre') {
                    $classe_status = 'status-livre';
                    $texto_status = 'Livre';
                } elseif ($mesa['status'] === 'ocupada') {
                    $classe_status = 'status-ocupada';
                    $texto_status = 'Ocupada';
                } else {
                    $classe_status = 'status-aguardando';
                    $texto_status = 'Aguardando Pagamento';
                }
                ?>

                <a href="abrir.php?id=<?php echo $mesa['id']; ?>" class="mesa-card <?php echo $classe_status; ?>">
                    <div>
                        <span
                            style="font-size: 12px; color: var(--text-secondary); text-transform: uppercase; font-weight: bold;">Mesa</span>
                        <div class="mesa-numero"><?php echo str_pad($mesa['numero'], 2, '0', STR_PAD_LEFT); ?></div>
                    </div>
                    <div>
                        <span class="mesa-status"><?php echo $texto_status; ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </main>

</body>

</html>