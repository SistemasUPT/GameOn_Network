<?php
// Incluir solo el controlador de autenticación
require_once '../../Controllers/AuthController.php';

// Variables para almacenar mensajes y datos del formulario
$error_message = '';
$username = '';

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $user_type = $_POST['user_type'];

    if (empty($username) || empty($password)) {
        $error_message = "Por favor, completa todos los campos.";
    } else {
        $authController = new AuthController();
        $error_message = $authController->login($username, $password, $user_type);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - GameOn Network</title>
    <link rel="stylesheet" href="../../Public/css/styles_login.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-image"></div>
        <div class="auth-container">
            <div class="auth-header">
                <img src="../../Resources/logo_ipd_3.png" alt="Logo GameOn Network" style="width: 400px; height: auto;">
                <h2>Iniciar Sesión</h2>
                <p>Bienvenido de vuelta a GameOn Network</p>
            </div>
            
            <div class="auth-body">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i>
                            Nombre de usuario
                        </label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?php echo htmlspecialchars($username); ?>" 
                               placeholder="Ingresa tu usuario" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            Contraseña
                        </label>
                        <div class="password-field">
                            <input type="password" id="password" name="password" class="form-control" 
                                   placeholder="Ingresa tu contraseña" required>
                            <span class="toggle-password" onclick="togglePasswordVisibility()">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
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
                        <div class="form-check">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">
                                <i class="fas fa-check-square"></i>
                                Recordarme
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt"></i>
                            Iniciar Sesión
                        </button>
                    </div>
                </form>
                
                <div class="forgot-password text-center">
                    <a href="forgot-password.php">
                        <i class="fas fa-question-circle"></i>
                        ¿Olvidaste tu contraseña?
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
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password i');
            
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

        // Agregar animación al formulario
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Iniciando sesión...';
            submitBtn.disabled = true;
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
        document.querySelector('.form-check').classList.add('selected');
    </script>
    
    <script src="../../Public/js/main.js"></script>
</body>
</html>