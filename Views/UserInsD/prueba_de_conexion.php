<?php
// test_db.php
// Prueba de conexión a la base de datos
require_once 'config_sunat.php';
try {
    $pdo = getDBConnection();
    echo "¡Conexión exitosa!";
} catch (Exception $e) {
    echo $e->getMessage();
}
?>