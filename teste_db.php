<?php
include "config/db.php"; // liga à base de dados

if ($conn) {
    echo "Ligação à base de dados OK!";
} else {
    echo "Erro na ligação!";
}
?>
