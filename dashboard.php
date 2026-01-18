<?php
session_start();
if (!isset($_SESSION["utilizador_id"])) {
    header("Location: login.php");
    exit;
}
?>

<link rel="stylesheet" href="css/style.css">

<header>
    Sistema de Registo de Ocorrências Comunitárias
</header>

<div class="container">
    <h2>Bem-vindo, <?= htmlspecialchars($_SESSION["nome"]) ?></h2>

    <div class="dashboard">
        <div class="card">
            <img src="images/agua.png">
            <h3>Água</h3>
            <p>Problemas de abastecimento</p>
            <a class="botao" href="nova_ocorrencia.php">Registar</a>
        </div>

        <div class="card">
            <img src="images/energia.png">
            <h3>Energia</h3>
            <p>Falhas elétricas</p>
            <a class="botao" href="nova_ocorrencia.php">Registar</a>
        </div>

        <div class="card">
            <img src="images/lixo.png">
            <h3>Lixo</h3>
            <p>Problemas de saneamento</p>
            <a class="botao" href="nova_ocorrencia.php">Registar</a>
        </div>

        <div class="card">
            <img src="images/seguranca.png">
            <h3>Segurança</h3>
            <p>Questões de risco</p>
            <a class="botao" href="nova_ocorrencia.php">Registar</a>
        </div>
    </div>

    <a class="botao" href="minhas_ocorrencias.php">📋 Minhas Ocorrências</a>
    <a class="botao" href="index.php">🌍 Ver Todas</a>
    <a class="botao" href="logout.php">🚪 Sair</a>
</div>
