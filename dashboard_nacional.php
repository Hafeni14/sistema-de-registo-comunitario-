<?php
session_start();
include "config/db.php";
include "config/helpers.php";

// Só o admin nacional vê o panorama do país inteiro
if (!isAdminNacional()) {
    header("Location: login.php");
    exit;
}

$admin_nome = htmlspecialchars($_SESSION['nome']);
$admin_iniciais = strtoupper(substr($_SESSION['nome'], 0, 2));

// Totais gerais
$total = $conn->query("SELECT COUNT(*) t FROM ocorrencias")->fetch_assoc()['t'];
$resolvidas = $conn->query("SELECT COUNT(*) t FROM ocorrencias WHERE estado='Resolvida'")->fetch_assoc()['t'];
$pendentes = $conn->query("SELECT COUNT(*) t FROM ocorrencias WHERE estado='Pendente'")->fetch_assoc()['t'];
$taxa_resolucao = $total > 0 ? round(($resolvidas / $total) * 100) : 0;

// Ocorrências por província (só províncias com pelo menos 1 ocorrência aparecem no topo)
$por_provincia = $conn->query("
    SELECT p.nome AS provincia, COUNT(o.id) AS total,
           SUM(o.estado = 'Pendente') AS pendentes,
           SUM(o.estado = 'Resolvida') AS resolvidas
    FROM provincias p
    LEFT JOIN ocorrencias o ON o.provincia_id = p.id
    GROUP BY p.id
    ORDER BY total DESC, p.nome ASC
");

// Ocorrências por tipo
$por_tipo = [];
$res_tipo = $conn->query("SELECT tipo, COUNT(*) AS total FROM ocorrencias GROUP BY tipo ORDER BY total DESC");
while ($row = $res_tipo->fetch_assoc()) {
    $por_tipo[$row['tipo']] = $row['total'];
}

// Ranking de municípios com mais ocorrências pendentes
$ranking_municipios = $conn->query("
    SELECT m.nome AS municipio, p.nome AS provincia, COUNT(o.id) AS pendentes
    FROM municipios m
    JOIN provincias p ON m.provincia_id = p.id
    JOIN ocorrencias o ON o.municipio_id = m.id AND o.estado = 'Pendente'
    GROUP BY m.id
    ORDER BY pendentes DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Nacional | Sistema Comunitário</title>
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
                    <div class="navbar-subtitle">Dashboard Nacional</div>
                </div>
            </a>

            <button class="navbar-toggle" onclick="toggleNav()">
                <span></span><span></span><span></span>
            </button>

            <div class="navbar-nav" id="navbarNav">
                <a href="responsavel/ocorrencias.php" class="nav-link">Painel Nacional</a>
                <a href="admin/responsaveis.php" class="nav-link">Responsáveis</a>
                <a href="admin/repor-senha.php" class="nav-link">Repor Senha</a>
                <a href="dashboard_nacional.php" class="nav-link active">Dashboard Nacional</a>
                <div class="nav-user">
                    <div class="user-avatar"><?= $admin_iniciais ?></div>
                    <span class="user-name"><?= $admin_nome ?></span>
                    <span class="admin-badge">Admin Nacional</span>
                    <a href="logout.php" class="btn btn-outline btn-sm">Sair</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="container">
            <h1>Dashboard Nacional</h1>
            <p>Panorama das ocorrências em todas as 21 províncias</p>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="container">

            <!-- TAXA DE RESOLUÇÃO -->
            <div class="rate-card fade-in">
                <div>
                    <div class="rate-card-title">Taxa de Resolução Nacional</div>
                    <div class="rate-card-value"><?= $taxa_resolucao ?>%</div>
                    <div class="rate-card-sub"><?= $resolvidas ?> de <?= $total ?> ocorrências resolvidas em todo o país</div>
                </div>
                <div class="rate-bar-wrap">
                    <div class="rate-bar-bg">
                        <div class="rate-bar-fill" style="width: <?= $taxa_resolucao ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- STATS GERAIS -->
            <div class="stats-grid fade-in" style="margin-bottom: 2rem;">
                <div class="stat-card">
                    <div class="stat-card-label">Total Nacional</div>
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

            <!-- POR TIPO -->
            <?php if (!empty($por_tipo)): ?>
            <div class="table-container fade-in" style="margin-bottom: 1.5rem; padding: 1.25rem 1.5rem;">
                <h3 style="margin-bottom: 1rem; font-size: 0.9375rem; color: var(--nu-gray-600);">Ocorrências por Categoria (Nacional)</h3>
                <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                    <?php foreach ($por_tipo as $tipo => $qty):
                        $tipo_class = strtolower(str_replace(['ç', 'á', 'ã', 'ú', 'ê'], ['c', 'a', 'a', 'u', 'e'], $tipo));
                        $pct = $total > 0 ? round(($qty / $total) * 100) : 0;
                    ?>
                    <div style="flex: 1; min-width: 140px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.3rem;">
                            <span class="tag tag-<?= $tipo_class ?>"><?= htmlspecialchars($tipo) ?></span>
                            <span style="font-weight: 700; font-size: 0.9375rem;"><?= $qty ?> <small style="font-weight: 400; color: var(--nu-gray-500);">(<?= $pct ?>%)</small></span>
                        </div>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-fill" style="width: <?= $pct ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- POR PROVÍNCIA -->
            <div class="table-container fade-in" style="margin-bottom: 1.5rem;">
                <div class="table-header">
                    <h2 class="section-title mb-0">Ocorrências por Província</h2>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Província</th>
                                <th>Total</th>
                                <th>Pendentes</th>
                                <th>Resolvidas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($p = $por_provincia->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['provincia']) ?></td>
                                    <td><?= (int) $p['total'] ?></td>
                                    <td><?= (int) $p['pendentes'] ?></td>
                                    <td><?= (int) $p['resolvidas'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- RANKING DE MUNICÍPIOS COM MAIS PENDENTES -->
            <div class="table-container fade-in">
                <div class="table-header">
                    <h2 class="section-title mb-0">Municípios com mais ocorrências pendentes</h2>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Município</th>
                                <th>Província</th>
                                <th>Pendentes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($ranking_municipios->num_rows === 0): ?>
                                <tr>
                                    <td colspan="4" class="text-center p-4">
                                        <div class="text-muted">Sem ocorrências pendentes de momento</div>
                                    </td>
                                </tr>
                            <?php else: $i = 1; while ($m = $ranking_municipios->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($m['municipio']) ?></td>
                                    <td><?= htmlspecialchars($m['provincia']) ?></td>
                                    <td><?= (int) $m['pendentes'] ?></td>
                                </tr>
                            <?php endwhile; endif; ?>
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
                <div class="footer-text">© <?= date('Y') ?> Painel de Administração</div>
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
