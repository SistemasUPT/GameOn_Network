<?php
session_start();

// Verificar si el usuario está autenticado como institución deportiva
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'instalacion') {
    header("Location: ../Auth/login.php");
    exit();
}

// Usar controladores
require_once '../../Controllers/InsDeporController.php';
require_once '../../Controllers/AreasDeportivasController.php'; // ✅ CORRECCIÓN: Nombre correcto del archivo

$insDeporController = new InsDeporController();
$areasController = new AreasDeportivasController(); // ✅ CORRECCIÓN: Nombre correcto de la clase
$usuarioInstalacionId = $_SESSION['user_id'];

// Obtener todas las instalaciones del usuario
$misInstalaciones = $insDeporController->getInstalacionesPorUsuario($usuarioInstalacionId);

include_once 'header.php';
?>

<link rel="stylesheet" href="../../Public/cssInsDepor/reservas.css">

<div class="reservas-container">
    <!-- Header de reservas -->
    <div class="reservas-header">
        <div class="header-content">
            <h2><i class="fas fa-calendar-alt"></i> Gestión de Reservas</h2>
            <p>Administra las reservas de todas tus áreas deportivas</p>
        </div>
        <div class="header-actions">
            <div class="date-selector">
                <label for="fechaReservas">Fecha:</label>
                <input type="date" id="fechaReservas" name="fechaReservas" value="<?= date('Y-m-d') ?>">
            </div>
            <button class="btn-refresh" onclick="actualizarReservas()">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filtros-reservas">
        <div class="filtro-instalacion">
            <label for="filtroInstalacion">Instalación:</label>
            <select id="filtroInstalacion" onchange="filtrarPorInstalacion()">
                <option value="">Todas las instalaciones</option>
                <?php foreach ($misInstalaciones as $instalacion): ?>
                    <option value="<?= $instalacion['id'] ?>"><?= htmlspecialchars($instalacion['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filtro-deporte">
            <label for="filtroDeporte">Deporte:</label>
            <select id="filtroDeporte" onchange="filtrarPorDeporte()">
                <option value="">Todos los deportes</option>
                <option value="1">Fútbol</option>
                <option value="2">Vóley</option>
                <option value="3">Básquet</option>
            </select>
        </div>
        <div class="filtro-estado">
            <label for="filtroEstado">Estado:</label>
            <select id="filtroEstado" onchange="filtrarPorEstado()">
                <option value="">Todos</option>
                <option value="disponible">Disponible</option>
                <option value="ocupado">Ocupado</option>
                <option value="mantenimiento">Mantenimiento</option>
            </select>
        </div>
    </div>

    <!-- Leyenda -->
    <div class="leyenda-reservas">
        <div class="leyenda-item">
            <div class="color-box disponible"></div>
            <span>Disponible</span>
        </div>
        <div class="leyenda-item">
            <div class="color-box ocupado"></div>
            <span>Reservado</span>
        </div>
        <div class="leyenda-item">
            <div class="color-box mantenimiento"></div>
            <span>Mantenimiento</span>
        </div>
        <div class="leyenda-item">
            <div class="color-box cerrado"></div>
            <span>Cerrado</span>
        </div>
    </div>

    <!-- Grid de reservas -->
    <div class="reservas-grid" id="reservasGrid">
        <?php if (!empty($misInstalaciones)): ?>
            <?php foreach ($misInstalaciones as $instalacion): ?>
                <div class="instalacion-reservas" data-instalacion-id="<?= $instalacion['id'] ?>">
                    <div class="instalacion-header">
                        <h3><i class="fas fa-building"></i> <?= htmlspecialchars($instalacion['nombre']) ?></h3>
                        <div class="instalacion-info">
                            <span class="direccion"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($instalacion['direccion']) ?></span>
                        </div>
                    </div>

                    <!-- ✅ CORRECCIÓN: Usando el método correcto -->
                    <div class="areas-deportivas">
                        <?php 
                        $areasDeportivas = $areasController->getAreasByInstitucion($instalacion['id']); // ✅ MÉTODO CORRECTO
                        if (empty($areasDeportivas)): 
                        ?>
                            <div class="no-areas">
                                <p>No hay áreas deportivas registradas para esta instalación</p>
                                <a href="areas_deportivas.php?instalacion=<?= $instalacion['id'] ?>" class="btn-primary">
                                    <i class="fas fa-plus"></i> Agregar Áreas
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($areasDeportivas as $area): 
                                $iconoDeporte = $area['deporte_id'] == 1 ? 'fas fa-futbol' : 
                                              ($area['deporte_id'] == 2 ? 'fas fa-volleyball-ball' : 'fas fa-basketball-ball');
                                $nombreDeporte = $area['deporte_id'] == 1 ? 'futbol' : 
                                               ($area['deporte_id'] == 2 ? 'voley' : 'basquet');
                            ?>
                            <div class="area-deportiva" data-area-id="<?= $area['id'] ?>" data-deporte="<?= $nombreDeporte ?>">
                                <div class="area-header">
                                    <div class="area-info">
                                        <h4><i class="<?= $iconoDeporte ?>"></i> <?= htmlspecialchars($area['nombre_area']) ?></h4>
                                        <span class="tarifa">S/. <?= number_format($area['tarifa_por_hora'], 2) ?>/hora</span>
                                    </div>
                                    <div class="area-estado <?= $area['estado'] === 'activa' ? 'activa' : 'mantenimiento' ?>">
                                        <i class="fas fa-<?= $area['estado'] === 'activa' ? 'circle' : 'tools' ?>"></i> 
                                        <?= ucfirst($area['estado']) ?>
                                    </div>
                                </div>
                                
                                <?php if ($area['estado'] === 'activa'): ?>
                                <div class="horarios-grid" data-area-id="<?= $area['id'] ?>">
                                    <!-- Los horarios se cargarán dinámicamente con JavaScript -->
                                    <div class="loading-horarios">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        <p>Cargando horarios...</p>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="horarios-grid mantenimiento-area">
                                    <div class="mantenimiento-mensaje">
                                        <i class="fas fa-tools"></i>
                                        <h4>Área en Mantenimiento</h4>
                                        <p>Esta área deportiva no está disponible temporalmente</p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-reservas">
                <i class="fas fa-calendar-times"></i>
                <h3>No hay instalaciones registradas</h3>
                <p>Primero registra instalaciones deportivas para gestionar sus reservas</p>
                <a href="instalaciones_deportivas.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Registrar Instalación
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="estadisticas-reservas">
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <h3 id="reservasHoy">-</h3>
                <p>Reservas Hoy</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3 id="horasOcupadas">-</h3>
                <p>Horas Ocupadas</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <h3 id="ingresosDia">S/. -</h3>
                <p>Ingresos del Día</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles de reserva -->
<div class="modal-overlay" id="modalReserva" style="display: none;">
    <div class="modal-reserva">
        <div class="modal-header">
            <h3><i class="fas fa-info-circle"></i> Detalles de la Reserva</h3>
            <button class="btn-close" onclick="cerrarModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- Contenido dinámico -->
        </div>
        <div class="modal-actions">
            <button class="btn-secondary" onclick="cerrarModal()">Cerrar</button>
            <button class="btn-primary" onclick="contactarCliente()">
                <i class="fas fa-phone"></i> Contactar Cliente
            </button>
        </div>
    </div>
</div>

<script src="../../Public/js/reservas_insdepor.js"></script>

<?php include_once 'footer.php'; ?>