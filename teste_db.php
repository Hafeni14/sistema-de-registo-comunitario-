<?php
include "config/db.php"; // liga a base de dados

if ($conn) {
    echo "Ligacao a base de dados OK!";
} else {
    echo "Erro na ligacao!";
}
?>
