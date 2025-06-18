<?php
require_once __DIR__ . '/../Models/ReservaModel.php';

class ReservaController {
    private $reservaModel;

    public function __construct() {
        $this->reservaModel = new ReservaModel();
    }

    public function obtenerReservas() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $action = $_GET['action'] ?? '';
            
            switch($action) {
                case 'getCronograma':
                case 'getHorarios':
                    $this->obtenerCronogramaArea();
                    break;
                case 'obtener_eventos_mes':
                    $this->obtenerEventosMes();
                    break;
                case 'obtener_proximas_reservas':
                    $this->obtenerProximasReservas();
                    break;
                case 'obtener_proximos_torneos':
                    $this->obtenerProximosTorneos();
                    break;
                case 'obtener_equipos_usuario':
                    $this->obtenerEquiposUsuario();
                    break;
                case 'reservas_institucion':
                    $this->obtenerReservasInstitucion();
                    break;
                default:
                    $this->sendError('Acción no válida');
                    break;
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            switch($action) {
                case 'crear_reserva':
                    $this->crearReserva();
                    break;
                default:
                    $this->sendError('Acción POST no válida');
                    break;
            }
        }
    }

    // ✅ NUEVO: Obtener cronograma de área deportiva
    private function obtenerCronogramaArea() {
        try {
            $areaId = intval($_GET['area_id'] ?? $_GET['id'] ?? 0);
            $fecha = $_GET['fecha'] ?? null;

            if ($areaId <= 0) {
                throw new Exception('ID de área deportiva inválido');
            }

            $cronograma = $this->reservaModel->obtenerCronogramaAreaDeportiva($areaId, $fecha);
            
            if (!$cronograma) {
                throw new Exception('Área deportiva no encontrada');
            }

            $this->sendSuccess($cronograma);

        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    // ✅ NUEVO: Obtener reservas para instituciones deportivas
    private function obtenerReservasInstitucion() {
        session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'instalacion') {
            $this->sendError('Usuario no autorizado');
            return;
        }

        try {
            $usuarioInstalacionId = $_SESSION['user_id'];
            $fecha = $_GET['fecha'] ?? null;
            
            $reservas = $this->reservaModel->obtenerReservasPorUsuarioInstalacion($usuarioInstalacionId, $fecha);
            $this->sendSuccess($reservas);
            
        } catch (Exception $e) {
            $this->sendError('Error obteniendo reservas: ' . $e->getMessage());
        }
    }

    // ✅ NUEVO: Crear reserva
    private function crearReserva() {
        session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'deportista') {
            $this->sendError('Usuario no autorizado');
            return;
        }

        try {
            $usuarioId = $_SESSION['user_id'];
            $areaId = intval($_POST['area_id']);
            $fecha = $_POST['fecha'];
            $horaInicio = $_POST['hora_inicio'];
            $horaFin = $_POST['hora_fin'];

            if (!$areaId || !$fecha || !$horaInicio || !$horaFin) {
                throw new Exception('Datos incompletos');
            }

            $resultado = $this->reservaModel->crearReserva($usuarioId, $areaId, $fecha, $horaInicio, $horaFin);
            
            if ($resultado['success']) {
                $this->sendSuccess($resultado);
            } else {
                $this->sendError($resultado['message']);
            }

        } catch (Exception $e) {
            $this->sendError('Error creando reserva: ' . $e->getMessage());
        }
    }

    // ✅ MANTENER FUNCIONES EXISTENTES
    private function obtenerEventosMes() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->sendError('Usuario no autenticado');
            return;
        }

        $fechaInicio = $_GET['fecha_inicio'] ?? '';
        $fechaFin = $_GET['fecha_fin'] ?? '';

        if (!$fechaInicio || !$fechaFin) {
            $this->sendError('Fechas requeridas');
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            
            $reservas = $this->reservaModel->obtenerReservasPorFecha($userId, $fechaInicio, $fechaFin);
            $torneos = $this->reservaModel->obtenerTorneosPorFecha($userId, $fechaInicio, $fechaFin);
            
            $eventos = [];
            
            foreach ($reservas as $reserva) {
                $eventos[] = [
                    'fecha' => $reserva['fecha'],
                    'tipo' => 'reserva',
                    'titulo' => $reserva['deporte'] . ' ' . substr($reserva['hora_inicio'], 0, 5),
                    'detalle' => $reserva['instalacion'] . ' - ' . $reserva['nombre_area'] . ' (' . $reserva['estado'] . ')'
                ];
            }
            
            foreach ($torneos as $torneo) {
                $eventos[] = [
                    'fecha' => substr($torneo['fecha'], 0, 10),
                    'tipo' => 'torneo',
                    'titulo' => $torneo['deporte_nombre'] . ' - ' . $torneo['torneo_nombre'],
                    'detalle' => $torneo['partido_detalle'] . ' - ' . $torneo['sede_nombre']
                ];
            }
            
            $this->sendSuccess($eventos);
            
        } catch (Exception $e) {
            $this->sendError('Error obteniendo eventos: ' . $e->getMessage());
        }
    }

    private function obtenerProximasReservas() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->sendError('Usuario no autenticado');
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            $reservas = $this->reservaModel->obtenerProximasReservas($userId, 5);
            $this->sendSuccess($reservas);
        } catch (Exception $e) {
            $this->sendError('Error obteniendo próximas reservas: ' . $e->getMessage());
        }
    }

    private function obtenerProximosTorneos() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->sendError('Usuario no autenticado');
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            $torneos = $this->reservaModel->obtenerProximosTorneos($userId, 5);
            $this->sendSuccess($torneos);
        } catch (Exception $e) {
            $this->sendError('Error obteniendo próximos torneos: ' . $e->getMessage());
        }
    }

    private function obtenerEquiposUsuario() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->sendError('Usuario no autenticado');
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            $equipos = $this->reservaModel->obtenerEquiposUsuario($userId);
            $this->sendSuccess($equipos);
        } catch (Exception $e) {
            $this->sendError('Error obteniendo equipos del usuario: ' . $e->getMessage());
        }
    }

    private function sendSuccess($data) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        exit;
    }

    private function sendError($message) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
}

$controller = new ReservaController();
$controller->obtenerReservas();
?>