<?php
session_start();
include "config/db.php";

// Se houver pedido de mudança de estado
if (isset($_GET['resolver'])) {
    $id = intval($_GET['resolver']);
    $conn->query("UPDATE ocorrencias SET estado='Resolvido' WHERE id=$id");
    header("Location: index.php");
    exit;
}

// Buscar todas ocorrências
$sql = "
SELECT o.*, b.nome AS bairro, u.nome AS utilizador
FROM ocorrencias o
JOIN bairros b ON o.bairro_id = b.id
JOIN utilizadores u ON o.utilizador_id = u.id
ORDER BY o.data_registo DESC
";

$res = $conn->query($sql);
?>

<link rel="stylesheet" href="css/style.css">

<h2>Ocorrências Comunitárias</h2>

<?php if (isset($_SESSION['nome'])): ?>
    <p>Olá, <?= htmlspecialchars($_SESSION['nome']) ?>! | 
       <a href="dashboard.php">Dashboard</a> | 
       <a href="logout.php">Sair</a>
    </p>
<?php else: ?>
    <p><a href="login.php">Login</a> | <a href="registar.php">Registar</a></p>
<?php endif; ?>

<?php
if ($res->num_rows == 0) {
    echo "<p>Não existem ocorrências registadas.</p>";
} else {
    while ($o = $res->fetch_assoc()) { ?>
        <div class="ocorrencia">
            <strong><?= htmlspecialchars($o['titulo']) ?></strong><br>
            <?= htmlspecialchars($o['descricao']) ?><br>
            Tipo: <?= htmlspecialchars($o['tipo']) ?><br>
            Bairro: <?= htmlspecialchars($o['bairro']) ?><br>
            Registada por: <?= htmlspecialchars($o['utilizador']) ?><br>
            Estado: <?= htmlspecialchars($o['estado']) ?><br>

            <?php if ($o['estado'] == 'Pendente'): ?>
                <a href="index.php?resolver=<?= $o['id'] ?>">✅ Marcar como Resolvido</a>
            <?php endif; ?>
        </div>
<?php } } ?>
