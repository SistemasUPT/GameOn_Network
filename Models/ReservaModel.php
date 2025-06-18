<?php
require_once __DIR__ . '/../Config/database.php';

class ReservaModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // ✅ NUEVO: Obtener reservas por usuario usando áreas deportivas
    public function obtenerReservasPorFecha($userId, $fechaInicio, $fechaFin) {
        $sql = "SELECT r.*, 
                       ad.nombre_area,
                       ad.tarifa_por_hora,
                       id.nombre as instalacion,
                       d.nombre as deporte
                FROM reservas r
                INNER JOIN areas_deportivas ad ON r.area_deportiva_id = ad.id
                INNER JOIN instituciones_deportivas id ON ad.institucion_deportiva_id = id.id
                INNER JOIN deportes d ON ad.deporte_id = d.id
                WHERE r.id_usuario = ? 
                AND r.fecha BETWEEN ? AND ?
                AND r.estado IN ('confirmada', 'pendiente')
                ORDER BY r.fecha ASC, r.hora_inicio ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $userId, $fechaInicio, $fechaFin);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ✅ NUEVO: Obtener próximas reservas usando áreas deportivas
    public function obtenerProximasReservas($userId, $limite = 5) {
        $sql = "SELECT r.*, 
                       ad.nombre_area,
                       ad.tarifa_por_hora,
                       id.nombre as instalacion,
                       d.nombre as deporte
                FROM reservas r
                INNER JOIN areas_deportivas ad ON r.area_deportiva_id = ad.id
                INNER JOIN instituciones_deportivas id ON ad.institucion_deportiva_id = id.id
                INNER JOIN deportes d ON ad.deporte_id = d.id
                WHERE r.id_usuario = ? 
                AND r.fecha >= CURDATE()
                AND r.estado IN ('confirmada', 'pendiente')
                ORDER BY r.fecha ASC, r.hora_inicio ASC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ✅ NUEVO: Obtener reservas por instalación para instituciones deportivas
    public function obtenerReservasPorUsuarioInstalacion($usuarioInstalacionId, $fecha = null) {
        $whereDate = $fecha ? "AND DATE(r.fecha) = ?" : "";
        
        $sql = "SELECT r.*, 
                       ad.nombre_area,
                       ad.tarifa_por_hora,
                       id.nombre as instalacion_nombre,
                       d.nombre as deporte_nombre,
                       ud.nombre as cliente_nombre,
                       ud.telefono as cliente_telefono
                FROM reservas r
                INNER JOIN areas_deportivas ad ON r.area_deportiva_id = ad.id
                INNER JOIN instituciones_deportivas id ON ad.institucion_deportiva_id = id.id
                INNER JOIN deportes d ON ad.deporte_id = d.id
                INNER JOIN usuarios_deportistas ud ON r.id_usuario = ud.id
                WHERE id.usuario_instalacion_id = ?
                $whereDate
                AND r.estado IN ('confirmada', 'pendiente')
                ORDER BY r.fecha ASC, r.hora_inicio ASC";
        
        $stmt = $this->conn->prepare($sql);
        if ($fecha) {
            $stmt->bind_param("is", $usuarioInstalacionId, $fecha);
        } else {
            $stmt->bind_param("i", $usuarioInstalacionId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ✅ NUEVO: Obtener cronograma de disponibilidad por área deportiva
    public function obtenerCronogramaAreaDeportiva($areaId, $fecha = null) {
        if (!$fecha) {
            $fecha = date('Y-m-d');
        }

        // Obtener información del área
        $sqlArea = "SELECT ad.*, id.nombre as instalacion_nombre, d.nombre as deporte_nombre
                    FROM areas_deportivas ad
                    INNER JOIN instituciones_deportivas id ON ad.institucion_deportiva_id = id.id
                    INNER JOIN deportes d ON ad.deporte_id = d.id
                    WHERE ad.id = ?";
        $stmtArea = $this->conn->prepare($sqlArea);
        $stmtArea->bind_param("i", $areaId);
        $stmtArea->execute();
        $resultadoArea = $stmtArea->get_result();
        $area = $resultadoArea->fetch_assoc();

        if (!$area) {
            return null;
        }

        // Obtener horarios del área para el día
        $diaSemana = $this->obtenerNombreDia($fecha);
        $sqlHorarios = "SELECT * FROM areas_horarios 
                        WHERE area_deportiva_id = ? AND dia = ? AND disponible = 1";
        $stmtHorarios = $this->conn->prepare($sqlHorarios);
        $stmtHorarios->bind_param("is", $areaId, $diaSemana);
        $stmtHorarios->execute();
        $resultadoHorarios = $stmtHorarios->get_result();
        $horarios = $resultadoHorarios->fetch_assoc();

        if (!$horarios) {
            return [
                'area' => $area,
                'fecha' => $fecha,
                'cerrado' => true,
                'cronograma' => []
            ];
        }

        // Obtener reservas existentes para la fecha
        $sqlReservas = "SELECT hora_inicio, hora_fin, estado, id_usuario
                        FROM reservas 
                        WHERE area_deportiva_id = ? AND fecha = ? AND estado != 'cancelada'";
        $stmtReservas = $this->conn->prepare($sqlReservas);
        $stmtReservas->bind_param("is", $areaId, $fecha);
        $stmtReservas->execute();
        $resultadoReservas = $stmtReservas->get_result();
        $reservasExistentes = $resultadoReservas->fetch_all(MYSQLI_ASSOC);

        // Generar cronograma
        $cronograma = $this->generarCronogramaPorHorarios(
            $horarios['hora_apertura'], 
            $horarios['hora_cierre'], 
            $reservasExistentes
        );

        return [
            'area' => $area,
            'fecha' => $fecha,
            'horarios' => $horarios,
            'cronograma' => $cronograma
        ];
    }

    // ✅ NUEVO: Crear reserva en área deportiva
    public function crearReserva($usuarioId, $areaId, $fecha, $horaInicio, $horaFin) {
        // Verificar disponibilidad
        if ($this->verificarDisponibilidad($areaId, $fecha, $horaInicio, $horaFin)) {
            $sql = "INSERT INTO reservas (id_usuario, area_deportiva_id, fecha, hora_inicio, hora_fin, estado) 
                    VALUES (?, ?, ?, ?, ?, 'pendiente')";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iisss", $usuarioId, $areaId, $fecha, $horaInicio, $horaFin);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'reserva_id' => $this->conn->insert_id,
                    'message' => 'Reserva creada exitosamente'
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'El horario no está disponible'
        ];
    }

    // ✅ NUEVO: Verificar disponibilidad de área
    public function verificarDisponibilidad($areaId, $fecha, $horaInicio, $horaFin) {
        // Verificar que el área esté activa
        $sqlArea = "SELECT estado FROM areas_deportivas WHERE id = ?";
        $stmtArea = $this->conn->prepare($sqlArea);
        $stmtArea->bind_param("i", $areaId);
        $stmtArea->execute();
        $resultArea = $stmtArea->get_result();
        $area = $resultArea->fetch_assoc();
        
        if (!$area || $area['estado'] !== 'activa') {
            return false;
        }

        // Verificar horarios de atención
        $diaSemana = $this->obtenerNombreDia($fecha);
        $sqlHorarios = "SELECT hora_apertura, hora_cierre FROM areas_horarios 
                        WHERE area_deportiva_id = ? AND dia = ? AND disponible = 1";
        $stmtHorarios = $this->conn->prepare($sqlHorarios);
        $stmtHorarios->bind_param("is", $areaId, $diaSemana);
        $stmtHorarios->execute();
        $resultHorarios = $stmtHorarios->get_result();
        $horarios = $resultHorarios->fetch_assoc();

        if (!$horarios || $horaInicio < $horarios['hora_apertura'] || $horaFin > $horarios['hora_cierre']) {
            return false;
        }

        // Verificar conflictos con reservas existentes
        $sqlConflictos = "SELECT COUNT(*) as conflictos FROM reservas 
                          WHERE area_deportiva_id = ? AND fecha = ? 
                          AND estado IN ('confirmada', 'pendiente')
                          AND ((hora_inicio <= ? AND hora_fin > ?) OR 
                               (hora_inicio < ? AND hora_fin >= ?) OR
                               (hora_inicio >= ? AND hora_fin <= ?))";
        
        $stmtConflictos = $this->conn->prepare($sqlConflictos);
        $stmtConflictos->bind_param("isssssss", $areaId, $fecha, $horaInicio, $horaInicio, $horaFin, $horaFin, $horaInicio, $horaFin);
        $stmtConflictos->execute();
        $resultConflictos = $stmtConflictos->get_result();
        $conflictos = $resultConflictos->fetch_assoc();

        return $conflictos['conflictos'] == 0;
    }

    // ✅ FUNCIONES AUXILIARES MEJORADAS
    private function generarCronogramaPorHorarios($horaApertura, $horaCierre, $reservasExistentes) {
        $cronograma = [];
        $horaActual = new DateTime($horaApertura);
        $horaFin = new DateTime($horaCierre);
        $intervalo = new DateInterval('PT30M'); // 30 minutos

        while ($horaActual < $horaFin) {
            $horaSiguiente = clone $horaActual;
            $horaSiguiente->add($intervalo);
            
            // Asegurarse de no pasar la hora de cierre
            if ($horaSiguiente > $horaFin) {
                $horaSiguiente = $horaFin;
            }

            $ocupado = $this->verificarIntervaloOcupado(
                $horaActual->format('H:i:s'), 
                $horaSiguiente->format('H:i:s'), 
                $reservasExistentes
            );

            $cronograma[] = [
                'hora_inicio' => $horaActual->format('H:i'),
                'hora_fin' => $horaSiguiente->format('H:i'),
                'disponible' => !$ocupado,
                'estado' => $ocupado ? 'ocupado' : 'disponible'
            ];

            $horaActual = $horaSiguiente;
        }

        return $cronograma;
    }

    private function verificarIntervaloOcupado($horaInicio, $horaFin, $reservasExistentes) {
        foreach ($reservasExistentes as $reserva) {
            $reservaInicio = $reserva['hora_inicio'];
            $reservaFin = $reserva['hora_fin'];

            if (($horaInicio >= $reservaInicio && $horaInicio < $reservaFin) ||
                ($horaFin > $reservaInicio && $horaFin <= $reservaFin) ||
                ($horaInicio <= $reservaInicio && $horaFin >= $reservaFin)) {
                return true;
            }
        }
        return false;
    }

    private function obtenerNombreDia($fecha) {
        $dias = [
            'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miercoles',
            'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sabado', 'Sunday' => 'Domingo'
        ];
        
        $nombreIngles = date('l', strtotime($fecha));
        return $dias[$nombreIngles] ?? $nombreIngles;
    }

    // ✅ MANTENER FUNCIONES EXISTENTES PARA TORNEOS
    public function obtenerTorneosPorFecha($userId, $fechaInicio, $fechaFin) {
        $sql = "SELECT DISTINCT tp.fecha_partido as fecha, t.nombre as torneo_nombre,
                       CONCAT(el.nombre, ' vs ', ev.nombre) as partido_detalle,
                       tp.fase, tp.estado_partido,
                       i.nombre as sede_nombre,
                       d.nombre as deporte_nombre,
                       tp.id as partido_id
                FROM torneos_partidos tp
                INNER JOIN torneos t ON tp.torneo_id = t.id
                INNER JOIN deportes d ON t.deporte_id = d.id
                INNER JOIN equipos el ON tp.equipo_local_id = el.id
                INNER JOIN equipos ev ON tp.equipo_visitante_id = ev.id
                INNER JOIN instituciones_deportivas i ON t.institucion_sede_id = i.id
                INNER JOIN equipo_miembros em ON (em.equipo_id = el.id OR em.equipo_id = ev.id)
                WHERE em.usuario_id = ?
                AND DATE(tp.fecha_partido) BETWEEN ? AND ?
                AND tp.estado_partido IN ('programado', 'en_curso')
                GROUP BY tp.id
                ORDER BY tp.fecha_partido ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $userId, $fechaInicio, $fechaFin);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerProximosTorneos($userId, $limite = 5) {
        $sql = "SELECT DISTINCT tp.fecha_partido, t.nombre as torneo_nombre,
                       CONCAT(el.nombre, ' vs ', ev.nombre) as partido_detalle,
                       tp.fase, i.nombre as sede_nombre,
                       d.nombre as deporte_nombre,
                       tp.id as partido_id
                FROM torneos_partidos tp
                INNER JOIN torneos t ON tp.torneo_id = t.id
                INNER JOIN deportes d ON t.deporte_id = d.id
                INNER JOIN equipos el ON tp.equipo_local_id = el.id
                INNER JOIN equipos ev ON tp.equipo_visitante_id = ev.id
                INNER JOIN instituciones_deportivas i ON t.institucion_sede_id = i.id
                INNER JOIN equipo_miembros em ON (em.equipo_id = el.id OR em.equipo_id = ev.id)
                WHERE em.usuario_id = ?
                AND tp.fecha_partido >= NOW()
                AND tp.estado_partido IN ('programado', 'en_curso')
                GROUP BY tp.id
                ORDER BY tp.fecha_partido ASC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerEquiposUsuario($userId) {
        $sql = "SELECT e.id, e.nombre, d.nombre as deporte
                FROM equipos e
                INNER JOIN equipo_miembros em ON e.id = em.equipo_id
                INNER JOIN deportes d ON e.deporte_id = d.id
                WHERE em.usuario_id = ?
                AND e.estado = 1
                ORDER BY e.nombre ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>