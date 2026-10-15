<?php
/**
 * Funções auxiliares partilhadas pelo Sistema Comunitário
 */

// Lista de todos os estados disponíveis
$estados_disponiveis = [
    'Pendente',
    'Em Análise',
    'Em Progresso',
    'Aguardando Recursos',
    'Resolvida',
    'Rejeitada',
];

/**
 * Retorna a classe CSS correspondente ao estado da ocorrência
 */
function getStatusClass($estado) {
    $map = [
        'Pendente'             => 'pending',
        'Em Análise'           => 'in-analysis',
        'Em Progresso'         => 'in-progress',
        'Aguardando Recursos'  => 'waiting',
        'Resolvida'            => 'resolved',
        'Rejeitada'            => 'rejected',
        // Compatibilidade com estado antigo
        'Resolvido'            => 'resolved',
    ];
    return $map[$estado] ?? 'pending';
}

/**
 * Verifica se o estado é considerado "fechado" (sem mais ações necessárias)
 */
function isClosed($estado) {
    return in_array($estado, ['Resolvida', 'Rejeitada', 'Resolvido']);
}

/**
 * Verifica se o utilizador atual é admin
 * (mantido por compatibilidade — is_admin corresponde agora a nivel_acesso = 'admin_nacional')
 */
function isAdmin() {
    return !empty($_SESSION['is_admin']);
}

/**
 * Verifica se o utilizador atual é responsável (município/bairro)
 */
function isResponsavel() {
    return ($_SESSION['nivel_acesso'] ?? null) === 'responsavel';
}

/**
 * Verifica se o utilizador atual é admin nacional
 */
function isAdminNacional() {
    return ($_SESSION['nivel_acesso'] ?? null) === 'admin_nacional';
}

/**
 * Dado um bairro, devolve o município e a província a que pertence.
 * Usado ao registar uma ocorrência para preencher o routing geográfico.
 */
function obterGeografiaDoBairro($conn, $bairro_id) {
    $bairro_id = (int) $bairro_id;
    $sql = "SELECT b.municipio_id, m.provincia_id
            FROM bairros b
            JOIN municipios m ON b.municipio_id = m.id
            WHERE b.id = $bairro_id";
    $res = $conn->query($sql);
    return $res ? $res->fetch_assoc() : null;
}

/**
 * Tenta atribuir automaticamente a ocorrência a um responsável do município.
 * Se houver mais que um responsável no mesmo município, fica sem atribuição
 * (null) e qualquer um deles pode assumi-la a partir do painel.
 */
function atribuirResponsavelAutomatico($conn, $municipio_id) {
    $municipio_id = (int) $municipio_id;
    $sql = "SELECT id FROM utilizadores
            WHERE nivel_acesso = 'responsavel' AND municipio_id = $municipio_id
            LIMIT 2";
    $res = $conn->query($sql);
    if ($res && $res->num_rows === 1) {
        return $res->fetch_assoc()['id'];
    }
    return null;
}

/* ------------------------------------------------------------------
 * Chat privado (administradores nacionais + responsáveis)
 * ------------------------------------------------------------------ */

/**
 * Verifica se o utilizador atual pode ver/escrever num canal do chat.
 * - 'nacional': admin nacional e responsáveis
 * - 'm<ID>': admin nacional (todos) ou o responsável desse município
 */
function podeAcederCanal($canal_chave) {
    if (isAdminNacional()) {
        return $canal_chave === 'nacional' || preg_match('/^m[0-9]+$/', $canal_chave) === 1;
    }
    if (isResponsavel()) {
        $mun = (int) ($_SESSION['municipio_id'] ?? 0);
        return $canal_chave === 'nacional' || ($mun > 0 && $canal_chave === 'm' . $mun);
    }
    return false;
}

/** Só admins nacionais e responsáveis usam o chat */
function podeUsarChat() {
    return isAdminNacional() || isResponsavel();
}

/**
 * Número total de mensagens não lidas (de outros) nos canais a que o utilizador tem acesso.
 */
function chatNaoLidas($conn) {
    if (!podeUsarChat()) return 0;
    $uid = (int) $_SESSION['utilizador_id'];

    if (isAdminNacional()) {
        $filtro = "1=1";
    } else {
        $mun = (int) ($_SESSION['municipio_id'] ?? 0);
        $filtro = "m.canal_chave IN ('nacional', 'm$mun')";
    }

    $sql = "SELECT COUNT(*) AS total
            FROM chat_mensagens m
            LEFT JOIN chat_leituras l ON l.utilizador_id = $uid AND l.canal_chave = m.canal_chave
            WHERE m.id > COALESCE(l.ultima_id, 0) AND m.autor_id <> $uid AND $filtro";
    $res = $conn->query($sql);
    return $res ? (int) $res->fetch_assoc()['total'] : 0;
}

/** Link "Chat" para a barra de navegação, com contador de não lidas. $prefixo = caminho relativo até à raiz */
function linkChatNav($conn, $prefixo = '', $ativo = false) {
    if (!podeUsarChat()) return '';
    $n = chatNaoLidas($conn);
    $badge = $n > 0 ? ' <span class="chat-badge">' . ($n > 99 ? '99+' : $n) . '</span>' : '';
    return '<a href="' . $prefixo . 'chat.php" class="nav-link' . ($ativo ? ' active' : '') . '">Chat' . $badge . '</a>';
}

/** Token CSRF simples para os pedidos POST do chat */
function chatCsrfToken() {
    if (empty($_SESSION['chat_csrf'])) {
        $_SESSION['chat_csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['chat_csrf'];
}
