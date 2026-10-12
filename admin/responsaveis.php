<?php
include("../config/db.php");
include("../config/helpers.php");
session_start();

// Só o admin nacional pode gerir responsáveis
if (!isAdminNacional()) {
    header("Location: ../login.php");
    exit;
}

$admin_nome = htmlspecialchars($_SESSION['nome']);
$admin_iniciais = strtoupper(substr($_SESSION['nome'], 0, 2));

$erro = null;
$sucesso = null;

// Criar novo responsável
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'criar') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $municipio_id = (int) ($_POST['municipio_id'] ?? 0);

    if ($nome === '' || $email === '' || strlen($senha) < 6 || $municipio_id <= 0) {
        $erro = "Preenche o nome, email, uma senha com pelo menos 6 caracteres, e escolhe um município.";
    } else {
        $email_safe = $conn->real_escape_string($email);
        $existe = $conn->query("SELECT id FROM utilizadores WHERE email = '$email_safe'");
        if ($existe->num_rows > 0) {
            $erro = "Já existe uma conta com este email.";
        } else {
            $nome_safe = $conn->real_escape_string($nome);
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $conn->query("INSERT INTO utilizadores (nome, email, senha, nivel_acesso, municipio_id)
                          VALUES ('$nome_safe', '$email_safe', '$senha_hash', 'responsavel', $municipio_id)");
            $sucesso = "Responsável \"$nome\" criado com sucesso.";
        }
    }
}

// Revogar responsável (volta a cidadão comum)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'revogar') {
    $id = (int) ($_POST['utilizador_id'] ?? 0);
    if ($id > 0) {
        $conn->query("UPDATE utilizadores SET nivel_acesso = 'cidadao', municipio_id = NULL, is_admin = 0 WHERE id = $id");
        $sucesso = "Acesso de responsável revogado.";
    }
}

// Lista de municípios para o formulário, agrupados por província
$municipios_res = $conn->query("
    SELECT m.id, m.nome AS municipio, p.nome AS provincia
    FROM municipios m
    JOIN provincias p ON m.provincia_id = p.id
    ORDER BY p.nome, m.nome
");
$municipios_por_provincia = [];
while ($m = $municipios_res->fetch_assoc()) {
    $municipios_por_provincia[$m['provincia']][] = $m;
}

// Lista de responsáveis já existentes
$responsaveis_res = $conn->query("
    SELECT u.id, u.nome, u.email, m.nome AS municipio, p.nome AS provincia
    FROM utilizadores u
    JOIN municipios m ON u.municipio_id = m.id
    JOIN provincias p ON m.provincia_id = p.id
    WHERE u.nivel_acesso = 'responsavel'
    ORDER BY p.nome, m.nome, u.nome
");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Responsáveis | Sistema Comunitário</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="page-wrapper">

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="../dashboard.php" class="navbar-brand">
                <div class="navbar-logo">SC</div>
                <div>
                    <div class="navbar-title">Sistema Comunitário</div>
                    <div class="navbar-subtitle">Gestão de Responsáveis</div>
                </div>
            </a>

            <button class="navbar-toggle" onclick="toggleNav()">
                <span></span><span></span><span></span>
            </button>

            <div class="navbar-nav" id="navbarNav">
                <a href="../responsavel/ocorrencias.php" class="nav-link">Painel Nacional</a>
                <a href="responsaveis.php" class="nav-link active">Responsáveis</a>
                <a href="repor-senha.php" class="nav-link">Repor Senha</a>
                <a href="../dashboard_nacional.php" class="nav-link">Dashboard Nacional</a>
                <div class="nav-user">
                    <div class="user-avatar"><?= $admin_iniciais ?></div>
                    <span class="user-name"><?= $admin_nome ?></span>
                    <span class="admin-badge">Admin Nacional</span>
                    <a href="../logout.php" class="btn btn-outline btn-sm">Sair</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="container">
            <h1>Gestão de Responsáveis</h1>
            <p>Cria e atribui contas de responsável a cada município</p>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="container">

            <?php if ($erro): ?>
                <div class="alert alert-error fade-in"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>
            <?php if ($sucesso): ?>
                <div class="alert alert-success fade-in"><?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>

            <!-- FORMULÁRIO DE CRIAÇÃO -->
            <div class="table-container fade-in" style="margin-bottom: 2rem; padding: 1.5rem;">
                <h2 class="section-title" style="margin-bottom: 1rem;">Criar novo responsável</h2>
                <form method="POST">
                    <input type="hidden" name="acao" value="criar">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="nome">Nome completo</label>
                            <input type="text" id="nome" name="nome" class="form-control" required
                                   value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="senha">Senha</label>
                            <input type="password" id="senha" name="senha" class="form-control"
                                   placeholder="Mínimo 6 caracteres" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="municipio_id">Município</label>
                            <select id="municipio_id" name="municipio_id" class="form-control" required>
                                <option value="">Selecione um município</option>
                                <?php foreach ($municipios_por_provincia as $provincia => $municipios): ?>
                                    <optgroup label="<?= htmlspecialchars($provincia) ?>">
                                        <?php foreach ($municipios as $m): ?>
                                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['municipio']) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-footer" style="margin-top: 1rem;">
                        <button type="submit" class="btn btn-primary">Criar responsável</button>
                    </div>
                </form>
            </div>

            <!-- LISTA DE RESPONSÁVEIS -->
            <div class="table-container fade-in">
                <div class="table-header">
                    <h2 class="section-title mb-0">Responsáveis atuais</h2>
                    <span class="text-muted"><?= $responsaveis_res->num_rows ?> responsável(is)</span>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Município</th>
                                <th>Província</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($responsaveis_res->num_rows === 0): ?>
                                <tr>
                                    <td colspan="5" class="text-center p-4">
                                        <div class="text-muted">Ainda não há responsáveis criados</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php while ($r = $responsaveis_res->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['nome']) ?></td>
                                        <td><?= htmlspecialchars($r['email']) ?></td>
                                        <td><?= htmlspecialchars($r['municipio']) ?></td>
                                        <td><?= htmlspecialchars($r['provincia']) ?></td>
                                        <td>
                                            <form method="POST" onsubmit="return confirm('Revogar o acesso de responsável desta conta?');">
                                                <input type="hidden" name="acao" value="revogar">
                                                <input type="hidden" name="utilizador_id" value="<?= $r['id'] ?>">
                                                <button type="submit" class="btn btn-outline btn-sm">Revogar</button>
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
