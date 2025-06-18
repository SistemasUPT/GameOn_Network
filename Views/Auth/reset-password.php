<?php
// Incluir el controlador de recuperación de contraseña
require_once '../../Controllers/PasswordRecoveryController.php';

// Variables para almacenar mensajes y datos
$message = '';
$message_type = '';
$token = '';
$token_valid = false;
$user_data = null;

// Obtener el token de la URL
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    
    // Validar el token
    $controller = new PasswordRecoveryController();
    $validacion = $controller->validarToken($token);
    
    if ($validacion['success']) {
        $token_valid = true;
        $user_data = $validacion['data'];
    } else {
        $message = $validacion['message'];
        $message_type = 'error';
    }
} else {
    $message = "Token de recuperación no válido o faltante.";
    $message_type = 'error';
}

// Procesar el formulario de cambio de contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_valid) {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    $token_form = trim($_POST['token']);
    
    if (empty($new_password) || empty($confirm_password)) {
        $message = "Por favor, completa todos los campos.";
        $message_type = 'error';
    } else {
        $resultado = $controller->cambiarPassword($token_form, $new_password, $confirm_password);
        
        if ($resultado['success']) {
            $message = "Tu contraseña ha sido actualizada exitosamente. Ya puedes iniciar sesión con tu nueva contraseña.";
            $message_type = 'success';
            $token_valid = false; // Ocultar el formulario después del éxito
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
    <title>Restablecer Contraseña - GameOn Network</title>
    <link rel="stylesheet" href="../../Public/css/styles_login.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        
        .user-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .user-info i {
            color: #6c757d;
            margin-right: 8px;
        }
        
        .password-requirements {
            background-color: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #004085;
        }
        
        .password-requirements ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
        }
        
        .password-requirements li {
            margin-bottom: 5px;
        }
        
        .password-strength {
            margin-top: 5px;
            height: 4px;
            background-color: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }
        
        .strength-weak { background-color: #dc3545; }
        .strength-medium { background-color: #ffc107; }
        .strength-strong { background-color: #28a745; }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-image"></div>
        <div class="auth-container">
            <div class="auth-header">
                <img src="../../Resources/logo_ipd_3.png" alt="Logo GameOn Network" style="width: 400px; height: auto;">
                <h2>Restablecer Contraseña</h2>
                <p>Crea una nueva contraseña segura para tu cuenta</p>
            </div>
            
            <div class="auth-body">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($token_valid && $user_data): ?>
                    <div class="user-info">
                        <i class="fas fa-user"></i>
                        <strong>Restableciendo contraseña para:</strong> <?php echo htmlspecialchars($user_data['email']); ?>
                        <br>
                        <i class="fas fa-tag"></i>
                        <strong>Tipo de cuenta:</strong> <?php echo ucfirst($user_data['user_type']); ?>
                    </div>
                    
                    <div class="password-requirements">
                        <i class="fas fa-shield-alt"></i>
                        <strong>Requisitos de la contraseña:</strong>
                        <ul>
                            <li>Mínimo 6 caracteres</li>
                            <li>Se recomienda usar una combinación de letras, números y símbolos</li>
                            <li>Evita usar información personal fácil de adivinar</li>
                        </ul>
                    </div>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?token=<?php echo urlencode($token); ?>" method="POST" id="resetPasswordForm">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        
                        <div class="form-group">
                            <label for="new_password">
                                <i class="fas fa-lock"></i>
                                Nueva contraseña
                            </label>
                            <div class="password-field">
                                <input type="password" id="new_password" name="new_password" class="form-control" 
                                       placeholder="Ingresa tu nueva contraseña" required minlength="6">
                                <span class="toggle-password" onclick="togglePasswordVisibility('new_password')">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <div class="password-strength">
                                <div class="password-strength-bar" id="strengthBar"></div>
                            </div>
                            <small id="strengthText" style="color: #6c757d; font-size: 12px;"></small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">
                                <i class="fas fa-lock"></i>
                                Confirmar nueva contraseña
                            </label>
                            <div class="password-field">
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                       placeholder="Confirma tu nueva contraseña" required minlength="6">
                                <span class="toggle-password" onclick="togglePasswordVisibility('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <small id="matchText" style="font-size: 12px;"></small>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block" id="submitBtn" disabled>
                                <i class="fas fa-key"></i>
                                Restablecer contraseña
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
                
                <div class="back-to-login">
                    <a href="login.php">
                        <i class="fas fa-arrow-left"></i>
                        Volver al inicio de sesión
                    </a>
                </div>
            </div>
            
            <div class="auth-footer">
                ¿Necesitas ayuda? 
                <a href="forgot-password.php">
                    <i class="fas fa-question-circle"></i>
                    Solicitar nuevo enlace
                </a>
            </div>
        </div>
    </div>
    
    <script>
        function togglePasswordVisibility(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = passwordField.nextElementSibling.querySelector('i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        function checkPasswordStrength(password) {
            let strength = 0;
            let text = '';
            let className = '';
            
            if (password.length >= 6) strength += 1;
            if (password.length >= 8) strength += 1;
            if (/[a-z]/.test(password)) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            if (strength <= 2) {
                text = 'Débil';
                className = 'strength-weak';
            } else if (strength <= 4) {
                text = 'Media';
                className = 'strength-medium';
            } else {
                text = 'Fuerte';
                className = 'strength-strong';
            }
            
            return { strength: Math.min(strength, 3), text, className };
        }
        
        function validatePasswords() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const submitBtn = document.getElementById('submitBtn');
            const matchText = document.getElementById('matchText');
            
            let isValid = true;
            
            // Validar longitud mínima
            if (newPassword.length < 6) {
                isValid = false;
            }
            
            // Validar coincidencia
            if (confirmPassword.length > 0) {
                if (newPassword === confirmPassword) {
                    matchText.textContent = '✓ Las contraseñas coinciden';
                    matchText.style.color = '#28a745';
                } else {
                    matchText.textContent = '✗ Las contraseñas no coinciden';
                    matchText.style.color = '#dc3545';
                    isValid = false;
                }
            } else {
                matchText.textContent = '';
                if (newPassword.length > 0) isValid = false;
            }
            
            submitBtn.disabled = !isValid;
        }
        
        // Event listeners
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthResult = checkPasswordStrength(password);
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            strengthBar.style.width = (strengthResult.strength / 3 * 100) + '%';
            strengthBar.className = 'password-strength-bar ' + strengthResult.className;
            strengthText.textContent = password.length > 0 ? 'Fortaleza: ' + strengthResult.text : '';
            
            validatePasswords();
        });
        
        document.getElementById('confirm_password').addEventListener('input', validatePasswords);
        
        // Agregar animación al formulario
        document.getElementById('resetPasswordForm')?.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Restableciendo...';
            submitBtn.disabled = true;
        });
    </script>
    
    <script src="../../Public/js/main.js"></script>
</body>
</html>