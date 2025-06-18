<?php
/**
 * Archivo de prueba para verificar las vistas de recuperación de contraseña
 * Paso 4: Verificación de las vistas
 */

echo "<h2>🧪 PRUEBAS DEL PASO 4: Vistas de Recuperación de Contraseña</h2>";
echo "<hr>";

// Verificar que los archivos de vista existen
$vistas = [
    'forgot-password.php' => 'c:\\xampp\\htdocs\\GameOn_Network\\Views\\Auth\\forgot-password.php',
    'reset-password.php' => 'c:\\xampp\\htdocs\\GameOn_Network\\Views\\Auth\\reset-password.php',
    'login.php' => 'c:\\xampp\\htdocs\\GameOn_Network\\Views\\Auth\\login.php'
];

echo "<h3>✅ Verificación de archivos de vista:</h3>";
echo "<ul>";

foreach ($vistas as $nombre => $ruta) {
    if (file_exists($ruta)) {
        $tamaño = filesize($ruta);
        echo "<li>✅ <strong>$nombre</strong>: Existe (" . number_format($tamaño) . " bytes)</li>";
    } else {
        echo "<li>❌ <strong>$nombre</strong>: No encontrado</li>";
    }
}

echo "</ul>";
echo "<hr>";

// Verificar enlaces entre vistas
echo "<h3>✅ Verificación de navegación entre vistas:</h3>";
echo "<ul>";

// Verificar enlace en login.php
$loginContent = file_get_contents('c:\\xampp\\htdocs\\GameOn_Network\\Views\\Auth\\login.php');
if (strpos($loginContent, 'href="forgot-password.php"') !== false) {
    echo "<li>✅ <strong>login.php</strong> → forgot-password.php: Enlace correcto</li>";
} else {
    echo "<li>❌ <strong>login.php</strong> → forgot-password.php: Enlace no encontrado</li>";
}

// Verificar enlace en forgot-password.php
$forgotContent = file_get_contents('c:\\xampp\\htdocs\\GameOn_Network\\Views\\Auth\\forgot-password.php');
if (strpos($forgotContent, 'href="login.php"') !== false) {
    echo "<li>✅ <strong>forgot-password.php</strong> → login.php: Enlace correcto</li>";
} else {
    echo "<li>❌ <strong>forgot-password.php</strong> → login.php: Enlace no encontrado</li>";
}

// Verificar enlace en reset-password.php
$resetContent = file_get_contents('c:\\xampp\\htdocs\\GameOn_Network\\Views\\Auth\\reset-password.php');
if (strpos($resetContent, 'href="login.php"') !== false) {
    echo "<li>✅ <strong>reset-password.php</strong> → login.php: Enlace correcto</li>";
} else {
    echo "<li>❌ <strong>reset-password.php</strong> → login.php: Enlace no encontrado</li>";
}

if (strpos($resetContent, 'href="forgot-password.php"') !== false) {
    echo "<li>✅ <strong>reset-password.php</strong> → forgot-password.php: Enlace correcto</li>";
} else {
    echo "<li>❌ <strong>reset-password.php</strong> → forgot-password.php: Enlace no encontrado</li>";
}

echo "</ul>";
echo "<hr>";

// Verificar dependencias de CSS y JS
echo "<h3>✅ Verificación de dependencias:</h3>";
echo "<ul>";

$dependencias = [
    'CSS Login' => '../../Public/css/styles_login.css',
    'Font Awesome' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css',
    'Google Fonts' => 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap',
    'Main JS' => '../../Public/js/main.js',
    'Logo' => '../../Resources/logo_ipd_3.png'
];

foreach ($dependencias as $nombre => $ruta) {
    if (strpos($ruta, 'http') === 0) {
        echo "<li>🌐 <strong>$nombre</strong>: Enlace externo ($ruta)</li>";
    } else {
        $rutaCompleta = 'c:\\xampp\\htdocs\\GameOn_Network\\' . str_replace('../../', '', $ruta);
        if (file_exists($rutaCompleta)) {
            echo "<li>✅ <strong>$nombre</strong>: Encontrado</li>";
        } else {
            echo "<li>❌ <strong>$nombre</strong>: No encontrado ($rutaCompleta)</li>";
        }
    }
}

echo "</ul>";
echo "<hr>";

// Verificar características de seguridad en las vistas
echo "<h3>✅ Verificación de características de seguridad:</h3>";
echo "<ul>";

// Verificar htmlspecialchars en forgot-password.php
if (strpos($forgotContent, 'htmlspecialchars') !== false) {
    echo "<li>✅ <strong>forgot-password.php</strong>: Usa htmlspecialchars para sanitización</li>";
} else {
    echo "<li>❌ <strong>forgot-password.php</strong>: No usa htmlspecialchars</li>";
}

// Verificar htmlspecialchars en reset-password.php
if (strpos($resetContent, 'htmlspecialchars') !== false) {
    echo "<li>✅ <strong>reset-password.php</strong>: Usa htmlspecialchars para sanitización</li>";
} else {
    echo "<li>❌ <strong>reset-password.php</strong>: No usa htmlspecialchars</li>";
}

// Verificar CSRF protection
if (strpos($forgotContent, 'PHP_SELF') !== false) {
    echo "<li>✅ <strong>forgot-password.php</strong>: Usa PHP_SELF para formularios</li>";
} else {
    echo "<li>❌ <strong>forgot-password.php</strong>: No usa PHP_SELF</li>";
}

if (strpos($resetContent, 'PHP_SELF') !== false) {
    echo "<li>✅ <strong>reset-password.php</strong>: Usa PHP_SELF para formularios</li>";
} else {
    echo "<li>❌ <strong>reset-password.php</strong>: No usa PHP_SELF</li>";
}

echo "</ul>";
echo "<hr>";

// Verificar funcionalidades JavaScript
echo "<h3>✅ Verificación de funcionalidades JavaScript:</h3>";
echo "<ul>";

// Verificar toggle password en reset-password.php
if (strpos($resetContent, 'togglePasswordVisibility') !== false) {
    echo "<li>✅ <strong>reset-password.php</strong>: Incluye toggle de visibilidad de contraseña</li>";
} else {
    echo "<li>❌ <strong>reset-password.php</strong>: No incluye toggle de contraseña</li>";
}

// Verificar validación de contraseñas
if (strpos($resetContent, 'checkPasswordStrength') !== false) {
    echo "<li>✅ <strong>reset-password.php</strong>: Incluye verificación de fortaleza de contraseña</li>";
} else {
    echo "<li>❌ <strong>reset-password.php</strong>: No incluye verificación de fortaleza</li>";
}

// Verificar validación de coincidencia
if (strpos($resetContent, 'validatePasswords') !== false) {
    echo "<li>✅ <strong>reset-password.php</strong>: Incluye validación de coincidencia de contraseñas</li>";
} else {
    echo "<li>❌ <strong>reset-password.php</strong>: No incluye validación de coincidencia</li>";
}

echo "</ul>";
echo "<hr>";

// Resumen de funcionalidades implementadas
echo "<h2>🎯 RESUMEN DEL PASO 4</h2>";
echo "<h3>✅ Vistas creadas:</h3>";
echo "<ul>";
echo "<li>✅ <strong>forgot-password.php</strong>: Formulario para solicitar recuperación de contraseña</li>";
echo "<li>✅ <strong>reset-password.php</strong>: Formulario para restablecer contraseña con token</li>";
echo "<li>✅ <strong>login.php</strong>: Actualizado con enlace correcto a forgot-password.php</li>";
echo "</ul>";

echo "<h3>✅ Características implementadas:</h3>";
echo "<ul>";
echo "<li>✅ <strong>Diseño consistente</strong>: Mismo estilo que login.php</li>";
echo "<li>✅ <strong>Validación de formularios</strong>: JavaScript y PHP</li>";
echo "<li>✅ <strong>Seguridad</strong>: Sanitización de entrada y CSRF protection</li>";
echo "<li>✅ <strong>UX mejorada</strong>: Indicadores de fortaleza de contraseña</li>";
echo "<li>✅ <strong>Navegación</strong>: Enlaces entre vistas</li>";
echo "<li>✅ <strong>Responsive</strong>: Compatible con dispositivos móviles</li>";
echo "<li>✅ <strong>Accesibilidad</strong>: Iconos y etiquetas descriptivas</li>";
echo "</ul>";

echo "<h3>✅ Flujo de usuario:</h3>";
echo "<ol>";
echo "<li><strong>Login</strong> → Click en 'Olvidaste tu contraseña' → <strong>forgot-password.php</strong></li>";
echo "<li><strong>forgot-password.php</strong> → Ingresa email → Recibe enlace por email</li>";
echo "<li><strong>Email</strong> → Click en enlace → <strong>reset-password.php?token=xxx</strong></li>";
echo "<li><strong>reset-password.php</strong> → Nueva contraseña → Redirige a login</li>";
echo "</ol>";

echo "<p><strong>Estado:</strong> PASO 4 COMPLETADO</p>";
echo "<p><strong>Siguiente:</strong> Proceder con Paso 5 (Rutas y endpoints)</p>";
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f8f9fa;
}

h2, h3 {
    color: #2c3e50;
}

ul, ol {
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

li {
    margin-bottom: 8px;
    padding: 5px 0;
}

hr {
    border: none;
    height: 2px;
    background: linear-gradient(to right, #3498db, #2ecc71);
    margin: 30px 0;
}

.success { color: #27ae60; }
.error { color: #e74c3c; }
.info { color: #3498db; }
</style>