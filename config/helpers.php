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
 */
function isAdmin() {
    return !empty($_SESSION['is_admin']);
}
