<?php
session_start();
include "config/db.php";

if (!isset($_SESSION["utilizador_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["utilizador_id"];
$nome = htmlspecialchars($_SESSION["nome"]);
$iniciais = strtoupper(substr($_SESSION["nome"], 0, 2));

$sql = "
SELECT o.*, b.nome AS bairro
FROM ocorrencias o
JOIN bairros b ON o.bairro_id = b.id
WHERE o.utilizador_id = $user_id
ORDER BY o.data_registo DESC
";

$res = $conn->query($sql);
$total = $res->num_rows;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Ocorrências | Sistema Comunitário</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="page-wrapper">

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="dashboard.php" class="navbar-brand">
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
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="minhas_ocorrencias.php" class="nav-link active">Minhas Ocorrências</a>
                <a href="index.php" class="nav-link">Ver Todas</a>
                <div class="nav-user">
                    <div class="user-avatar"><?= $iniciais ?></div>
                    <span class="user-name"><?= $nome ?></span>
                    <a href="logout.php" class="btn btn-outline btn-sm">Sair</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="container">
            <h1>Minhas Ocorrências</h1>
            <p>Acompanhe todas as ocorrências que você registou</p>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="container">

            <!-- SECTION HEADER -->
            <div class="section-header">
                <div>
                    <span class="text-muted"><?= $total ?> ocorrência(s) encontrada(s)</span>
                </div>
                <a href="nova_ocorrencia.php" class="btn btn-primary">+ Nova Ocorrência</a>
            </div>

            <?php if ($total == 0): ?>
                <!-- EMPTY STATE -->
                <div class="empty-state fade-in">
                    <div class="empty-state-icon"></div>
                    <h3>Nenhuma ocorrência registada</h3>
                    <p>Você ainda não registou nenhuma ocorrência. Comece agora!</p>
                    <a href="nova_ocorrencia.php" class="btn btn-primary">Registar Primeira Ocorrência</a>
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
                            
                            <?php if (!empty($o['imagem'])): ?>
                            <div class="ocorrencia-image">
                                <img src="<?= $o['imagem'] ?>" alt="Imagem da ocorrência">
                            </div>
                            <?php endif; ?>
                            
                            <div class="ocorrencia-meta">
                                <span class="ocorrencia-meta-item">
                                    <span class="tag tag-<?= $tipo_class ?>"><?= htmlspecialchars($o['tipo']) ?></span>
                                </span>
                                <span class="ocorrencia-meta-item">
                                    <?= htmlspecialchars($o['bairro']) ?>
                                </span>
                                <span class="ocorrencia-meta-item">
                                    <?= date('d/m/Y H:i', strtotime($o['data_registo'])) ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="dashboard.php" class="btn btn-outline">Voltar ao Dashboard</a>
            </div>

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
