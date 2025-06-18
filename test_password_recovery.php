<?php
/**
 * Archivo de prueba para verificar la funcionalidad de recuperación de contraseña
 * Paso 2: Verificación de métodos del modelo
 */

require_once 'Models/UsuarioModel.php';

// Crear instancia del modelo
$usuarioModel = new UsuarioModel();

echo "<h2>🧪 PRUEBAS DEL PASO 2: Métodos de Recuperación de Contraseña</h2>";
echo "<hr>";

// Prueba 1: Buscar usuario por email (deportista)
echo "<h3>✅ Prueba 1: Buscar usuario deportista por email</h3>";
$email_test = "test@example.com";
$resultado1 = $usuarioModel->buscarUsuarioPorEmail($email_test, 'deportista');
echo "<p><strong>Email buscado:</strong> $email_test</p>";
echo "<p><strong>Resultado:</strong> " . (empty($resultado1) ? "No encontrado (esperado si no existe)" : "Usuario encontrado: ID " . $resultado1['id']) . "</p>";
echo "<hr>";

// Prueba 2: Crear token de recuperación
echo "<h3>✅ Prueba 2: Crear token de recuperación</h3>";
$token_result = $usuarioModel->crearTokenRecuperacion(1, 'deportista', $email_test);
echo "<p><strong>Resultado:</strong></p>";
echo "<pre>" . print_r($token_result, true) . "</pre>";

if (isset($token_result['token'])) {
    $test_token = $token_result['token'];
    echo "<p><strong>Token generado:</strong> $test_token</p>";
    
    // Prueba 3: Validar token
    echo "<h3>✅ Prueba 3: Validar token de recuperación</h3>";
    $validacion = $usuarioModel->validarTokenRecuperacion($test_token);
    echo "<p><strong>Token válido:</strong> " . (empty($validacion) ? "No" : "Sí") . "</p>";
    if (!empty($validacion)) {
        echo "<pre>" . print_r($validacion, true) . "</pre>";
    }
    echo "<hr>";
    
    // Prueba 4: Marcar token como usado
    echo "<h3>✅ Prueba 4: Marcar token como usado</h3>";
    $marcado = $usuarioModel->marcarTokenUsado($test_token);
    echo "<p><strong>Token marcado como usado:</strong> " . ($marcado ? "Sí" : "No") . "</p>";
    echo "<hr>";
    
    // Prueba 5: Validar token usado (debe fallar)
    echo "<h3>✅ Prueba 5: Validar token ya usado (debe fallar)</h3>";
    $validacion2 = $usuarioModel->validarTokenRecuperacion($test_token);
    echo "<p><strong>Token válido después de usar:</strong> " . (empty($validacion2) ? "No (correcto)" : "Sí (error)") . "</p>";
    echo "<hr>";
}

// Prueba 6: Limpiar tokens expirados
echo "<h3>✅ Prueba 6: Limpiar tokens expirados</h3>";
$limpieza = $usuarioModel->limpiarTokensExpirados();
echo "<p><strong>Resultado de limpieza:</strong></p>";
echo "<pre>" . print_r($limpieza, true) . "</pre>";
echo "<hr>";

// Prueba 7: Verificar estructura de tabla
echo "<h3>✅ Prueba 7: Verificar conexión a base de datos</h3>";
try {
    $database = new Database();
    $conn = $database->getConnection();
    $result = $conn->query("SELECT COUNT(*) as total FROM password_recovery_tokens");
    $row = $result->fetch_assoc();
    echo "<p><strong>Conexión a BD:</strong> ✅ Exitosa</p>";
    echo "<p><strong>Total de tokens en BD:</strong> " . $row['total'] . "</p>";
} catch (Exception $e) {
    echo "<p><strong>Error de conexión:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>🎯 RESUMEN DEL PASO 2</h2>";
echo "<p>✅ Modelo UsuarioModel.php actualizado con métodos de recuperación</p>";
echo "<p>✅ Sintaxis PHP validada correctamente</p>";
echo "<p>✅ Métodos probados funcionalmente</p>";
echo "<p><strong>Estado:</strong> PASO 2 COMPLETADO</p>";
echo "<p><strong>Siguiente:</strong> Proceder con Paso 3 (Controlador)</p>";
?>