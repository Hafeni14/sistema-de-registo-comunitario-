<?php
include("config/db.php");
include("config/helpers.php");
session_start();

// Só admins nacionais e responsáveis
if (empty($_SESSION['utilizador_id']) || !podeUsarChat()) {
    header("Location: login.php");
    exit;
}

$uid = (int) $_SESSION['utilizador_id'];
$nome = htmlspecialchars($_SESSION['nome']);
$iniciais = strtoupper(substr($_SESSION['nome'], 0, 2));
$e_admin = isAdminNacional();
$csrf = chatCsrfToken();

/** Nome legível de um município (com província) */
function nomeMunicipio($conn, $id) {
    $id = (int) $id;
    $r = $conn->query("SELECT m.nome, p.nome AS provincia FROM municipios m JOIN provincias p ON m.provincia_id = p.id WHERE m.id = $id");
    $row = $r ? $r->fetch_assoc() : null;
    return $row ? $row['nome'] . ' (' . $row['provincia'] . ')' : null;
}

// Canais disponíveis: nacional + município(s)
$canais = [['chave' => 'nacional', 'titulo' => 'Canal Nacional']];
if ($e_admin) {
    // Municípios que já têm mensagens
    $r = $conn->query("SELECT DISTINCT canal_chave FROM chat_mensagens WHERE canal_chave <> 'nacional' ORDER BY canal_chave");
    while ($r && ($c = $r->fetch_assoc())) {
        $mid = (int) substr($c['canal_chave'], 1);
        if ($t = nomeMunicipio($conn, $mid)) $canais[] = ['chave' => $c['canal_chave'], 'titulo' => $t];
    }
} else {
    $mun = (int) ($_SESSION['municipio_id'] ?? 0);
    if ($mun > 0 && ($t = nomeMunicipio($conn, $mun))) {
        $canais[] = ['chave' => 'm' . $mun, 'titulo' => $t];
    }
}

// Admin pode abrir o canal de qualquer município
$canal_pedido = $_GET['canal'] ?? 'nacional';
if ($e_admin && preg_match('/^m[0-9]+$/', $canal_pedido)) {
    $titulo_extra = nomeMunicipio($conn, substr($canal_pedido, 1));
    $existe = array_filter($canais, fn($c) => $c['chave'] === $canal_pedido);
    if ($titulo_extra && !$existe) {
        $canais[] = ['chave' => $canal_pedido, 'titulo' => $titulo_extra];
    }
}
if (!podeAcederCanal($canal_pedido) || !array_filter($canais, fn($c) => $c['chave'] === $canal_pedido)) {
    $canal_pedido = 'nacional';
}
$canal_atual = $canal_pedido;
$titulo_atual = array_values(array_filter($canais, fn($c) => $c['chave'] === $canal_atual))[0]['titulo'];

// Não lidas por canal
$nao_lidas_canal = [];
foreach ($canais as $c) {
    $k = $conn->real_escape_string($c['chave']);
    $r = $conn->query("SELECT COUNT(*) AS n FROM chat_mensagens m
                       LEFT JOIN chat_leituras l ON l.utilizador_id = $uid AND l.canal_chave = m.canal_chave
                       WHERE m.canal_chave = '$k' AND m.id > COALESCE(l.ultima_id, 0) AND m.autor_id <> $uid");
    $nao_lidas_canal[$c['chave']] = $r ? (int) $r->fetch_assoc()['n'] : 0;
}

// Lista de municípios para o admin abrir outros canais
$municipios_por_provincia = [];
if ($e_admin) {
    $r = $conn->query("SELECT m.id, m.nome, p.nome AS provincia FROM municipios m JOIN provincias p ON m.provincia_id = p.id ORDER BY p.nome, m.nome");
    while ($r && ($m = $r->fetch_assoc())) $municipios_por_provincia[$m['provincia']][] = $m;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat | Sistema Comunitário</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="page-wrapper">

    <nav class="navbar">
        <div class="navbar-container">
            <a href="responsavel/ocorrencias.php" class="navbar-brand">
                <div class="navbar-logo">SC</div>
                <div>
                    <div class="navbar-title">Sistema Comunitário</div>
                    <div class="navbar-subtitle">Chat da Administração</div>
                </div>
            </a>
            <button class="navbar-toggle" onclick="document.getElementById('navbarNav').classList.toggle('active')">
                <span></span><span></span><span></span>
            </button>
            <div class="navbar-nav" id="navbarNav">
                <a href="responsavel/ocorrencias.php" class="nav-link"><?= $e_admin ? 'Painel Nacional' : 'Dashboard' ?></a>
                <?php if ($e_admin): ?>
                    <a href="admin/responsaveis.php" class="nav-link">Responsáveis</a>
                    <a href="admin/repor-senha.php" class="nav-link">Repor Senha</a>
                    <a href="dashboard_nacional.php" class="nav-link">Dashboard Nacional</a>
                <?php endif; ?>
                <?= linkChatNav($conn, '', true) ?>
                <div class="nav-user">
                    <div class="user-avatar"><?= $iniciais ?></div>
                    <span class="user-name"><?= $nome ?></span>
                    <span class="admin-badge"><?= $e_admin ? 'Admin Nacional' : 'Responsável' ?></span>
                    <a href="logout.php" class="btn btn-outline btn-sm">Sair</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <div class="container">
            <h1>Chat da Administração</h1>
            <p>Conversas privadas entre administradores nacionais e responsáveis</p>
        </div>
    </div>

    <main class="main-content">
        <div class="container">
            <div class="chat-layout">

                <aside class="chat-canais">
                    <h3>Canais</h3>
                    <?php foreach ($canais as $c): ?>
                        <a href="chat.php?canal=<?= urlencode($c['chave']) ?>"
                           class="chat-canal<?= $c['chave'] === $canal_atual ? ' ativo' : '' ?>">
                            <span><?= htmlspecialchars($c['titulo']) ?></span>
                            <?php if ($nao_lidas_canal[$c['chave']] > 0): ?>
                                <span class="chat-badge"><?= $nao_lidas_canal[$c['chave']] ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>

                    <?php if ($e_admin): ?>
                        <form method="GET" class="chat-abrir">
                            <label class="form-label" for="canal-mun">Abrir canal de município</label>
                            <select id="canal-mun" name="canal" class="form-control" onchange="if(this.value) this.form.submit()">
                                <option value="">Escolher...</option>
                                <?php foreach ($municipios_por_provincia as $prov => $muns): ?>
                                    <optgroup label="<?= htmlspecialchars($prov) ?>">
                                        <?php foreach ($muns as $m): ?>
                                            <option value="m<?= (int) $m['id'] ?>"><?= htmlspecialchars($m['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    <?php endif; ?>
                </aside>

                <section class="chat-janela">
                    <div class="chat-topo">
                        <strong><?= htmlspecialchars($titulo_atual) ?></strong>
                        <small>Os administradores nacionais podem ver todas as conversas.</small>
                    </div>
                    <div class="chat-mensagens" id="mensagens" aria-live="polite">
                        <div class="chat-vazio" id="vazio">A carregar...</div>
                    </div>
                    <form class="chat-form" id="formEnviar" autocomplete="off">
                        <textarea id="texto" class="form-control" rows="1" maxlength="2000"
                                  placeholder="Escreve uma mensagem (Enter envia, Shift+Enter nova linha)" required></textarea>
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </form>
                </section>

            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand"><div class="footer-logo">SC</div><span class="footer-text">Sistema Comunitário</span></div>
                <div class="footer-text">© <?= date('Y') ?> Chat da Administração</div>
            </div>
        </div>
    </footer>

    <script>
        const CANAL = <?= json_encode($canal_atual) ?>;
        const CSRF = <?= json_encode($csrf) ?>;
        const caixa = document.getElementById('mensagens');
        const campo = document.getElementById('texto');
        let ultimoId = 0;

        function formatarHora(s) {
            const d = new Date(s.replace(' ', 'T'));
            if (isNaN(d)) return s;
            return d.toLocaleString('pt-PT', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
        }

        function desenhar(msgs) {
            if (!msgs.length) return;
            const vazio = document.getElementById('vazio');
            if (vazio) vazio.remove();
            const primeiraCarga = ultimoId === 0;
            const noFundo = caixa.scrollHeight - caixa.scrollTop - caixa.clientHeight < 80;
            msgs.forEach(m => {
                const div = document.createElement('div');
                div.className = 'chat-msg' + (m.minha ? ' minha' : '');
                const autor = document.createElement('div');
                autor.className = 'chat-autor';
                autor.textContent = (m.minha ? 'Tu' : m.autor) + (m.nivel_acesso === 'admin_nacional' ? ' · Admin Nacional' : '') + ' · ' + formatarHora(m.criado_em);
                const corpo = document.createElement('div');
                corpo.className = 'chat-texto';
                corpo.textContent = m.texto;   // textContent: sem risco de XSS
                div.append(autor, corpo);
                caixa.appendChild(div);
                ultimoId = Math.max(ultimoId, m.id);
            });
            if (primeiraCarga || noFundo) {
                caixa.scrollTop = caixa.scrollHeight;
            }
        }

        async function carregar() {
            try {
                const r = await fetch('chat_api.php?acao=listar&canal=' + encodeURIComponent(CANAL) + '&depois_id=' + ultimoId);
                if (!r.ok) return;
                const dados = await r.json();
                if (ultimoId === 0 && !dados.mensagens.length) {
                    document.getElementById('vazio').textContent = 'Ainda não há mensagens neste canal.';
                }
                desenhar(dados.mensagens);
            } catch (e) { /* rede em baixo: tenta no próximo ciclo */ }
        }

        async function enviar() {
            const texto = campo.value.trim();
            if (!texto) return;
            const corpo = new URLSearchParams({ acao: 'enviar', canal: CANAL, texto, csrf: CSRF });
            const r = await fetch('chat_api.php', { method: 'POST', body: corpo });
            if (r.ok) { campo.value = ''; await carregar(); caixa.scrollTop = caixa.scrollHeight; }
            else { const d = await r.json().catch(() => ({})); alert(d.erro || 'Não foi possível enviar.'); }
        }

        document.getElementById('formEnviar').addEventListener('submit', e => { e.preventDefault(); enviar(); });
        campo.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); enviar(); }
        });

        carregar();
        setInterval(() => { if (!document.hidden) carregar(); }, 4000);
    </script>
</body>
</html>
