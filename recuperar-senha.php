<?php
session_start();
include "config/db.php";

$erro = null;
$sucesso = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $numero_bi = strtoupper(trim($_POST['numero_bi'] ?? ''));
    $data_nascimento = trim($_POST['data_nascimento'] ?? '');
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    if ($email === '' || $numero_bi === '' || $data_nascimento === '') {
        $erro = "Preenche o email, o número de BI e a data de nascimento.";
    } elseif (strlen($nova_senha) < 6) {
        $erro = "A nova senha deve ter pelo menos 6 caracteres.";
    } elseif ($nova_senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        $email_safe = $conn->real_escape_string($email);
        $res = $conn->query("SELECT id, numero_bi, data_nascimento FROM utilizadores WHERE email = '$email_safe'");
        $utilizador = $res ? $res->fetch_assoc() : null;

        // Comparação feita em PHP (não na query) para não revelar, por tempo de resposta
        // ou mensagem, se foi o email, o BI ou a data que não coincidiu.
        $identidade_confirmada = $utilizador
            && !empty($utilizador['numero_bi'])
            && !empty($utilizador['data_nascimento'])
            && strtoupper($utilizador['numero_bi']) === $numero_bi
            && $utilizador['data_nascimento'] === $data_nascimento;

        if (!$identidade_confirmada) {
            $erro = "Os dados não coincidem com nenhuma conta registada. Se a tua conta foi criada por um administrador (ex: contas de responsável), não tem BI associado — contacta o admin nacional para repor a senha.";
        } else {
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $conn->query("UPDATE utilizadores SET senha = '$senha_hash' WHERE id = " . (int) $utilizador['id']);
            $sucesso = "Senha alterada com sucesso! Já podes entrar com a nova senha.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha | Sistema Comunitário</title>
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
                <h1>Recuperar senha</h1>
                <p>Confirma a tua identidade para definires uma nova senha</p>
            </div>

            <?php if ($erro): ?>
                <div class="alert alert-error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>
            <?php if ($sucesso): ?>
                <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>

            <?php if (!$sucesso): ?>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="numero_bi">Número do Bilhete de Identidade</label>
                    <input type="text" id="numero_bi" name="numero_bi" class="form-control"
                           placeholder="000123456LA041" maxlength="14" style="text-transform:uppercase" required
                           value="<?= htmlspecialchars($_POST['numero_bi'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="data_nascimento">Data de Nascimento</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" class="form-control" required
                           value="<?= htmlspecialchars($_POST['data_nascimento'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="nova_senha">Nova senha</label>
                    <input type="password" id="nova_senha" name="nova_senha" class="form-control"
                           placeholder="Mínimo 6 caracteres" required minlength="6">
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirmar_senha">Confirmar nova senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" required minlength="6">
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">Repor senha</button>
                </div>
            </form>
            <?php endif; ?>

            <div class="form-link">
                <a href="login.php">Voltar ao login</a>
            </div>
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
