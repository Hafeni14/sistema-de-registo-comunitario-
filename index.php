<?php
session_start();
include "config/db.php";
include "config/helpers.php";

$is_logged_in = isset($_SESSION['utilizador_id']);
$user_id = $is_logged_in ? $_SESSION['utilizador_id'] : null;
$nome = $is_logged_in ? htmlspecialchars($_SESSION['nome']) : '';
$iniciais = $is_logged_in ? strtoupper(substr($_SESSION['nome'], 0, 2)) : '';

// Processar ações (like, comentário, resolver)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_logged_in) {
    // Adicionar/remover like
    if (isset($_POST['like_ocorrencia_id'])) {
        $oc_id = intval($_POST['like_ocorrencia_id']);
        
        // Verificar se já deu like
        $check = $conn->query("SELECT id FROM likes WHERE ocorrencia_id = $oc_id AND utilizador_id = $user_id");
        if ($check->num_rows > 0) {
            // Remover like
            $conn->query("DELETE FROM likes WHERE ocorrencia_id = $oc_id AND utilizador_id = $user_id");
        } else {
            // Adicionar like
            $conn->query("INSERT INTO likes (ocorrencia_id, utilizador_id) VALUES ($oc_id, $user_id)");
        }
        header("Location: index.php#ocorrencia-$oc_id");
        exit;
    }
    
    // Adicionar comentário
    if (isset($_POST['comentario_ocorrencia_id']) && isset($_POST['comentario_texto'])) {
        $oc_id = intval($_POST['comentario_ocorrencia_id']);
        $texto = trim($_POST['comentario_texto']);
        
        if (!empty($texto)) {
            $texto_escaped = $conn->real_escape_string($texto);
            $conn->query("INSERT INTO comentarios (ocorrencia_id, utilizador_id, texto) VALUES ($oc_id, $user_id, '$texto_escaped')");
        }
        header("Location: index.php#ocorrencia-$oc_id");
        exit;
    }
}

// Resolver ocorrência foi movido para o painel de administração
// Apenas admins podem mudar o estado das ocorrências

// Buscar todas ocorrências
$sql = "
SELECT o.*, b.nome AS bairro, u.nome AS utilizador
FROM ocorrencias o
JOIN bairros b ON o.bairro_id = b.id
JOIN utilizadores u ON o.utilizador_id = u.id
ORDER BY o.data_registo DESC
";

$res = $conn->query($sql);
$total = $res->num_rows;

// Função para obter likes de uma ocorrência
function getLikes($conn, $ocorrencia_id, $user_id = null) {
    $count = $conn->query("SELECT COUNT(*) as total FROM likes WHERE ocorrencia_id = $ocorrencia_id")->fetch_assoc()['total'];
    $user_liked = false;
    if ($user_id) {
        $user_liked = $conn->query("SELECT id FROM likes WHERE ocorrencia_id = $ocorrencia_id AND utilizador_id = $user_id")->num_rows > 0;
    }
    return ['count' => $count, 'user_liked' => $user_liked];
}

// Função para obter comentários de uma ocorrência
function getComentarios($conn, $ocorrencia_id, $is_logged_in, $limit = null) {
    $sql = "SELECT c.*, u.nome AS autor 
            FROM comentarios c 
            JOIN utilizadores u ON c.utilizador_id = u.id 
            WHERE c.ocorrencia_id = $ocorrencia_id 
            ORDER BY c.data_registo DESC";
    
    if ($limit) {
        $sql .= " LIMIT $limit";
    }
    
    return $conn->query($sql);
}

// Função para contar total de comentários
function countComentarios($conn, $ocorrencia_id) {
    return $conn->query("SELECT COUNT(*) as total FROM comentarios WHERE ocorrencia_id = $ocorrencia_id")->fetch_assoc()['total'];
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ocorrências Comunitárias | Sistema Comunitário</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="page-wrapper">

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand">
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
                <?php if ($is_logged_in): ?>
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="minhas_ocorrencias.php" class="nav-link">Minhas Ocorrências</a>
                    <a href="index.php" class="nav-link active">Ver Todas</a>
                    <?php if (!empty($_SESSION['is_admin'])): ?>
                        <a href="responsavel/ocorrencias.php" class="nav-link" style="color: var(--nu-purple); font-weight: 700;">Painel Admin</a>
                    <?php endif; ?>
                    <div class="nav-user">
                        <div class="user-avatar"><?= $iniciais ?></div>
                        <span class="user-name"><?= $nome ?></span>
                        <?php if (!empty($_SESSION['is_admin'])): ?>
                            <span class="admin-badge">Admin</span>
                        <?php endif; ?>
                        <a href="logout.php" class="btn btn-outline btn-sm">Sair</a>
                    </div>
                <?php else: ?>
                    <a href="index.php" class="nav-link active">Ocorrências</a>
                    <a href="login.php" class="btn btn-outline btn-sm">Entrar</a>
                    <a href="registar.php" class="btn btn-primary btn-sm">Criar Conta</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero hero-bg">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <h1>Ocorrências Comunitárias</h1>
            <p>Juntos construímos uma comunidade melhor. Veja todas as ocorrências registadas e acompanhe o progresso das resoluções.</p>
            
            <?php if (!$is_logged_in): ?>
                <div class="btn-group justify-center">
                    <a href="registar.php" class="btn btn-secondary">Criar Conta</a>
                    <a href="login.php" class="btn btn-outline" style="border-color: rgba(255,255,255,0.5); color: white;">Entrar</a>
                </div>
            <?php else: ?>
                <a href="nova_ocorrencia.php" class="btn btn-secondary">+ Registar Nova Ocorrencia</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="container">

            <!-- SECTION HEADER -->
            <div class="section-header">
                <h2 class="section-title mb-0">Todas as Ocorrências</h2>
                <span class="text-muted"><?= $total ?> ocorrência(s) registada(s)</span>
            </div>

            <?php if ($total == 0): ?>
                <!-- EMPTY STATE -->
                <div class="empty-state fade-in">
                    <div class="empty-state-icon"></div>
                    <h3>Nenhuma ocorrência registada</h3>
                    <p>Ainda não existem ocorrências no sistema. Seja o primeiro a registar!</p>
                    <?php if ($is_logged_in): ?>
                        <a href="nova_ocorrencia.php" class="btn btn-primary">Registar Primeira Ocorrência</a>
                    <?php else: ?>
                        <a href="registar.php" class="btn btn-primary">Criar Conta para Registar</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- OCCURRENCE LIST -->
                <div class="ocorrencia-list fade-in">
                    <?php while ($o = $res->fetch_assoc()):
                        $tipo_class = strtolower(str_replace('ç', 'c', str_replace('á', 'a', $o['tipo'])));
                        $status_class = getStatusClass($o['estado']);
                        $likes = getLikes($conn, $o['id'], $user_id);
                        $total_comentarios = countComentarios($conn, $o['id']);
                        $limit_comentarios = $is_logged_in ? null : 5;
                        $comentarios = getComentarios($conn, $o['id'], $is_logged_in, $limit_comentarios);
                    ?>
                        <div class="ocorrencia-card" id="ocorrencia-<?= $o['id'] ?>">
                            <div class="ocorrencia-header">
                                <h3 class="ocorrencia-title"><?= htmlspecialchars($o['titulo']) ?></h3>
                                <span class="status-badge <?= $status_class ?>">
                                    <?= htmlspecialchars($o['estado']) ?>
                                </span>
                            </div>
                            
                            <p class="ocorrencia-description"><?= htmlspecialchars($o['descricao']) ?></p>
                            
                            <?php if (!empty($o['imagem'])): ?>
                            <div class="ocorrencia-image">
                                <img src="<?= $o['imagem'] ?>" alt="Imagem da ocorrência">
                            </div>
                            <?php endif; ?>
                            
                            <div class="ocorrencia-meta">
                                <span class="ocorrencia-meta-item">
                                    <span class="tag tag-<?= $tipo_class ?>"><?= htmlspecialchars($o['tipo']) ?></span>
                                </span>
                                <span class="ocorrencia-meta-item">
                                    <?= htmlspecialchars($o['bairro']) ?>
                                </span>
                                <span class="ocorrencia-meta-item">
                                    <?= htmlspecialchars($o['utilizador']) ?>
                                </span>
                                <span class="ocorrencia-meta-item">
                                    <?= date('d/m/Y H:i', strtotime($o['data_registo'])) ?>
                                </span>
                            </div>

                            <!-- INTERAÇÕES: LIKES E COMENTÁRIOS -->
                            <div class="ocorrencia-interactions">
                                <!-- Botão de Like -->
                                <div class="interaction-buttons">
                                    <?php if ($is_logged_in): ?>
                                        <form method="POST" class="like-form">
                                            <input type="hidden" name="like_ocorrencia_id" value="<?= $o['id'] ?>">
                                            <button type="submit" class="btn-like <?= $likes['user_liked'] ? 'liked' : '' ?>">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="<?= $likes['user_liked'] ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                                </svg>
                                                <span><?= $likes['count'] ?></span>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <a href="login.php" class="btn-like" title="Faça login para dar like">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                            </svg>
                                            <span><?= $likes['count'] ?></span>
                                        </a>
                                    <?php endif; ?>

                                    <span class="interaction-separator">|</span>

                                    <span class="comments-count">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                        </svg>
                                        <span><?= $total_comentarios ?> comentário(s)</span>
                                    </span>
                                </div>

                                <!-- Seção de Comentários -->
                                <div class="comentarios-section">
                                    <?php if ($total_comentarios > 0): ?>
                                        <div class="comentarios-list">
                                            <?php while ($c = $comentarios->fetch_assoc()): ?>
                                                <div class="comentario">
                                                    <div class="comentario-header">
                                                        <span class="comentario-autor"><?= htmlspecialchars($c['autor']) ?></span>
                                                        <span class="comentario-data"><?= date('d/m/Y H:i', strtotime($c['data_registo'])) ?></span>
                                                    </div>
                                                    <p class="comentario-texto"><?= htmlspecialchars($c['texto']) ?></p>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                        
                                        <?php if (!$is_logged_in && $total_comentarios > 5): ?>
                                            <div class="comentarios-login-prompt">
                                                <a href="login.php">Faça login para ver todos os <?= $total_comentarios ?> comentários</a>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p class="no-comentarios">Ainda não há comentários. Seja o primeiro!</p>
                                    <?php endif; ?>

                                    <!-- Formulário de Comentário -->
                                    <?php if ($is_logged_in): ?>
                                        <form method="POST" class="comentario-form">
                                            <input type="hidden" name="comentario_ocorrencia_id" value="<?= $o['id'] ?>">
                                            <div class="comentario-input-group">
                                                <input type="text" name="comentario_texto" class="form-control" placeholder="Escreva um comentário..." required>
                                                <button type="submit" class="btn btn-primary btn-sm">Enviar</button>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <div class="comentario-login-prompt">
                                            <a href="login.php" class="btn btn-outline btn-sm">Faça login para comentar</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($is_logged_in && $o['utilizador_id'] == $user_id && isClosed($o['estado']) === false): ?>
                                <div class="ocorrencia-actions" style="display:flex; align-items:center; gap:.75rem;">
                                    <span style="font-size:0.8125rem; color: var(--nu-gray-500);">
                                        A sua ocorrência está a ser acompanhada pela administração.
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>

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
                <div class="footer-links">
                    <a href="index.php">Ocorrências</a>
                    <?php if ($is_logged_in): ?>
                        <a href="dashboard.php">Dashboard</a>
                        <a href="minhas_ocorrencias.php">Minhas Ocorrências</a>
                    <?php else: ?>
                        <a href="login.php">Entrar</a>
                        <a href="registar.php">Criar Conta</a>
                    <?php endif; ?>
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
