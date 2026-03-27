<?php
/**
 * Migration Runner — executa automaticamente ao arrancar a aplicação.
 * Regista na tabela `migrations` o que já foi executado e ignora essas.
 * Requer que $conn já esteja definido (incluído após db.php).
 */

// 1. Criar tabela de controlo de migrations (se não existir)
$conn->query("
    CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL UNIQUE,
        executado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
");

// 2. Obter migrations já executadas
$executadas = [];
$res = $conn->query("SELECT nome FROM migrations ORDER BY nome ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $executadas[] = $row['nome'];
    }
}

// 3. Ler ficheiros .sql da pasta migrations/ (ordenados por nome)
$pasta = __DIR__ . '/../migrations/';
$ficheiros = glob($pasta . '*.sql');
sort($ficheiros);

foreach ($ficheiros as $ficheiro) {
    $nome = basename($ficheiro);

    // Ignorar se já foi executada
    if (in_array($nome, $executadas)) {
        continue;
    }

    // Ler e executar o SQL
    $sql = file_get_contents($ficheiro);

    // Executar cada instrução separadamente (suporte a múltiplos statements)
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($s) => $s !== '' && !preg_match('/^--/', $s)
    );

    $sucesso = true;
    foreach ($statements as $statement) {
        if (!$conn->query($statement)) {
            // Ignorar erro de coluna já existente (comum em ADD COLUMN)
            if ($conn->errno !== 1060) {
                $sucesso = false;
                error_log("[Migration] ERRO em '$nome': " . $conn->error . " | SQL: $statement");
            }
        }
    }

    // Registar como executada (mesmo com erro tolerado)
    if ($sucesso) {
        $nome_escaped = $conn->real_escape_string($nome);
        $conn->query("INSERT IGNORE INTO migrations (nome) VALUES ('$nome_escaped')");
    }
}
