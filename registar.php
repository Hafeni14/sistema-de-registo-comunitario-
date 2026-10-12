<?php
session_start();
include "config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome            = trim($_POST["nome"]);
    $email           = trim($_POST["email"]);
    $telefone        = preg_replace('/\s+/', '', trim($_POST["telefone"]));
    $numero_bi       = strtoupper(trim($_POST["numero_bi"]));
    $data_nascimento = trim($_POST["data_nascimento"]);
    $senha           = $_POST["senha"];

    $idade = (new DateTime())->diff(new DateTime($data_nascimento))->y;

    if ($idade < 18) {
        $erro = "Deve ter pelo menos 18 anos para se registar.";
    } elseif (!preg_match('/^(\+244|00244|0)?9[0-9]{8}$/', $telefone)) {
        $erro = "Número de telefone inválido. Exemplo: +244 923 456 789";
    } elseif (!preg_match('/^\d{9}[A-Z]{2}\d{3}$/', $numero_bi)) {
        $erro = "Número de BI inválido. Formato: 000123456LA041";
    } else {
        $email_safe = $conn->real_escape_string($email);
        $bi_safe    = $conn->real_escape_string($numero_bi);
        $tel_safe   = $conn->real_escape_string($telefone);
        $nome_safe  = $conn->real_escape_string($nome);
        $data_safe  = $conn->real_escape_string($data_nascimento);

        $res = $conn->query("SELECT id FROM utilizadores WHERE email='$email_safe' OR numero_bi='$bi_safe' OR telefone='$tel_safe'");

        if ($res->num_rows > 0) {
            $erro = "Email, BI ou telefone já se encontram registados.";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $conn->query("INSERT INTO utilizadores (nome, email, senha, telefone, numero_bi, data_nascimento)
                          VALUES ('$nome_safe', '$email_safe', '$senha_hash', '$tel_safe', '$bi_safe', '$data_safe')")
            or die($conn->error);
            header("Location: login.php");
            exit;
        }
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
                    <input type="text" id="nome" name="nome" class="form-control"
                           placeholder="O seu nome completo" required
                           value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control"
                           placeholder="seu@email.com" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="telefone">Número de Telefone</label>
                    <input type="tel" id="telefone" name="telefone" class="form-control"
                           placeholder="+244 923 456 789" required
                           value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
                    <small class="form-hint">Formato angolano: +244 9XX XXX XXX</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="numero_bi">Número do Bilhete de Identidade</label>
                    <input type="text" id="numero_bi" name="numero_bi" class="form-control"
                           placeholder="000123456LA041" maxlength="14"
                           style="text-transform:uppercase" required
                           value="<?= htmlspecialchars($_POST['numero_bi'] ?? '') ?>">
                    <small class="form-hint">9 dígitos + 2 letras + 3 dígitos</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="data_nascimento">Data de Nascimento</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" class="form-control"
                           required max="<?= date('Y-m-d', strtotime('-18 years')) ?>"
                           value="<?= htmlspecialchars($_POST['data_nascimento'] ?? '') ?>">
                    <small class="form-hint">Idade mínima: 18 anos</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control"
                           placeholder="Mínimo 6 caracteres" required minlength="6">
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
