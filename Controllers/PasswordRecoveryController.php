<?php
/**
 * Controlador para la funcionalidad de recuperación de contraseña
 * Paso 3: Lógica de negocio y manejo de solicitudes
 */

session_start();
require_once __DIR__ . '/../Models/UsuarioModel.php';

class PasswordRecoveryController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
    }

    /**
     * Procesar solicitud de recuperación de contraseña
     */
    public function solicitarRecuperacion($email, $user_type) {
        // Validar entrada
        if (empty($email) || empty($user_type)) {
            return [
                'success' => false,
                'message' => 'Email y tipo de usuario son requeridos.'
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Formato de email inválido.'
            ];
        }

        if (!in_array($user_type, ['deportista', 'instalacion'])) {
            return [
                'success' => false,
                'message' => 'Tipo de usuario inválido.'
            ];
        }

        // Buscar usuario por email
        $usuario = $this->usuarioModel->buscarUsuarioPorEmail($email, $user_type);
        
        if (!$usuario) {
            // Por seguridad, no revelamos si el email existe o no
            return [
                'success' => true,
                'message' => 'Si el email existe en nuestro sistema, recibirás un enlace de recuperación.'
            ];
        }

        // Crear token de recuperación
        $tokenResult = $this->usuarioModel->crearTokenRecuperacion(
            $usuario['id'], 
            $user_type, 
            $email
        );

        if (isset($tokenResult['error'])) {
            return [
                'success' => false,
                'message' => 'Error interno. Intenta nuevamente más tarde.'
            ];
        }

        // Preparar datos para el email
        $emailData = [
            'to_email' => $email,
            'username' => $usuario['username'],
            'token' => $tokenResult['token'],
            'expires_at' => $tokenResult['expires_at'],
            'recovery_link' => $this->generarEnlaceRecuperacion($tokenResult['token'])
        ];

        return [
            'success' => true,
            'message' => 'Si el email existe en nuestro sistema, recibirás un enlace de recuperación.',
            'email_data' => $emailData
        ];
    }

    /**
     * Validar token de recuperación y mostrar formulario
     */
    public function validarToken($token) {
        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Token requerido.'
            ];
        }

        $tokenData = $this->usuarioModel->validarTokenRecuperacion($token);
        
        if (!$tokenData) {
            return [
                'success' => false,
                'message' => 'Token inválido o expirado.'
            ];
        }

        return [
            'success' => true,
            'data' => $tokenData
        ];
    }

    /**
     * Procesar cambio de contraseña
     */
    public function cambiarPassword($token, $nueva_password, $confirmar_password) {
        // Validar entrada
        if (empty($token) || empty($nueva_password) || empty($confirmar_password)) {
            return [
                'success' => false,
                'message' => 'Todos los campos son requeridos.'
            ];
        }

        if ($nueva_password !== $confirmar_password) {
            return [
                'success' => false,
                'message' => 'Las contraseñas no coinciden.'
            ];
        }

        if (strlen($nueva_password) < 6) {
            return [
                'success' => false,
                'message' => 'La contraseña debe tener al menos 6 caracteres.'
            ];
        }

        // Validar token
        $tokenData = $this->usuarioModel->validarTokenRecuperacion($token);
        
        if (!$tokenData) {
            return [
                'success' => false,
                'message' => 'Token inválido o expirado.'
            ];
        }

        // Actualizar contraseña
        $passwordUpdated = $this->usuarioModel->actualizarPassword(
            $tokenData['user_id'],
            $tokenData['user_type'],
            $nueva_password
        );

        if (!$passwordUpdated) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la contraseña.'
            ];
        }

        // Marcar token como usado
        $this->usuarioModel->marcarTokenUsado($token);

        return [
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente. Ya puedes iniciar sesión.',
            'user_type' => $tokenData['user_type']
        ];
    }

    /**
     * Generar enlace de recuperación
     */
    private function generarEnlaceRecuperacion($token) {
        $base_url = $this->getBaseUrl();
        return $base_url . "/Views/Auth/reset-password.php?token=" . urlencode($token);
    }

    /**
     * Preparar datos para EmailJS
     */
    public function prepararDatosEmailJS($email, $user_type) {
        // Validar entrada
        if (empty($email) || empty($user_type)) {
            return [
                'success' => false,
                'message' => 'Email y tipo de usuario son requeridos.'
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Formato de email inválido.'
            ];
        }

        if (!in_array($user_type, ['deportista', 'instalacion'])) {
            return [
                'success' => false,
                'message' => 'Tipo de usuario inválido.'
            ];
        }

        // Buscar usuario por email
        $usuario = $this->usuarioModel->buscarUsuarioPorEmail($email, $user_type);
        
        if (!$usuario) {
            // Por seguridad, no revelamos si el email existe o no
            return [
                'success' => true,
                'message' => 'Si el email existe en nuestro sistema, recibirás un enlace de recuperación.',
                'send_email' => false
            ];
        }

        // Crear token de recuperación
        $tokenResult = $this->usuarioModel->crearTokenRecuperacion(
            $usuario['id'], 
            $user_type, 
            $email
        );

        if (isset($tokenResult['error'])) {
            return [
                'success' => false,
                'message' => 'Error interno. Intenta nuevamente más tarde.'
            ];
        }

        // Preparar datos específicos para EmailJS
        $recoveryLink = $this->generarEnlaceRecuperacion($tokenResult['token']);
        $expirationTime = date('d/m/Y H:i', strtotime($tokenResult['expires_at']));
        
        return [
            'success' => true,
            'message' => 'Datos preparados para envío de email.',
            'send_email' => true,
            'email_data' => [
                'to_email' => $email,
                'to_name' => $usuario['username'],
                'recovery_link' => $recoveryLink,
                'expiration_time' => $expirationTime,
                'user_type_display' => $user_type === 'deportista' ? 'Deportista' : 'Institución Deportiva'
            ]
        ];
    }

    /**
     * Obtener URL base del sitio
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME'];
        $path = dirname(dirname($script)); // Subir dos niveles desde Controllers
        
        return $protocol . '://' . $host . $path;
    }

    /**
     * Limpiar tokens expirados (método de mantenimiento)
     */
    public function limpiarTokensExpirados() {
        return $this->usuarioModel->limpiarTokensExpirados();
    }

    /**
     * Procesar solicitud AJAX
     */
    public function procesarAjax() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'message' => 'Método no permitido.'
            ]);
            return;
        }

        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'solicitar_recuperacion':
                $email = $_POST['email'] ?? '';
                $user_type = $_POST['user_type'] ?? '';
                $result = $this->solicitarRecuperacion($email, $user_type);
                echo json_encode($result);
                break;
                
            case 'preparar_emailjs':
                $email = $_POST['email'] ?? '';
                $user_type = $_POST['user_type'] ?? '';
                $result = $this->prepararDatosEmailJS($email, $user_type);
                echo json_encode($result);
                break;
                
            case 'cambiar_password':
                $token = $_POST['token'] ?? '';
                $nueva_password = $_POST['nueva_password'] ?? '';
                $confirmar_password = $_POST['confirmar_password'] ?? '';
                $result = $this->cambiarPassword($token, $nueva_password, $confirmar_password);
                echo json_encode($result);
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'message' => 'Acción no válida.'
                ]);
        }
    }
}

// Procesar solicitudes AJAX si se llama directamente
if (basename($_SERVER['PHP_SELF']) === 'PasswordRecoveryController.php') {
    $controller = new PasswordRecoveryController();
    $controller->procesarAjax();
}
?>