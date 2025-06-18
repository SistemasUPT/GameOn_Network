<?php
// Incluir el controlador de recuperación de contraseña
require_once '../../Controllers/PasswordRecoveryController.php';

// Variables para almacenar mensajes y datos del formulario
$message = '';
$message_type = '';
$email = '';

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $user_type = $_POST['user_type'];

    if (empty($email)) {
        $message = "Por favor, ingresa tu correo electrónico.";
        $message_type = 'error';
    } else {
        $controller = new PasswordRecoveryController();
        $resultado = $controller->solicitarRecuperacion($email, $user_type);
        
        if ($resultado['success']) {
            $message = "Si el correo electrónico está registrado en nuestro sistema, recibirás un enlace de recuperación en unos minutos.";
            $message_type = 'success';
            $email = ''; // Limpiar el campo por seguridad
        } else {
            $message = $resultado['message'];
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - GameOn Network</title>
    <link rel="stylesheet" href="../../Public/css/styles_login.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- EmailJS SDK -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
    <!-- Configuración EmailJS -->
    <script src="../../Config/emailjs_config.js"></script>
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-to-login a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s ease;
        }
        
        .back-to-login a:hover {
            color: #0056b3;
            text-decoration: underline;
        }
        
        .info-box {
            background-color: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #004085;
        }
        
        .info-box i {
            color: #007bff;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-image"></div>
        <div class="auth-container">
            <div class="auth-header">
                <img src="../../Resources/logo_ipd_3.png" alt="Logo GameOn Network" style="width: 400px; height: auto;">
                <h2>Recuperar Contraseña</h2>
                <p>Ingresa tu correo electrónico para recibir un enlace de recuperación</p>
            </div>
            
            <div class="auth-body">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <strong>Instrucciones:</strong> Ingresa el correo electrónico asociado a tu cuenta y selecciona tu tipo de usuario. Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.
                </div>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="forgotPasswordForm">
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            Correo electrónico
                        </label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($email); ?>" 
                               placeholder="Ingresa tu correo electrónico" required>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <i class="fas fa-users"></i>
                            Tipo de usuario
                        </label>
                        <div class="user-type-selector">
                            <div class="form-check">
                                <input type="radio" id="user_type_dep" name="user_type" value="deportista" checked>
                                <label for="user_type_dep">
                                    <i class="fas fa-running"></i>
                                    Deportista
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="radio" id="user_type_ins" name="user_type" value="instalacion">
                                <label for="user_type_ins">
                                    <i class="fas fa-building"></i>
                                    Instalación Deportiva
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane"></i>
                            Enviar enlace de recuperación
                        </button>
                    </div>
                </form>
                
                <div class="back-to-login">
                    <a href="login.php">
                        <i class="fas fa-arrow-left"></i>
                        Volver al inicio de sesión
                    </a>
                </div>
            </div>
            
            <div class="auth-footer">
                ¿No tienes cuenta? 
                <a href="../UserDep/registrousuario.php">
                    <i class="fas fa-user-plus"></i>
                    Regístrate como deportista
                </a> o 
                <a href="../UserInsD/registroinsdepor.php">
                    <i class="fas fa-building"></i>
                    registra tu instalación deportiva
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Nota: La configuración de EmailJS se carga desde emailjs_config.js

        // Función para mostrar mensajes
        function mostrarMensaje(mensaje, tipo) {
            // Remover mensajes anteriores
            const alertaAnterior = document.querySelector('.alert');
            if (alertaAnterior) {
                alertaAnterior.remove();
            }

            // Crear nueva alerta
            const alerta = document.createElement('div');
            alerta.className = `alert alert-${tipo}`;
            alerta.innerHTML = `
                <i class="fas ${tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                ${mensaje}
            `;

            // Insertar antes del formulario
            const authBody = document.querySelector('.auth-body');
            const infoBox = document.querySelector('.info-box');
            authBody.insertBefore(alerta, infoBox.nextSibling);

            // Auto-ocultar mensajes de éxito
            if (tipo === 'success') {
                setTimeout(() => {
                    alerta.style.opacity = '0';
                    setTimeout(() => {
                        if (alerta.parentNode) {
                            alerta.remove();
                        }
                    }, 300);
                }, 8000);
            }
        }

        // Manejar envío del formulario
        document.getElementById('forgotPasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault(); // Prevenir envío tradicional

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Mostrar estado de carga
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            submitBtn.disabled = true;

            try {
                // Obtener datos del formulario
                const formData = new FormData(this);
                formData.append('action', 'preparar_emailjs');

                // Enviar solicitud al controlador
                const response = await fetch('../../Controllers/PasswordRecoveryController.php', {
                    method: 'POST',
                    body: formData
                });

                const resultado = await response.json();

                if (resultado.success) {
                    if (resultado.send_email && resultado.email_data) {
                        // Enviar email con EmailJS
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando email...';
                        
                        const emailResult = await enviarEmailRecuperacion(resultado.email_data);
                        
                        if (emailResult.success) {
                            mostrarMensaje(
                                'Email de recuperación enviado exitosamente. Revisa tu bandeja de entrada y spam.',
                                'success'
                            );
                            this.reset(); // Limpiar formulario
                        } else {
                            mostrarMensaje(
                                'Error al enviar el email. Por favor, intenta nuevamente o contacta al soporte.',
                                'error'
                            );
                        }
                    } else {
                        // Usuario no encontrado, pero mostramos mensaje genérico por seguridad
                        mostrarMensaje(resultado.message, 'success');
                        this.reset();
                    }
                } else {
                    mostrarMensaje(resultado.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarMensaje(
                    'Error de conexión. Por favor, verifica tu conexión a internet e intenta nuevamente.',
                    'error'
                );
            } finally {
                // Restaurar botón
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });

        // Mejorar la interfaz de los radio buttons
        document.querySelectorAll('input[name="user_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.form-check').forEach(check => {
                    check.classList.remove('selected');
                });
                this.closest('.form-check').classList.add('selected');
            });
        });

        // Marcar el primer radio como seleccionado visualmente
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.form-check').classList.add('selected');
        });
    </script>
    
    <script src="../../Public/js/main.js"></script>
</body>
</html>