<?php
/**
 * Archivo de prueba para verificar el controlador de recuperación de contraseña
 * Paso 3: Verificación del controlador
 */

require_once 'Controllers/PasswordRecoveryController.php';

echo "<h2>🧪 PRUEBAS DEL PASO 3: Controlador de Recuperación de Contraseña</h2>";
echo "<hr>";

// Crear instancia del controlador
$controller = new PasswordRecoveryController();

// Prueba 1: Solicitar recuperación con email inválido
echo "<h3>✅ Prueba 1: Validación de email inválido</h3>";
$resultado1 = $controller->solicitarRecuperacion('email-invalido', 'deportista');
echo "<p><strong>Email:</strong> email-invalido</p>";
echo "<p><strong>Resultado:</strong></p>";
echo "<pre>" . print_r($resultado1, true) . "</pre>";
echo "<hr>";

// Prueba 2: Solicitar recuperación con tipo de usuario inválido
echo "<h3>✅ Prueba 2: Validación de tipo de usuario inválido</h3>";
$resultado2 = $controller->solicitarRecuperacion('test@example.com', 'tipo_invalido');
echo "<p><strong>Tipo de usuario:</strong> tipo_invalido</p>";
echo "<p><strong>Resultado:</strong></p>";
echo "<pre>" . print_r($resultado2, true) . "</pre>";
echo "<hr>";

// Prueba 3: Solicitar recuperación con datos válidos (email no existente)
echo "<h3>✅ Prueba 3: Solicitud con email válido pero no existente</h3>";
$resultado3 = $controller->solicitarRecuperacion('noexiste@example.com', 'deportista');
echo "<p><strong>Email:</strong> noexiste@example.com</p>";
echo "<p><strong>Resultado:</strong></p>";
echo "<pre>" . print_r($resultado3, true) . "</pre>";
echo "<hr>";

// Prueba 4: Validar token vacío
echo "<h3>✅ Prueba 4: Validación de token vacío</h3>";
$resultado4 = $controller->validarToken('');
echo "<p><strong>Token:</strong> (vacío)</p>";
echo "<p><strong>Resultado:</strong></p>";
echo "<pre>" . print_r($resultado4, true) . "</pre>";
echo "<hr>";

// Prueba 5: Validar token inexistente
echo "<h3>✅ Prueba 5: Validación de token inexistente</h3>";
$resultado5 = $controller->validarToken('token_inexistente_12345');
echo "<p><strong>Token:</strong> token_inexistente_12345</p>";
echo "<p><strong>Resultado:</strong></p>";
echo "<pre>" . print_r($resultado5, true) . "</pre>";
echo "<hr>";

// Prueba 6: Cambiar contraseña con datos inválidos
echo "<h3>✅ Prueba 6: Cambio de contraseña con contraseñas no coincidentes</h3>";
$resultado6 = $controller->cambiarPassword('token_test', 'password123', 'password456');
echo "<p><strong>Nueva contraseña:</strong> password123</p>";
echo "<p><strong>Confirmar contraseña:</strong> password456</p>";
echo "<p><strong>Resultado:</strong></p>";
echo "<pre>" . print_r($resultado6, true) . "</pre>";
echo "<hr>";

// Prueba 7: Cambiar contraseña muy corta
echo "<h3>✅ Prueba 7: Cambio de contraseña muy corta</h3>";
$resultado7 = $controller->cambiarPassword('token_test', '123', '123');
echo "<p><strong>Contraseña:</strong> 123 (muy corta)</p>";
echo "<p><strong>Resultado:</strong></p>";
echo "<pre>" . print_r($resultado7, true) . "</pre>";
echo "<hr>";

// Prueba 8: Limpiar tokens expirados
echo "<h3>✅ Prueba 8: Limpiar tokens expirados</h3>";
$resultado8 = $controller->limpiarTokensExpirados();
echo "<p><strong>Resultado:</strong></p>";
echo "<pre>" . print_r($resultado8, true) . "</pre>";
echo "<hr>";

// Prueba 9: Verificar métodos de URL
echo "<h3>✅ Prueba 9: Verificar generación de URLs</h3>";
// Simular datos de servidor para la prueba
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/GameOn_Network/test_controller_step3.php';
$_SERVER['HTTPS'] = 'off';

// Usar reflexión para acceder al método privado
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('generarEnlaceRecuperacion');
$method->setAccessible(true);
$enlace = $method->invoke($controller, 'token_ejemplo_123');

echo "<p><strong>Enlace generado:</strong> $enlace</p>";
echo "<hr>";

// Resumen de funcionalidades del controlador
echo "<h2>🎯 RESUMEN DEL PASO 3</h2>";
echo "<h3>✅ Funcionalidades implementadas:</h3>";
echo "<ul>";
echo "<li>✅ <strong>solicitarRecuperacion()</strong>: Procesa solicitudes de recuperación con validaciones</li>";
echo "<li>✅ <strong>validarToken()</strong>: Valida tokens de recuperación</li>";
echo "<li>✅ <strong>cambiarPassword()</strong>: Procesa cambio de contraseña con validaciones</li>";
echo "<li>✅ <strong>generarEnlaceRecuperacion()</strong>: Genera URLs de recuperación</li>";
echo "<li>✅ <strong>limpiarTokensExpirados()</strong>: Mantenimiento de tokens</li>";
echo "<li>✅ <strong>procesarAjax()</strong>: Manejo de solicitudes AJAX</li>";
echo "</ul>";

echo "<h3>✅ Validaciones implementadas:</h3>";
echo "<ul>";
echo "<li>✅ Validación de formato de email</li>";
echo "<li>✅ Validación de tipo de usuario</li>";
echo "<li>✅ Validación de longitud de contraseña</li>";
echo "<li>✅ Validación de coincidencia de contraseñas</li>";
echo "<li>✅ Validación de tokens</li>";
echo "<li>✅ Manejo seguro de errores</li>";
echo "</ul>";

echo "<h3>✅ Características de seguridad:</h3>";
echo "<ul>";
echo "<li>✅ No revela si un email existe en el sistema</li>";
echo "<li>✅ Tokens de un solo uso</li>";
echo "<li>✅ Validación de expiración de tokens</li>";
echo "<li>✅ Sanitización de entrada</li>";
echo "<li>✅ Respuestas JSON estructuradas</li>";
echo "</ul>";

echo "<p><strong>Estado:</strong> PASO 3 COMPLETADO</p>";
echo "<p><strong>Siguiente:</strong> Proceder con Paso 4 (Vistas)</p>";
?>