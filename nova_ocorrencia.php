<?php
session_start();
include "config/db.php";
include "config/helpers.php";

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
    $imagem_path = null;

    // Processar upload de imagem — grava em disco, guarda só o caminho na BD
    // (base64 na coluna infla a base de dados ~33% por imagem; a escala nacional
    // isto pesa rápido. Ver /uploads/ocorrencias/.)
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];
        $file_type = $_FILES['imagem']['type'];
        $file_size = $_FILES['imagem']['size'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (isset($allowed_types[$file_type]) && $file_size <= $max_size) {
            $pasta_uploads = __DIR__ . '/uploads/ocorrencias';
            if (!is_dir($pasta_uploads)) {
                mkdir($pasta_uploads, 0775, true);
            }
            $nome_ficheiro = uniqid('ocorrencia_', true) . '.' . $allowed_types[$file_type];
            $destino = $pasta_uploads . '/' . $nome_ficheiro;

            if (is_writable($pasta_uploads) && move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
                $imagem_path = '/uploads/ocorrencias/' . $nome_ficheiro;
            } else {
                $erro = "Não foi possível guardar a imagem no servidor. Tenta novamente ou continua sem imagem.";
            }
        } else {
            $erro = "Imagem inválida. Use JPG, PNG, GIF ou WEBP (max 5MB).";
        }
    }

    // Derivar município/província a partir do bairro escolhido (routing geográfico)
    $geo = !isset($erro) ? obterGeografiaDoBairro($conn, $bairro) : null;
    if (!isset($erro) && !$geo) {
        $erro = "Bairro inválido. Selecione um bairro da lista.";
    }

    if (!isset($erro)) {
        $municipio_id = (int) $geo['municipio_id'];
        $provincia_id = (int) $geo['provincia_id'];
        $responsavel_id = atribuirResponsavelAutomatico($conn, $municipio_id);

        $titulo_escaped = $conn->real_escape_string($titulo);
        $descricao_escaped = $conn->real_escape_string($descricao);
        $tipo_escaped = $conn->real_escape_string($tipo);
        $imagem_escaped = $imagem_path ? $conn->real_escape_string($imagem_path) : null;
        $responsavel_sql = $responsavel_id ? (int) $responsavel_id : "NULL";

        if ($imagem_escaped) {
            $sql = "INSERT INTO ocorrencias (titulo, descricao, imagem, tipo, bairro_id, municipio_id, provincia_id, utilizador_id, responsavel_id)
                    VALUES ('$titulo_escaped', '$descricao_escaped', '$imagem_escaped', '$tipo_escaped', '$bairro', '$municipio_id', '$provincia_id', '$user_id', $responsavel_sql)";
        } else {
            $sql = "INSERT INTO ocorrencias (titulo, descricao, tipo, bairro_id, municipio_id, provincia_id, utilizador_id, responsavel_id)
                    VALUES ('$titulo_escaped', '$descricao_escaped', '$tipo_escaped', '$bairro', '$municipio_id', '$provincia_id', '$user_id', $responsavel_sql)";
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
                <?php if (!empty($_SESSION['pode_gerir'])): ?>
                    <a href="responsavel/ocorrencias.php" class="nav-link" style="color: var(--nu-purple); font-weight: 700;">Painel Admin</a>
                <?php endif; ?>
                <div class="nav-user">
                    <div class="user-avatar"><?= $iniciais ?></div>
                    <span class="user-name"><?= $nome ?></span>
                    <?php if (!empty($_SESSION['pode_gerir'])): ?>
                        <span class="admin-badge"><?= (($_SESSION['nivel_acesso'] ?? '') === 'admin_nacional') ? 'Admin Nacional' : 'Responsável' ?></span>
                    <?php endif; ?>
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
                            $res = $conn->query("
                                SELECT b.id, b.nome AS bairro, m.nome AS municipio
                                FROM bairros b
                                JOIN municipios m ON b.municipio_id = m.id
                                ORDER BY m.nome, b.nome
                            ");
                            $municipio_atual = null;
                            while ($b = $res->fetch_assoc()) {
                                if ($b['municipio'] !== $municipio_atual) {
                                    if ($municipio_atual !== null) echo "</optgroup>";
                                    echo "<optgroup label='" . htmlspecialchars($b['municipio']) . "'>";
                                    $municipio_atual = $b['municipio'];
                                }
                                echo "<option value='{$b['id']}'>" . htmlspecialchars($b['bairro']) . "</option>";
                            }
                            if ($municipio_atual !== null) echo "</optgroup>";
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
