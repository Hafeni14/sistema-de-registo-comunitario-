<?php
session_start();
include "config/db.php";

if (!isset($_SESSION["utilizador_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST["titulo"]);
    $descricao = trim($_POST["descricao"]);
    $tipo = $_POST["tipo"];
    $bairro = $_POST["bairro"];
    $user_id = $_SESSION["utilizador_id"];

    $sql = "INSERT INTO ocorrencias (titulo, descricao, tipo, bairro_id, utilizador_id)
            VALUES ('$titulo', '$descricao', '$tipo', '$bairro', '$user_id')";

    if ($conn->query($sql)) {
        $sucesso = "Ocorrência registada com sucesso!";
    } else {
        $erro = "Erro ao registar: " . $conn->error;
    }
}
?>

<link rel="stylesheet" href="css/style.css">

<h2>Nova Ocorrência</h2>

<?php
if (isset($sucesso)) echo "<p style='color:green'>$sucesso</p>";
if (isset($erro)) echo "<p style='color:red'>$erro</p>";
?>

<form method="POST">
    <input type="text" name="titulo" placeholder="Título" required>
    <textarea name="descricao" placeholder="Descrição" required></textarea>
    <select name="tipo" required>
        <option value="">-- Tipo --</option>
        <option>Água</option>
        <option>Energia</option>
        <option>Lixo</option>
        <option>Segurança</option>
    </select>
    <select name="bairro" required>
        <option value="">-- Bairro --</option>
        <?php
        $res = $conn->query("SELECT * FROM bairros");
        while ($b = $res->fetch_assoc()) {
            echo "<option value='{$b['id']}'>{$b['nome']}</option>";
        }
        ?>
    </select>
    <button type="submit">Registar Ocorrência</button>
</form>
