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
