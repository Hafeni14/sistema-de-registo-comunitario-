<?php
include("../config/db.php");
session_start();

/* Acoes */
if(isset($_GET['resolver'])){
    $id = (int) $_GET['resolver'];
    mysqli_query($conn, "UPDATE ocorrencias SET estado='Resolvida' WHERE id=$id");
    header("Location: ocorrencias.php");
    exit;
}

/* Contadores */
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM ocorrencias"))['t'];
$pendentes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM ocorrencias WHERE estado!='Resolvida'"))['t'];
$resolvidas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM ocorrencias WHERE estado='Resolvida'"))['t'];

$result = mysqli_query($conn, "
    SELECT o.*, b.nome AS bairro, u.nome AS utilizador
    FROM ocorrencias o
    LEFT JOIN bairros b ON o.bairro_id = b.id
    LEFT JOIN utilizadores u ON o.utilizador_id = u.id
    ORDER BY o.data_registo DESC
");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Técnico | Sistema Comunitário</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="page-wrapper">

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="ocorrencias.php" class="navbar-brand">
                <div class="navbar-logo">SC</div>
                <div>
                    <div class="navbar-title">Sistema Comunitário</div>
                    <div class="navbar-subtitle">Painel Técnico</div>
                </div>
            </a>

            <button class="navbar-toggle" onclick="toggleNav()">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="navbar-nav" id="navbarNav">
                <a href="ocorrencias.php" class="nav-link active">Ocorrências</a>
                <a href="../index.php" class="nav-link">Ver Site</a>
                <div class="nav-user">
                    <div class="user-avatar">RT</div>
                    <span class="user-name">Responsável Técnico</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="container">
            <h1>Painel de Gestão</h1>
            <p>Gerencie todas as ocorrências comunitárias registadas no sistema</p>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="container">

            <!-- STATS GRID -->
            <div class="stats-grid fade-in">
                <div class="stat-card">
                    <div class="stat-card-label">Total de Ocorrências</div>
                    <div class="stat-card-value"><?= $total ?></div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-card-label">Pendentes</div>
                    <div class="stat-card-value"><?= $pendentes ?></div>
                </div>

                <div class="stat-card success">
                    <div class="stat-card-label">Resolvidas</div>
                    <div class="stat-card-value"><?= $resolvidas ?></div>
                </div>
            </div>

            <!-- TABLE SECTION -->
            <div class="table-container fade-in">
                <div class="table-header">
                    <h2 class="section-title mb-0">Lista de Ocorrências</h2>
                    <span class="text-muted"><?= $total ?> registos encontrados</span>
                </div>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Tipo</th>
                                <th>Bairro</th>
                                <th>Reportado por</th>
                                <th>Data</th>
                                <th>Estado</th>
                                <th>Imagem</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) == 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center p-4">
                                        <div class="text-muted">Nenhuma ocorrência encontrada</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php while($o = mysqli_fetch_assoc($result)): 
                                    $tipo_class = strtolower(str_replace(['ç', 'á', 'ã'], ['c', 'a', 'a'], $o['tipo']));
                                    $is_resolved = $o['estado'] == 'Resolvida';
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($o['titulo']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="tag tag-<?= $tipo_class ?>">
                                            <?= htmlspecialchars($o['tipo']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($o['bairro'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($o['utilizador'] ?? '-') ?></td>
                                    <td><?= date('d/m/Y', strtotime($o['data_registo'])) ?></td>
                                    <td>
                                        <span class="status-badge <?= $is_resolved ? 'resolved' : 'pending' ?>">
                                            <?= htmlspecialchars($o['estado']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($o['imagem'])): ?>
                                            <a href="<?= $o['imagem'] ?>" target="_blank" class="btn btn-outline btn-sm">Ver</a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(!$is_resolved): ?>
                                            <a class="btn btn-success btn-sm" href="?resolver=<?= $o['id'] ?>">
                                                Resolver
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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
                <div class="footer-text">© <?= date('Y') ?> Painel Técnico - Todos os direitos reservados</div>
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
