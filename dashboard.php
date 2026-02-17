<?php
session_start();
if (!isset($_SESSION["utilizador_id"])) {
    header("Location: login.php");
    exit;
}

$nome = htmlspecialchars($_SESSION["nome"]);
$iniciais = strtoupper(substr($_SESSION["nome"], 0, 2));
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sistema Comunitário</title>
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
                <a href="dashboard.php" class="nav-link active">Dashboard</a>
                <a href="minhas_ocorrencias.php" class="nav-link">Minhas Ocorrências</a>
                <a href="index.php" class="nav-link">Ver Todas</a>
                <div class="nav-user">
                    <div class="user-avatar"><?= $iniciais ?></div>
                    <span class="user-name"><?= $nome ?></span>
                    <a href="logout.php" class="btn btn-outline btn-sm">Sair</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="container">

            <!-- WELCOME SECTION -->
            <div class="welcome-section fade-in">
                <h2>Olá, <?= $nome ?>!</h2>
                <p>Bem-vindo ao sistema de registo de ocorrências comunitárias. Selecione uma categoria abaixo para registar uma nova ocorrência.</p>
            </div>

            <!-- SECTION HEADER -->
            <div class="section-header">
                <h2 class="section-title mb-0">Registar Ocorrência</h2>
                <a href="nova_ocorrencia.php" class="btn btn-primary">+ Nova Ocorrência</a>
            </div>

            <!-- CATEGORY CARDS -->
            <div class="dashboard-grid fade-in">
                <a href="nova_ocorrencia.php?tipo=Água" class="dashboard-card">
                    <div class="card-image-box">
                        <img src="imagens/water.jpg" alt="Água" style="width: 100%; height: 80px; object-fit: cover; border-radius: 8px;">
                    </div>
                    <h3>Água</h3>
                    <p>Problemas de abastecimento de água</p>
                    <span class="btn btn-primary btn-sm">Registar</span>
                </a>

                <a href="nova_ocorrencia.php?tipo=Energia" class="dashboard-card">
                    <div class="card-image-box">
                        <img src="imagens/eletricity.jpg" alt="Energia" style="width: 100%; height: 80px; object-fit: cover; border-radius: 8px;">
                    </div>
                    <h3>Energia</h3>
                    <p>Falhas elétricas e iluminação</p>
                    <span class="btn btn-primary btn-sm">Registar</span>
                </a>

                <a href="nova_ocorrencia.php?tipo=Lixo" class="dashboard-card">
                    <div class="card-image-box">
                        <img src="imagens/trash.jpg" alt="Lixo" style="width: 100%; height: 80px; object-fit: cover; border-radius: 8px;">
                    </div>
                    <h3>Lixo</h3>
                    <p>Problemas de saneamento</p>
                    <span class="btn btn-primary btn-sm">Registar</span>
                </a>

                <a href="nova_ocorrencia.php?tipo=Segurança" class="dashboard-card">
                    <div class="card-image-box">
                        <img src="imagens/security.png" alt="Segurança" style="width: 100%; height: 80px; object-fit: cover; border-radius: 8px;">
                    </div>
                    <h3>Segurança</h3>
                    <p>Questões de risco público</p>
                    <span class="btn btn-primary btn-sm">Registar</span>
                </a>
            </div>

            <!-- QUICK ACTIONS -->
            <div class="section-header mt-4">
                <h2 class="section-title mb-0">Ações Rápidas</h2>
            </div>

            <div class="quick-actions fade-in">
                <a href="minhas_ocorrencias.php" class="btn btn-secondary">
                    Minhas Ocorrências
                </a>
                <a href="index.php" class="btn btn-outline">
                    Ver Todas as Ocorrências
                </a>
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
                <div class="footer-links">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="minhas_ocorrencias.php">Minhas Ocorrências</a>
                    <a href="index.php">Ver Todas</a>
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
