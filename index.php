<?php
session_start();
include "config/db.php";

// Se houver pedido de mudança de estado
if (isset($_GET['resolver'])) {
    $id = intval($_GET['resolver']);
    $conn->query("UPDATE ocorrencias SET estado='Resolvido' WHERE id=$id");
    header("Location: index.php");
    exit;
}

// Buscar todas ocorrências
$sql = "
SELECT o.*, b.nome AS bairro, u.nome AS utilizador
FROM ocorrencias o
JOIN bairros b ON o.bairro_id = b.id
JOIN utilizadores u ON o.utilizador_id = u.id
ORDER BY o.data_registo DESC
";

$res = $conn->query($sql);
$total = $res->num_rows;

$is_logged_in = isset($_SESSION['utilizador_id']);
$nome = $is_logged_in ? htmlspecialchars($_SESSION['nome']) : '';
$iniciais = $is_logged_in ? strtoupper(substr($_SESSION['nome'], 0, 2)) : '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ocorrências Comunitárias | Sistema Comunitário</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="page-wrapper">

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand">
                <div class="navbar-logo">SC</div>
                <div>
                    <div class="navbar-title">Sistema Comunitário</div>
                    <div class="navbar-subtitle">Gestão de Ocorrências</div>
                </div>
            </a>

            <button class="navbar-toggle" onclick="toggleNav()">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="navbar-nav" id="navbarNav">
                <?php if ($is_logged_in): ?>
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="minhas_ocorrencias.php" class="nav-link">Minhas Ocorrências</a>
                    <a href="index.php" class="nav-link active">Ver Todas</a>
                    <div class="nav-user">
                        <div class="user-avatar"><?= $iniciais ?></div>
                        <span class="user-name"><?= $nome ?></span>
                        <a href="logout.php" class="btn btn-outline btn-sm">Sair</a>
                    </div>
                <?php else: ?>
                    <a href="index.php" class="nav-link active">Ocorrências</a>
                    <a href="login.php" class="btn btn-outline btn-sm">Entrar</a>
                    <a href="registar.php" class="btn btn-primary btn-sm">Criar Conta</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="container">
            <h1>Ocorrências Comunitárias</h1>
            <p>Juntos construímos uma comunidade melhor. Veja todas as ocorrências registadas e acompanhe o progresso das resoluções.</p>
            
            <?php if (!$is_logged_in): ?>
                <div class="btn-group justify-center">
                    <a href="registar.php" class="btn btn-secondary">Criar Conta</a>
                    <a href="login.php" class="btn btn-outline" style="border-color: rgba(255,255,255,0.5); color: white;">Entrar</a>
                </div>
            <?php else: ?>
                <a href="nova_ocorrencia.php" class="btn btn-secondary">+ Registar Nova Ocorrência</a>
            <?php endif; ?>

            <div class="hero-image-placeholder">
                [Imagem: Ilustração de comunidade unida, pessoas colaborando, mapa da cidade]
            </div>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="container">

            <!-- SECTION HEADER -->
            <div class="section-header">
                <h2 class="section-title mb-0">Todas as Ocorrências</h2>
                <span class="text-muted"><?= $total ?> ocorrência(s) registada(s)</span>
            </div>

            <?php if ($total == 0): ?>
                <!-- EMPTY STATE -->
                <div class="empty-state fade-in">
                    <div class="empty-state-icon">📋</div>
                    <h3>Nenhuma ocorrência registada</h3>
                    <p>Ainda não existem ocorrências no sistema. Seja o primeiro a registar!</p>
                    <?php if ($is_logged_in): ?>
                        <a href="nova_ocorrencia.php" class="btn btn-primary">Registar Primeira Ocorrência</a>
                    <?php else: ?>
                        <a href="registar.php" class="btn btn-primary">Criar Conta para Registar</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- OCCURRENCE LIST -->
                <div class="ocorrencia-list fade-in">
                    <?php while ($o = $res->fetch_assoc()): 
                        $tipo_class = strtolower(str_replace('ç', 'c', str_replace('á', 'a', $o['tipo'])));
                        $is_resolved = $o['estado'] == 'Resolvida' || $o['estado'] == 'Resolvido';
                    ?>
                        <div class="ocorrencia-card">
                            <div class="ocorrencia-header">
                                <h3 class="ocorrencia-title"><?= htmlspecialchars($o['titulo']) ?></h3>
                                <span class="status-badge <?= $is_resolved ? 'resolved' : 'pending' ?>">
                                    <?= htmlspecialchars($o['estado']) ?>
                                </span>
                            </div>
                            
                            <p class="ocorrencia-description"><?= htmlspecialchars($o['descricao']) ?></p>
                            
                            <div class="ocorrencia-meta">
                                <span class="ocorrencia-meta-item">
                                    <span class="tag tag-<?= $tipo_class ?>"><?= htmlspecialchars($o['tipo']) ?></span>
                                </span>
                                <span class="ocorrencia-meta-item">
                                    📍 <?= htmlspecialchars($o['bairro']) ?>
                                </span>
                                <span class="ocorrencia-meta-item">
                                    👤 <?= htmlspecialchars($o['utilizador']) ?>
                                </span>
                                <span class="ocorrencia-meta-item">
                                    📅 <?= date('d/m/Y H:i', strtotime($o['data_registo'])) ?>
                                </span>
                            </div>

                            <?php if (!$is_resolved && $is_logged_in): ?>
                                <div class="ocorrencia-actions">
                                    <a href="index.php?resolver=<?= $o['id'] ?>" class="btn btn-success btn-sm">
                                        ✅ Marcar como Resolvido
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="footer-logo">SC</div>
                    <span class="footer-text">Sistema Comunitário</span>
                </div>
                <div class="footer-links">
                    <a href="index.php">Ocorrências</a>
                    <?php if ($is_logged_in): ?>
                        <a href="dashboard.php">Dashboard</a>
                        <a href="minhas_ocorrencias.php">Minhas Ocorrências</a>
                    <?php else: ?>
                        <a href="login.php">Entrar</a>
                        <a href="registar.php">Criar Conta</a>
                    <?php endif; ?>
                </div>
                <div class="footer-text">© <?= date('Y') ?> Todos os direitos reservados</div>
            </div>
        </div>
    </footer>

    <script>
        function toggleNav() {
            document.getElementById('navbarNav').classList.toggle('active');
        }
    </script>

</body>
</html>
