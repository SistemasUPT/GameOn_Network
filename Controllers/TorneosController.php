<?php
// Controllers/TorneosController.php
require_once '../Models/TorneosModel.php';

class TorneosController {
    private $torneosModel;
    
    public function __construct() {
        $this->torneosModel = new TorneosModel();
    }
    
    private function verificarAutenticacion() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            $this->response(['success' => false, 'message' => 'No autenticado']);
            return false;
        }
        return true;
    }
    
    private function response($data) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // ✅ FUNCIÓN EXISTENTE - Actualizada para instituciones
    public function obtenerTorneos() {
        if (!$this->verificarAutenticacion()) return;
        
        $filtros = [
            'deporte_id' => $_GET['deporte_id'] ?? null,
            'estado' => $_GET['estado'] ?? null,
            'calificacion_min' => $_GET['calificacion_min'] ?? 0,
            'nombre' => $_GET['nombre'] ?? '',
            'organizador_tipo' => $_GET['organizador_tipo'] ?? null
        ];
        
        // ✅ NUEVO: Si es institución deportiva, filtrar por sus torneos
        if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'instalacion') {
            $filtros['usuario_instalacion_id'] = $_SESSION['user_id'];
        }
        
        try {
            $torneos = $this->torneosModel->obtenerTorneosConFiltros($filtros);
            $this->response(['success' => true, 'torneos' => $torneos]);
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error al obtener torneos: ' . $e->getMessage()]);
        }
    }

    // ✅ NUEVA FUNCIÓN: Crear torneo desde institución deportiva
    public function crearTorneo() {
        if (!$this->verificarAutenticacion()) return;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->response(['success' => false, 'message' => 'Método no permitido']);
        }
        
        // Solo instituciones pueden crear torneos por esta ruta
        if ($_SESSION['user_type'] !== 'instalacion') {
            $this->response(['success' => false, 'message' => 'Solo instituciones deportivas pueden crear torneos']);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $datos = [
            'nombre' => trim($input['nombre'] ?? ''),
            'descripcion' => trim($input['descripcion'] ?? ''),
            'deporte_id' => $input['deporte_id'] ?? 0,
            'organizador_tipo' => 'institucion', // Siempre institucion para esta ruta
            'organizador_id' => $_SESSION['user_id'],
            'institucion_sede_id' => $input['institucion_sede_id'] ?? 0,
            'max_equipos' => $input['max_equipos'] ?? 16,
            'fecha_inicio' => $input['fecha_inicio'] ?? '',
            'fecha_fin' => $input['fecha_fin'] ?? '',
            'fecha_inscripcion_inicio' => $input['fecha_inscripcion_inicio'] ?? '',
            'fecha_inscripcion_fin' => $input['fecha_inscripcion_fin'] ?? '',
            'modalidad' => $input['modalidad'] ?? 'eliminacion_simple',
            'premio_descripcion' => trim($input['premio_descripcion'] ?? ''),
            'costo_inscripcion' => $input['costo_inscripcion'] ?? 0.00,
            'imagen_torneo' => $input['imagen_torneo'] ?? null
        ];
        
        // Validaciones
        if (empty($datos['nombre']) || empty($datos['deporte_id']) || empty($datos['institucion_sede_id'])) {
            $this->response(['success' => false, 'message' => 'Nombre, deporte y sede son requeridos']);
        }
        
        if ($datos['max_equipos'] < 4 || $datos['max_equipos'] > 32) {
            $this->response(['success' => false, 'message' => 'El número de equipos debe estar entre 4 y 32']);
        }
        
        if (empty($datos['fecha_inicio']) || empty($datos['fecha_inscripcion_inicio']) || empty($datos['fecha_inscripcion_fin'])) {
            $this->response(['success' => false, 'message' => 'Todas las fechas son requeridas']);
        }
        
        try {
            $resultado = $this->torneosModel->crearTorneo($datos);
            
            if ($resultado['success']) {
                $torneoId = $resultado['torneo_id'];
                
                // ✅ NUEVO: Guardar áreas deportivas si se seleccionaron
                if (!empty($datos['areas_deportivas'])) {
                    $resultadoAreas = $this->torneosModel->guardarAreasDelTorneo(
                        $torneoId, 
                        $datos['areas_deportivas'], 
                        $_SESSION['user_id']
                    );
                    
                    if ($resultadoAreas['success']) {
                        $resultado['message'] .= ' y se reservaron ' . $resultadoAreas['reservas_creadas'] . ' áreas deportivas';
                    } else {
                        $resultado['message'] .= ' pero hubo problemas con las reservas de áreas: ' . $resultadoAreas['message'];
                    }
                }
            }
            
            $this->response($resultado);
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error al crear torneo: ' . $e->getMessage()]);
        }
    }

    // ✅ NUEVA FUNCIÓN: Actualizar torneo
    public function actualizarTorneo() {
        if (!$this->verificarAutenticacion()) return;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->response(['success' => false, 'message' => 'Método no permitido']);
        }
        
        if ($_SESSION['user_type'] !== 'instalacion') {
            $this->response(['success' => false, 'message' => 'Solo instituciones deportivas pueden editar torneos']);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $torneoId = $input['torneo_id'] ?? null;
        
        if (!$torneoId) {
            $this->response(['success' => false, 'message' => 'ID de torneo requerido']);
        }
        
        // Verificar permisos
        if (!$this->torneosModel->verificarPermisosEdicion($torneoId, $_SESSION['user_id'])) {
            $this->response(['success' => false, 'message' => 'No tienes permisos para editar este torneo']);
        }
        
        $datos = [
            'nombre' => trim($input['nombre'] ?? ''),
            'descripcion' => trim($input['descripcion'] ?? ''),
            'deporte_id' => $input['deporte_id'] ?? 0,
            'organizador_id' => $_SESSION['user_id'],
            'institucion_sede_id' => $input['institucion_sede_id'] ?? 0,
            'max_equipos' => $input['max_equipos'] ?? 16,
            'fecha_inicio' => $input['fecha_inicio'] ?? '',
            'fecha_fin' => $input['fecha_fin'] ?? '',
            'fecha_inscripcion_inicio' => $input['fecha_inscripcion_inicio'] ?? '',
            'fecha_inscripcion_fin' => $input['fecha_inscripcion_fin'] ?? '',
            'modalidad' => $input['modalidad'] ?? 'eliminacion_simple',
            'premio_descripcion' => trim($input['premio_descripcion'] ?? ''),
            'costo_inscripcion' => $input['costo_inscripcion'] ?? 0.00,
            'imagen_torneo' => $input['imagen_torneo'] ?? null
        ];
        
        try {
            $resultado = $this->torneosModel->actualizarTorneo($torneoId, $datos);
            $this->response($resultado);
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error al actualizar torneo: ' . $e->getMessage()]);
        }
    }

    // ✅ FUNCIONES EXISTENTES - Mantener como están
    public function obtenerDetallesTorneo() {
        if (!$this->verificarAutenticacion()) return;
        
        $torneo_id = $_GET['torneo_id'] ?? null;
        
        if (!$torneo_id) {
            $this->response(['success' => false, 'message' => 'ID de torneo requerido']);
        }
        
        try {
            $torneo = $this->torneosModel->obtenerDetallesTorneo($torneo_id);
            $equipos = $this->torneosModel->obtenerEquiposInscritos($torneo_id);
            
            $this->response([
                'success' => true, 
                'torneo' => $torneo,
                'equipos_inscritos' => $equipos
            ]);
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error obteniendo detalles: ' . $e->getMessage()]);
        }
    }
    
    public function inscribirEquipo() {
        if (!$this->verificarAutenticacion()) return;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->response(['success' => false, 'message' => 'Método no permitido']);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $torneo_id = $input['torneo_id'] ?? null;
        $equipo_id = $input['equipo_id'] ?? null;
        
        if (!$torneo_id || !$equipo_id) {
            $this->response(['success' => false, 'message' => 'Datos incompletos']);
        }
        
        try {
            $resultado = $this->torneosModel->inscribirEquipoEnTorneo($torneo_id, $equipo_id, $_SESSION['user_id']);
            $this->response($resultado);
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => 'Error en inscripción: ' . $e->getMessage()]);
        }
    }
    
    // ✅ MANEJADOR DE RUTAS ACTUALIZADO
    public function handleRequest() {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'obtener_torneos':
                $this->obtenerTorneos();
                break;
            case 'crear_torneo':
                $this->crearTorneo();
                break;
            case 'actualizar_torneo':
                $this->actualizarTorneo();
                break;
            case 'obtener_detalles':
                $this->obtenerDetallesTorneo();
                break;
            case 'inscribir_equipo':
                $this->inscribirEquipo();
                break;
            default:
                $this->response(['success' => false, 'message' => 'Acción no válida']);
                break;
        }
    }
}

// Manejo directo si se llama al archivo
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new TorneosController();
    $controller->handleRequest();
}
?>