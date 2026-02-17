<?php
session_start();
include "config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);

    // Verifica se já existe o email
    $res = $conn->query("SELECT * FROM utilizadores WHERE email='$email'");
    if ($res->num_rows > 0) {
        $erro = "Este email já está registado!";
    } else {
        $conn->query("INSERT INTO utilizadores (nome, email, senha)
                      VALUES ('$nome', '$email', '$senha')") or die($conn->error);
        header("Location: login.php"); // redireciona para login
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta | Sistema Comunitário</title>
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
                <h1>Criar conta</h1>
                <p>Junte-se à comunidade</p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label" for="nome">Nome completo</label>
                    <input type="text" id="nome" name="nome" class="form-control" placeholder="O seu nome" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="seu@email.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6">
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">Criar conta</button>
                </div>

                <div class="form-link">
                    Já tem uma conta? <a href="login.php">Entrar</a>
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
