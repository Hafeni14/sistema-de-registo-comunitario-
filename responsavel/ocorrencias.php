<?php
include("../config/db.php");
session_start();

/* Ações */
if(isset($_GET['resolver'])){
    $id = (int) $_GET['resolver'];
    mysqli_query($conn, "UPDATE ocorrencias SET estado='Resolvida' WHERE id=$id");
}

/* Contadores */
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM ocorrencias"))['t'];
$pendentes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM ocorrencias WHERE estado!='Resolvida'"))['t'];
$resolvidas = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) t FROM ocorrencias WHERE estado='Resolvida'"))['t'];

$result = mysqli_query($conn, "
    SELECT * FROM ocorrencias
    ORDER BY data_registo DESC
");
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Responsável Técnico Municipal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header>
    <div class="container">
        <h1>Responsável Técnico</h1>
        <p>Painel de gestão de ocorrências comunitárias</p>
    </div>
</header>

<div class="container">

    <!-- PAINEL DE RESUMO -->
    <div class="painel">
        <div class="painel-card">
            <h3>Total de Ocorrências</h3>
            <p><?= $total ?></p>
        </div>

        <div class="painel-card">
            <h3>Ocorrências Pendentes</h3>
            <p><?= $pendentes ?></p>
        </div>

        <div class="painel-card">
            <h3>Ocorrências Resolvidas</h3>
            <p><?= $resolvidas ?></p>
        </div>
    </div>

    <!-- TABELA -->
    <h2>Lista de Ocorrências</h2>

    <table>
        <tr>
            <th>Título</th>
            <th>Tipo</th>
            <th>Estado</th>
            <th>Ação</th>
        </tr>

        <?php while($o = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= htmlspecialchars($o['titulo']) ?></td>
            <td>
                <span class="tag tag-<?= $o['tipo'] ?>">
                    <?= ucfirst($o['tipo']) ?>
                </span>
            </td>
            <td class="<?= $o['estado']=='Resolvida' ? 'estado-resolvida' : 'estado-pendente' ?>">
                <?= $o['estado'] ?>
            </td>
            <td>
                <?php if($o['estado'] != 'Resolvida'): ?>
                    <a class="btn btn-sec" href="?resolver=<?= $o['id'] ?>">
                        Marcar como Resolvida
                    </a>
                <?php else: ?>
                    —
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

</div>

<footer>
    © Plataforma Municipal de Ocorrências Comunitárias
</footer>

</body>
</html>
