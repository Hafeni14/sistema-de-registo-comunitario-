<?php
include("../config/db.php");
include("../config/helpers.php");
session_start();

// Somente admins podem aceder a este painel
if (empty($_SESSION['is_admin'])) {
    header("Location: ../login.php");
    exit;
}

$admin_nome = htmlspecialchars($_SESSION['nome']);
$admin_iniciais = strtoupper(substr($_SESSION['nome'], 0, 2));

// Processar mudança de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ocorrencia_id'], $_POST['novo_estado'])) {
    $id = (int) $_POST['ocorrencia_id'];
    $novo_estado = $conn->real_escape_string(trim($_POST['novo_estado']));
    if (in_array($novo_estado, $estados_disponiveis)) {
        mysqli_query($conn, "UPDATE ocorrencias SET estado='$novo_estado' WHERE id=$id");
    }
    $filtro_redirect = isset($_POST['filtro_estado']) ? '?estado=' . urlencode($_POST['filtro_estado']) : '';
    header("Location: ocorrencias.php$filtro_redirect");
    exit;
}

// Filtro de estado (URL: ?estado=Pendente, etc.)
$filtro_estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';

// Contadores por estado
$total      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM ocorrencias"))['t'];
$pendentes  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM ocorrencias WHERE estado='Pendente'"))['t'];
$em_analise = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM ocorrencias WHERE estado='Em Análise'"))['t'];
$em_progresso = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM ocorrencias WHERE estado='Em Progresso'"))['t'];
$aguardando = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM ocorrencias WHERE estado='Aguardando Recursos'"))['t'];
$resolvidas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM ocorrencias WHERE estado='Resolvida'"))['t'];
$rejeitadas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM ocorrencias WHERE estado='Rejeitada'"))['t'];
$taxa_resolucao = $total > 0 ? round(($resolvidas / $total) * 100) : 0;
$fechadas = $resolvidas + $rejeitadas;

// Contadores por tipo
$por_tipo = [];
$res_tipo = mysqli_query($conn, "SELECT tipo, COUNT(*) as total FROM ocorrencias GROUP BY tipo ORDER BY total DESC");
while ($row = mysqli_fetch_assoc($res_tipo)) {
    $por_tipo[$row['tipo']] = $row['total'];
}

// Query de ocorrências com filtro opcional
$where_sql = '';
if ($filtro_estado !== '' && in_array($filtro_estado, $estados_disponiveis)) {
    $filtro_escaped = $conn->real_escape_string($filtro_estado);
    $where_sql = "WHERE o.estado = '$filtro_escaped'";
}

$result = mysqli_query($conn, "
    SELECT o.*, b.nome AS bairro, u.nome AS utilizador
    FROM ocorrencias o
    LEFT JOIN bairros b ON o.bairro_id = b.id
    LEFT JOIN utilizadores u ON o.utilizador_id = u.id
    $where_sql
    ORDER BY o.data_registo DESC
");
$total_filtrado = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Sistema Comunitário</title>
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
                    <div class="navbar-subtitle">Painel de Administração</div>
                </div>
            </a>

            <button class="navbar-toggle" onclick="toggleNav()">
                <span></span><span></span><span></span>
            </button>

            <div class="navbar-nav" id="navbarNav">
                <a href="ocorrencias.php" class="nav-link active">Dashboard</a>
                <a href="../index.php" class="nav-link">Ver Site</a>
                <div class="nav-user">
                    <div class="user-avatar"><?= $admin_iniciais ?></div>
                    <span class="user-name"><?= $admin_nome ?></span>
                    <span class="admin-badge">Admin</span>
                    <a href="../logout.php" class="btn btn-outline btn-sm">Sair</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="container">
            <h1>Dashboard de Administração</h1>
            <p>Gerencie e atualize o estado de todas as ocorrências comunitárias</p>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="container">

            <!-- TAXA DE RESOLUÇÃO -->
            <div class="rate-card fade-in">
                <div>
                    <div class="rate-card-title">Taxa de Resolução Global</div>
                    <div class="rate-card-value"><?= $taxa_resolucao ?>%</div>
                    <div class="rate-card-sub"><?= $resolvidas ?> de <?= $total ?> ocorrências resolvidas</div>
                </div>
                <div class="rate-bar-wrap">
                    <div class="rate-bar-label">
                        <span>0%</span>
                        <span><?= $fechadas ?> fechadas (resolvidas + rejeitadas)</span>
                        <span>100%</span>
                    </div>
                    <div class="rate-bar-bg">
                        <div class="rate-bar-fill" style="width: <?= $taxa_resolucao ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- STATS POR ESTADO -->
            <div class="stats-grid fade-in">
                <div class="stat-card">
                    <div class="stat-card-label">Total de Ocorrências</div>
                    <div class="stat-card-value"><?= $total ?></div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-card-label">Pendentes</div>
                    <div class="stat-card-value"><?= $pendentes ?></div>
                </div>
                <div class="stat-card info">
                    <div class="stat-card-label">Em Análise</div>
                    <div class="stat-card-value"><?= $em_analise ?></div>
                </div>
                <div class="stat-card in-progress">
                    <div class="stat-card-label">Em Progresso</div>
                    <div class="stat-card-value"><?= $em_progresso ?></div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-card-label">Aguardando Recursos</div>
                    <div class="stat-card-value"><?= $aguardando ?></div>
                </div>
                <div class="stat-card success">
                    <div class="stat-card-label">Resolvidas</div>
                    <div class="stat-card-value"><?= $resolvidas ?></div>
                </div>
                <div class="stat-card rejected">
                    <div class="stat-card-label">Rejeitadas</div>
                    <div class="stat-card-value"><?= $rejeitadas ?></div>
                </div>
            </div>

            <!-- STATS POR TIPO -->
            <?php if (!empty($por_tipo)): ?>
            <div class="table-container fade-in" style="margin-bottom: 1.5rem; padding: 1.25rem 1.5rem;">
                <h3 style="margin-bottom: 1rem; font-size: 0.9375rem; color: var(--nu-gray-600);">Ocorrências por Categoria</h3>
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

            <!-- TABELA DE OCORRÊNCIAS -->
            <div class="table-container fade-in">
                <div class="table-header">
                    <h2 class="section-title mb-0">Lista de Ocorrências</h2>
                    <span class="text-muted"><?= $total_filtrado ?> registo(s) <?= $filtro_estado !== '' ? 'com estado "' . htmlspecialchars($filtro_estado) . '"' : 'encontrados' ?></span>
                </div>

                <!-- FILTROS POR ESTADO -->
                <div style="padding: 0 1.5rem 1rem;">
                    <div class="filter-tabs">
                        <a href="ocorrencias.php" class="filter-tab <?= $filtro_estado === '' ? 'active' : '' ?>">Todos (<?= $total ?>)</a>
                        <a href="?estado=Pendente" class="filter-tab <?= $filtro_estado === 'Pendente' ? 'active' : '' ?>">Pendente (<?= $pendentes ?>)</a>
                        <a href="?estado=Em+Análise" class="filter-tab <?= $filtro_estado === 'Em Análise' ? 'active' : '' ?>">Em Análise (<?= $em_analise ?>)</a>
                        <a href="?estado=Em+Progresso" class="filter-tab <?= $filtro_estado === 'Em Progresso' ? 'active' : '' ?>">Em Progresso (<?= $em_progresso ?>)</a>
                        <a href="?estado=Aguardando+Recursos" class="filter-tab <?= $filtro_estado === 'Aguardando Recursos' ? 'active' : '' ?>">Aguardando (<?= $aguardando ?>)</a>
                        <a href="?estado=Resolvida" class="filter-tab <?= $filtro_estado === 'Resolvida' ? 'active' : '' ?>">Resolvida (<?= $resolvidas ?>)</a>
                        <a href="?estado=Rejeitada" class="filter-tab <?= $filtro_estado === 'Rejeitada' ? 'active' : '' ?>">Rejeitada (<?= $rejeitadas ?>)</a>
                    </div>
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
                                <th>Estado Atual</th>
                                <th>Imagem</th>
                                <th>Alterar Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($total_filtrado == 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center p-4">
                                        <div class="text-muted">Nenhuma ocorrência encontrada<?= $filtro_estado !== '' ? ' com este filtro' : '' ?></div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php while ($o = mysqli_fetch_assoc($result)):
                                    $tipo_class = strtolower(str_replace(['ç', 'á', 'ã', 'ú', 'ê'], ['c', 'a', 'a', 'u', 'e'], $o['tipo']));
                                    $status_class = getStatusClass($o['estado']);
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($o['titulo']) ?></strong>
                                        <?php if (!empty($o['descricao'])): ?>
                                            <div style="font-size: 0.8rem; color: var(--nu-gray-500); margin-top: 2px; max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <?= htmlspecialchars($o['descricao']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="tag tag-<?= $tipo_class ?>">
                                            <?= htmlspecialchars($o['tipo']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($o['bairro'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($o['utilizador'] ?? '-') ?></td>
                                    <td style="white-space: nowrap;"><?= date('d/m/Y', strtotime($o['data_registo'])) ?></td>
                                    <td>
                                        <span class="status-badge <?= $status_class ?>">
                                            <?= htmlspecialchars($o['estado']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($o['imagem'])): ?>
                                            <a href="<?= $o['imagem'] ?>" target="_blank" class="btn btn-outline btn-sm">Ver</a>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" class="state-select-form">
                                            <input type="hidden" name="ocorrencia_id" value="<?= $o['id'] ?>">
                                            <input type="hidden" name="filtro_estado" value="<?= htmlspecialchars($filtro_estado) ?>">
                                            <select name="novo_estado">
                                                <?php foreach ($estados_disponiveis as $estado): ?>
                                                    <option value="<?= htmlspecialchars($estado) ?>" <?= $o['estado'] === $estado ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($estado) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm">Salvar</button>
                                        </form>
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
