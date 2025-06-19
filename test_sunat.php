<?php
/**
 * Script de pruebas para la integración con SUNAT
 * Ejecutar desde línea de comandos: php test_sunat.php
 */

require_once 'SunatAPI.php';

echo "=== PRUEBAS DE INTEGRACIÓN SUNAT ===\n\n";

// RUCs de prueba (puedes cambiar estos valores)
$rucs_prueba = [
    '20100070970', // RUC válido de ejemplo
    '20123456789', // RUC que puede no existir
    '12345678901', // RUC con formato válido pero probablemente inexistente
    '1234567890'   // RUC con formato inválido
];

$sunatAPI = new SunatAPI();

foreach ($rucs_prueba as $ruc) {
    echo "Probando RUC: {$ruc}\n";
    echo str_repeat("-", 50) . "\n";
    
    $inicio = microtime(true);
    $resultado = $sunatAPI->validarRUC($ruc);
    $tiempo = round((microtime(true) - $inicio) * 1000, 2);
    
    echo "Tiempo de respuesta: {$tiempo}ms\n";
    echo "Resultado: " . ($resultado['success'] ? 'ÉXITO' : 'ERROR') . "\n";
    echo "Mensaje: {$resultado['message']}\n";
    
    if ($resultado['success'] && $resultado['data']) {
        echo "Datos obtenidos:\n";
        foreach ($resultado['data'] as $key => $value) {
            echo "  - {$key}: {$value}\n";
        }
    }
    
    echo "\n";
}

// Prueba de fallback
echo "=== PRUEBA DE FALLBACK ===\n";
$ruc_test = '20100070970';
echo "Probando RUC con fallback: {$ruc_test}\n";

$inicio = microtime(true);
$resultado = $sunatAPI->validarRUCConFallback($ruc_test);
$tiempo = round((microtime(true) - $inicio) * 1000, 2);

echo "Tiempo total: {$tiempo}ms\n";
echo "Resultado final: " . ($resultado['success'] ? 'ÉXITO' : 'ERROR') . "\n";
echo "Mensaje: {$resultado['message']}\n";

// Prueba de validación de formato
echo "\n=== PRUEBA DE VALIDACIÓN DE FORMATO ===\n";
$formatos_prueba = [
    '20100070970' => 'Válido',
    '2010007097' => 'Muy corto',
    '201000709701' => 'Muy largo',
    '2010007097a' => 'Con letra',
    '20100070971' => 'Dígito verificador incorrecto'
];

foreach ($formatos_prueba as $ruc => $descripcion) {
    $reflector = new ReflectionClass('SunatAPI');
    $method = $reflector->getMethod('validarFormatoRUC');
    $method->setAccessible(true);
    
    $esValido = $method->invoke($sunatAPI, $ruc);
    echo "RUC: {$ruc} ({$descripcion}) - " . ($esValido ? 'FORMATO VÁLIDO' : 'FORMATO INVÁLIDO') . "\n";
}

echo "\n=== PRUEBAS COMPLETADAS ===\n";

// Función para probar conexión a base de datos
function probarConexionBD() {
    echo "\n=== PRUEBA DE CONEXIÓN BD ===\n";
    try {
        $pdo = getDBConnection();
        echo "✓ Conexión a base de datos exitosa\n";
        
        // Verificar si las tablas existen
        $tablas = ['instituciones_deportivas', 'sunat_validaciones_log'];
        
        foreach ($tablas as $tabla) {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$tabla}'");
            if ($stmt->rowCount() > 0) {
                echo "✓ Tabla '{$tabla}' existe\n";
            } else {
                echo "✗ Tabla '{$tabla}' NO existe\n";
            }
        }
        
    } catch (Exception $e) {
        echo "✗ Error de conexión BD: " . $e->getMessage() . "\n";
    }
}

// Ejecutar prueba de BD solo si está disponible
if (function_exists('getDBConnection')) {
    probarConexionBD();
}

echo "\nPruebas finalizadas. Revisa los logs para más detalles.\n";
?>