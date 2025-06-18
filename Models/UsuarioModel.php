<?php
require_once __DIR__ . '/../Config/database.php';

class UsuarioModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function obtenerUsuarioPorUsername($username, $user_type) {
        if ($user_type === 'deportista') {
            $table = 'usuarios_deportistas';
            $stmt = $this->conn->prepare("SELECT id, username, password, estado FROM $table WHERE username = ?");
        } else if ($user_type === 'instalacion') {
            $table = 'usuarios_instalaciones';
            // SOLO instalaciones privadas (excluir IPD)
            $stmt = $this->conn->prepare("SELECT id, username, password, estado FROM $table WHERE username = ? AND tipo_usuario = 'privado'");
        } else {
            return false;
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();

        $stmt->close();
        return $usuario;
    }

    public function registrarDeportista($data) {
        $stmt = $this->conn->prepare("INSERT INTO usuarios_deportistas (
            nombre, apellidos, email, username, password, telefono, fecha_nacimiento, genero, nivel_habilidad, estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo')");

        $password_hashed = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt->bind_param(
            "sssssssss",
            $data['nombre'],
            $data['apellidos'],
            $data['email'],
            $data['username'],
            $password_hashed,
            $data['telefono'],
            $data['fecha_nacimiento'],
            $data['genero'],
            $data['nivel_habilidad']
        );

        if (!$stmt->execute()) {
            return ['error' => $stmt->error];
        }

        $usuario_id = $stmt->insert_id;
        $stmt->close();

        // Deportes favoritos
        if (!empty($data['deportes_favoritos'])) {
            foreach ($data['deportes_favoritos'] as $deporte_id) {
                $insertDeporte = $this->conn->prepare("INSERT INTO usuarios_deportes (usuario_id, deporte_id) VALUES (?, ?)");
                $insertDeporte->bind_param("ii", $usuario_id, $deporte_id);
                $insertDeporte->execute();
                $insertDeporte->close();
            }
        }

        // Disponibilidad
        if (!empty($data['disponibilidad'])) {
            foreach ($data['disponibilidad'] as $dia => $franjas) {
                foreach ($franjas as $franja) {
                    $insertDisponibilidad = $this->conn->prepare("INSERT INTO usuarios_disponibilidad (usuario_id, disponibilidad_id) VALUES (?, ?)");
                    $insertDisponibilidad->bind_param("ii", $usuario_id, $franja);
                    $insertDisponibilidad->execute();
                    $insertDisponibilidad->close();
                }
            }
        }

        return ['success' => true];
    }

    public function registrarInstalacion($data) {
        $stmt = $this->conn->prepare("INSERT INTO usuarios_instalaciones (
            username, password, tipo_usuario, estado
        ) VALUES (?, ?, ?, 1)");

        $password_hashed = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt->bind_param(
            "sss",
            $data['username'],
            $password_hashed,
            $data['tipo_usuario'] ?? 'privado'
        );

        if (!$stmt->execute()) {
            return ['error' => $stmt->error];
        }

        $usuario_id = $stmt->insert_id;
        $stmt->close();

        return ['success' => true, 'usuario_id' => $usuario_id];
    }

    // ===== MÉTODOS PARA RECUPERACIÓN DE CONTRASEÑA =====
    
    /**
     * Buscar usuario por email y tipo para recuperación de contraseña
     */
    public function buscarUsuarioPorEmail($email, $user_type) {
        if ($user_type === 'deportista') {
            $stmt = $this->conn->prepare("SELECT id, username, email FROM usuarios_deportistas WHERE email = ? AND estado = 1");
        } else if ($user_type === 'instalacion') {
            // Para instalaciones, necesitamos obtener el email de la tabla instituciones_deportivas
            $stmt = $this->conn->prepare("
                SELECT ui.id, ui.username, id.email 
                FROM usuarios_instalaciones ui 
                JOIN instituciones_deportivas id ON ui.id = id.usuario_id 
                WHERE id.email = ? AND ui.estado = 1 AND ui.tipo_usuario = 'privado'
            ");
        } else {
            return false;
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $stmt->close();
        
        return $usuario;
    }
    
    /**
     * Crear token de recuperación de contraseña
     */
    public function crearTokenRecuperacion($user_id, $user_type, $email) {
        // Generar token único
        $token = bin2hex(random_bytes(32));
        
        // Token expira en 1 hora
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $this->conn->prepare("
            INSERT INTO password_recovery_tokens (user_type, user_id, email, token, expires_at) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param("sisss", $user_type, $user_id, $email, $token, $expires_at);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'token' => $token, 'expires_at' => $expires_at];
        } else {
            $stmt->close();
            return ['error' => 'Error al crear token de recuperación'];
        }
    }
    
    /**
     * Validar token de recuperación
     */
    public function validarTokenRecuperacion($token) {
        $stmt = $this->conn->prepare("
            SELECT id, user_type, user_id, email, expires_at, used 
            FROM password_recovery_tokens 
            WHERE token = ? AND used = 0 AND expires_at > NOW()
        ");
        
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $tokenData = $result->fetch_assoc();
        $stmt->close();
        
        return $tokenData;
    }
    
    /**
     * Marcar token como usado
     */
    public function marcarTokenUsado($token) {
        $stmt = $this->conn->prepare("
            UPDATE password_recovery_tokens 
            SET used = 1, used_at = NOW() 
            WHERE token = ?
        ");
        
        $stmt->bind_param("s", $token);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Actualizar contraseña del usuario
     */
    public function actualizarPassword($user_id, $user_type, $nueva_password) {
        $password_hashed = password_hash($nueva_password, PASSWORD_DEFAULT);
        
        if ($user_type === 'deportista') {
            $stmt = $this->conn->prepare("UPDATE usuarios_deportistas SET password = ? WHERE id = ?");
        } else if ($user_type === 'instalacion') {
            $stmt = $this->conn->prepare("UPDATE usuarios_instalaciones SET password = ? WHERE id = ?");
        } else {
            return false;
        }
        
        $stmt->bind_param("si", $password_hashed, $user_id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Limpiar tokens expirados (método de mantenimiento)
     */
    public function limpiarTokensExpirados() {
        $stmt = $this->conn->prepare("DELETE FROM password_recovery_tokens WHERE expires_at < NOW() OR used = 1");
        $result = $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        
        return ['success' => $result, 'deleted_tokens' => $affected_rows];
    }
}
?>