<?php
/**
 * API do chat privado (JSON).
 *   GET  ?acao=listar&canal=nacional&depois_id=0
 *   POST acao=enviar, canal, texto, csrf
 */
include("config/db.php");
include("config/helpers.php");
session_start();

header('Content-Type: application/json; charset=utf-8');

function responder($dados, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($_SESSION['utilizador_id']) || !podeUsarChat()) {
    responder(['erro' => 'Sem permissão'], 403);
}

$uid = (int) $_SESSION['utilizador_id'];
$acao = $_REQUEST['acao'] ?? '';
$canal = $_REQUEST['canal'] ?? '';

if (!podeAcederCanal($canal)) {
    responder(['erro' => 'Sem acesso a este canal'], 403);
}

// Um canal de município tem de existir
if ($canal !== 'nacional') {
    $mid = (int) substr($canal, 1);
    $r = $conn->query("SELECT id FROM municipios WHERE id = $mid");
    if (!$r || $r->num_rows === 0) {
        responder(['erro' => 'Canal inexistente'], 404);
    }
}

if ($acao === 'enviar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['chat_csrf'] ?? '', $_POST['csrf'] ?? '')) {
        responder(['erro' => 'Pedido inválido'], 400);
    }
    $texto = trim($_POST['texto'] ?? '');
    if ($texto === '' || mb_strlen($texto) > 2000) {
        responder(['erro' => 'Mensagem vazia ou com mais de 2000 caracteres'], 400);
    }
    $stmt = $conn->prepare("INSERT INTO chat_mensagens (canal_chave, autor_id, texto) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $canal, $uid, $texto);
    if (!$stmt->execute()) {
        responder(['erro' => 'Não foi possível enviar'], 500);
    }
    responder(['ok' => true, 'id' => $stmt->insert_id]);
}

if ($acao === 'listar') {
    $depois_id = (int) ($_GET['depois_id'] ?? 0);
    if ($depois_id > 0) {
        $stmt = $conn->prepare("SELECT m.id, m.texto, m.criado_em, m.autor_id, u.nome AS autor, u.nivel_acesso
                                FROM chat_mensagens m JOIN utilizadores u ON u.id = m.autor_id
                                WHERE m.canal_chave = ? AND m.id > ? ORDER BY m.id ASC LIMIT 200");
        $stmt->bind_param("si", $canal, $depois_id);
    } else {
        // Primeira carga: últimas 100 mensagens, em ordem cronológica
        $stmt = $conn->prepare("SELECT * FROM (
                                    SELECT m.id, m.texto, m.criado_em, m.autor_id, u.nome AS autor, u.nivel_acesso
                                    FROM chat_mensagens m JOIN utilizadores u ON u.id = m.autor_id
                                    WHERE m.canal_chave = ? ORDER BY m.id DESC LIMIT 100
                                ) t ORDER BY id ASC");
        $stmt->bind_param("s", $canal);
    }
    $stmt->execute();
    $mensagens = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if ($mensagens) {
        $ultima = (int) end($mensagens)['id'];
        $stmt2 = $conn->prepare("INSERT INTO chat_leituras (utilizador_id, canal_chave, ultima_id) VALUES (?, ?, ?)
                                 ON DUPLICATE KEY UPDATE ultima_id = GREATEST(ultima_id, VALUES(ultima_id))");
        $stmt2->bind_param("isi", $uid, $canal, $ultima);
        $stmt2->execute();
    }
    foreach ($mensagens as &$m) {
        $m['id'] = (int) $m['id'];
        $m['minha'] = ((int) $m['autor_id'] === $uid);
        unset($m['autor_id']);
    }
    responder(['mensagens' => $mensagens, 'nao_lidas' => chatNaoLidas($conn)]);
}

responder(['erro' => 'Ação inválida'], 400);
