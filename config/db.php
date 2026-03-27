<?php
// Configuracao de Base de Dados
// Suporta Docker (variaveis de ambiente) e localhost

$host = getenv('DB_HOST') ?: "localhost";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') ?: "root";
$db   = getenv('DB_NAME') ?: "ocorrencias_comunitarias";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro de ligacao: " . $conn->connect_error);
}

// Definir charset UTF-8 para evitar problemas de encoding
$conn->set_charset("utf8mb4");
mysqli_query($conn, "SET NAMES 'utf8mb4'");
mysqli_query($conn, "SET CHARACTER SET utf8mb4");

// Executar migrations pendentes automaticamente
require_once __DIR__ . '/migrate.php';
?>
