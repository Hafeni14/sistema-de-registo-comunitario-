<?php
session_start();
include "config/db.php";

if (!isset($_SESSION["utilizador_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["utilizador_id"];

$sql = "
SELECT o.*, b.nome AS bairro
FROM ocorrencias o
JOIN bairros b ON o.bairro_id = b.id
WHERE o.utilizador_id = $user_id
ORDER BY o.data_registo DESC
";

$res = $conn->query($sql);
?>

<link rel="stylesheet" href="css/style.css">

<h2>Minhas Ocorrências</h2>

<?php if ($res->num_rows == 0) {
    echo "<p>Não existem ocorrências registadas.</p>";
} else {
    while ($o = $res->fetch_assoc()) { ?>
        <div class="ocorrencia">
            <strong><?= htmlspecialchars($o['titulo']) ?></strong><br>
            <?= htmlspecialchars($o['descricao']) ?><br>
            Bairro: <?= htmlspecialchars($o['bairro']) ?> | Estado: <?= htmlspecialchars($o['estado']) ?><br>
            Registada em: <?= $o['data_registo'] ?>
        </div>
<?php } } ?>
