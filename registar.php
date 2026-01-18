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
        $erro = "Email já registado!";
    } else {
        $conn->query("INSERT INTO utilizadores (nome, email, senha)
                      VALUES ('$nome', '$email', '$senha')") or die($conn->error);
        header("Location: login.php"); // redireciona para login
        exit;
    }
}
?>

<link rel="stylesheet" href="css/style.css">

<h2>Registar Utilizador</h2>

<?php if (isset($erro)) { echo "<p style='color:red'>$erro</p>"; } ?>

<form method="POST">
    <input type="text" name="nome" placeholder="Nome" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="senha" placeholder="Senha" required>
    <button type="submit">Registar</button>
</form>
