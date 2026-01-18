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
        header("Location: dashboard.php");
        exit;
    } else {
        $erro = "Email ou senha incorretos!";
    }
}
?>

<link rel="stylesheet" href="css/style.css">

<h2>Login</h2>

<?php if (isset($erro)) { echo "<p style='color:red'>$erro</p>"; } ?>

<form method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="senha" placeholder="Senha" required>
    <button type="submit">Entrar</button>
</form>
