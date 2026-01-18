<?php
$host = "localhost";
$user = "root";
$pass = "root"; // padrão MAMP
$db   = "ocorrencias_comunitarias";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro de ligação: " . $conn->connect_error);
}
?>
