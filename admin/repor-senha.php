<?php
include("../config/db.php");
include("../config/helpers.php");
session_start();

if (!isAdminNacional()) {
    header("Location: ../login.php");
    exit;
}

$admin_nome = htmlspecialchars($_SESSION['nome']);
$admin_iniciais = strtoupper(substr($_SESSION['nome'], 0, 2));

$erro = null;
$sucesso = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $nova_senha = $_POST['nova_senha'] ?? '';

    if ($email === '' || strlen($nova_senha) < 6) {
        $erro = "Indica o email da conta e uma nova senha com pelo menos 6 caracteres.";
    } else {
        $email_safe = $conn->real_escape_string($email);
        $res = $conn->query("SELECT id, nome FROM utilizadores WHERE email = '$email_safe'");
        $utilizador = $res ? $res->fetch_assoc() : null;

        if (!$utilizador) {
            $erro = "Não existe nenhuma conta com este email.";
        } else {
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $conn->query("UPDATE utilizadores SET senha = '$senha_hash' WHERE id = " . (int) $utilizador['id']);
            $sucesso = "Senha de \"{$utilizador['nome']}\" reposta com sucesso. Partilha a nova senha com a pessoa por um canal seguro.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repor Senha de Utilizador | Sistema Comunitário</title>
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
                    <div class="navbar-subtitle">Repor Senha</div>
                </div>
            </a>

            <button class="navbar-toggle" onclick="toggleNav()">
                <span></span><span></span><span></span>
            </button>

            <div class="navbar-nav" id="navbarNav">
                <a href="../responsavel/ocorrencias.php" class="nav-link">Painel Nacional</a>
                <a href="responsaveis.php" class="nav-link">Responsáveis</a>
                <a href="repor-senha.php" class="nav-link active">Repor Senha</a>
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
            <h1>Repor Senha de Utilizador</h1>
            <p>Para contas sem BI associado (ex: responsáveis) que não conseguem usar a recuperação normal</p>
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

            <div class="table-container fade-in" style="max-width: 480px; margin: 0 auto; padding: 1.5rem;">
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label" for="email">Email da conta</label>
                        <input type="email" id="email" name="email" class="form-control" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="nova_senha">Nova senha</label>
                        <input type="password" id="nova_senha" name="nova_senha" class="form-control"
                               placeholder="Mínimo 6 caracteres" required minlength="6">
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary btn-block">Repor senha</button>
                    </div>
                </form>
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
