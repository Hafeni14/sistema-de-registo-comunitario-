<?php
session_start();
include "config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];

    $res = $conn->query("SELECT * FROM utilizadores WHERE email='$email'");
    $u = $res->fetch_assoc();

    if ($u && password_verify($senha, $u["senha"])) {
        $_SESSION["utilizador_id"] = $u["id"];
        $_SESSION["nome"] = $u["nome"];
        $_SESSION["is_admin"] = !empty($u["is_admin"]) ? 1 : 0;
        header("Location: dashboard.php");
        exit;
    } else {
        $erro = "Email ou senha incorretos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar | Sistema Comunitário</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">

    <div class="auth-content">
        <div class="auth-card fade-in">
            <div class="auth-logo">
                <div class="logo-icon">SC</div>
                <h1>Bem-vindo de volta</h1>
                <p>Entre na sua conta para continuar</p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="seu@email.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">Entrar</button>
                </div>

                <div class="form-link">
                    Não tem uma conta? <a href="registar.php">Criar conta</a>
                </div>
            </form>
        </div>
    </div>

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

</body>
</html>
