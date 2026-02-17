<?php
session_start();
include "config/db.php";

if (!isset($_SESSION["utilizador_id"])) {
    header("Location: login.php");
    exit;
}

$tipo_selecionado = isset($_GET['tipo']) ? $_GET['tipo'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST["titulo"]);
    $descricao = trim($_POST["descricao"]);
    $tipo = $_POST["tipo"];
    $bairro = $_POST["bairro"];
    $user_id = $_SESSION["utilizador_id"];
    $imagem_base64 = null;

    // Processar upload de imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['imagem']['type'];
        $file_size = $_FILES['imagem']['size'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $image_data = file_get_contents($_FILES['imagem']['tmp_name']);
            $imagem_base64 = 'data:' . $file_type . ';base64,' . base64_encode($image_data);
        } else {
            $erro = "Imagem inválida. Use JPG, PNG, GIF ou WEBP (max 5MB).";
        }
    }

    if (!isset($erro)) {
        $titulo_escaped = $conn->real_escape_string($titulo);
        $descricao_escaped = $conn->real_escape_string($descricao);
        $tipo_escaped = $conn->real_escape_string($tipo);
        $imagem_escaped = $imagem_base64 ? $conn->real_escape_string($imagem_base64) : null;

        if ($imagem_escaped) {
            $sql = "INSERT INTO ocorrencias (titulo, descricao, imagem, tipo, bairro_id, utilizador_id)
                    VALUES ('$titulo_escaped', '$descricao_escaped', '$imagem_escaped', '$tipo_escaped', '$bairro', '$user_id')";
        } else {
            $sql = "INSERT INTO ocorrencias (titulo, descricao, tipo, bairro_id, utilizador_id)
                    VALUES ('$titulo_escaped', '$descricao_escaped', '$tipo_escaped', '$bairro', '$user_id')";
        }

        if ($conn->query($sql)) {
            $sucesso = "Ocorrência registada com sucesso!";
        } else {
            $erro = "Erro ao registar: " . $conn->error;
        }
    }
}

$nome = htmlspecialchars($_SESSION["nome"]);
$iniciais = strtoupper(substr($_SESSION["nome"], 0, 2));
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Ocorrência | Sistema Comunitário</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="page-wrapper">

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="dashboard.php" class="navbar-brand">
                <div class="navbar-logo">SC</div>
                <div>
                    <div class="navbar-title">Sistema Comunitário</div>
                    <div class="navbar-subtitle">Gestão de Ocorrências</div>
                </div>
            </a>

            <button class="navbar-toggle" onclick="toggleNav()">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="navbar-nav" id="navbarNav">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="minhas_ocorrencias.php" class="nav-link">Minhas Ocorrências</a>
                <a href="index.php" class="nav-link">Ver Todas</a>
                <div class="nav-user">
                    <div class="user-avatar"><?= $iniciais ?></div>
                    <span class="user-name"><?= $nome ?></span>
                    <a href="logout.php" class="btn btn-outline btn-sm">Sair</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="container">
            <h1>Nova Ocorrência</h1>
            <p>Preencha os dados abaixo para registar uma nova ocorrência na sua comunidade</p>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="container container-md">

            <?php if (isset($sucesso)): ?>
                <div class="alert alert-success fade-in">
                    <?= htmlspecialchars($sucesso) ?>
                    <div class="mt-2">
                        <a href="minhas_ocorrencias.php" class="btn btn-success btn-sm">Ver Minhas Ocorrências</a>
                        <a href="nova_ocorrencia.php" class="btn btn-outline btn-sm">Registar Outra</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($erro)): ?>
                <div class="alert alert-error fade-in"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <div class="form-container fade-in" style="max-width: 100%;">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label" for="titulo">Título da Ocorrência</label>
                        <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ex: Falta de água na Rua Principal" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" class="form-control" placeholder="Descreva o problema com o máximo de detalhes possível..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="imagem">Imagem (opcional)</label>
                        <input type="file" id="imagem" name="imagem" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                        <small class="text-muted">Formatos: JPG, PNG, GIF, WEBP. Tamanho máximo: 5MB</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="tipo">Tipo de Ocorrência</label>
                        <select id="tipo" name="tipo" class="form-control" required>
                            <option value="">Selecione o tipo</option>
                            <option value="Água" <?= $tipo_selecionado == 'Água' ? 'selected' : '' ?>>Água</option>
                            <option value="Energia" <?= $tipo_selecionado == 'Energia' ? 'selected' : '' ?>>Energia</option>
                            <option value="Lixo" <?= $tipo_selecionado == 'Lixo' ? 'selected' : '' ?>>Lixo</option>
                            <option value="Segurança" <?= $tipo_selecionado == 'Segurança' ? 'selected' : '' ?>>Segurança</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="bairro">Bairro</label>
                        <select id="bairro" name="bairro" class="form-control" required>
                            <option value="">Selecione o bairro</option>
                            <?php
                            $res = $conn->query("SELECT * FROM bairros ORDER BY nome");
                            while ($b = $res->fetch_assoc()) {
                                echo "<option value='{$b['id']}'>{$b['nome']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary btn-block btn-lg">Registar Ocorrência</button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <a href="dashboard.php" class="text-muted">Voltar ao Dashboard</a>
                </div>
            </div>

        </div>
    </main>

    <!-- FOOTER -->
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

    <script>
        function toggleNav() {
            document.getElementById('navbarNav').classList.toggle('active');
        }
    </script>

</body>
</html>
