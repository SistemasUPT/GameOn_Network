<?php
session_start();

// Verificar si el usuario está autenticado como institución deportiva
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'instalacion') {
    header("Location: ../Auth/login.php");
    exit();
}

// Usar tu controlador existente
require_once '../../Controllers/InsDeporController.php';

$insDeporController = new InsDeporController();

// Obtener el ID del usuario de la sesión
$usuarioInstalacionId = $_SESSION['user_id'];

// Obtener datos específicos de esta institución
$misInstalaciones = $insDeporController->getInstalacionesPorUsuario($usuarioInstalacionId);
$reservasHoy = $insDeporController->getReservasHoyPorUsuario($usuarioInstalacionId);
$datosCalificacion = $insDeporController->getCalificacionPromedioPorUsuario($usuarioInstalacionId);
$estadisticasMes = $insDeporController->getEstadisticasMesPorUsuario($usuarioInstalacionId);

// Datos de la institución
$datosInstitucion = [
    'nombre' => $_SESSION['username'],
    'calificacion_promedio' => $datosCalificacion['calificacion_promedio'],
    'total_instalaciones' => $datosCalificacion['total_instalaciones']
];

// Incluir cabecera
include_once 'header.php';
?>

<div class="dashboard-container-inst">
    <!-- Panel de Bienvenida -->
    <div class="welcome-banner-inst">
        <div class="welcome-content-inst">
            <h1>¡Bienvenido <?= $_SESSION['username'] ?>!</h1>
            <p>Administra tus instalaciones deportivas de manera eficiente y maximiza tu potencial.</p>
        </div>
        <div class="welcome-actions-inst">
            <button class="btn-primary-inst btn-nueva-instalacion">
                <i class="fas fa-plus"></i> Nueva Instalación
            </button>
            <button class="btn-secondary-inst btn-ver-promociones">
                <i class="fas fa-bullhorn"></i> Crear Torneos
            </button>
        </div>
    </div>

    <!-- Métricas Principales -->
    <div class="metrics-row-inst">
        <div class="metric-card-inst">
            <div class="metric-icon-inst bg-blue">
                <i class="fas fa-building"></i>
            </div>
            <div class="metric-content-inst">
                <h3><?= count($misInstalaciones) ?></h3>
                <p>Instalaciones Registradas</p>
                <span class="metric-change-inst <?= count($misInstalaciones) > 0 ? 'positive' : 'neutral' ?>">
                    <?= count($misInstalaciones) > 0 ? 'Activas en el sistema' : 'Sin instalaciones registradas' ?>
                </span>
            </div>
        </div>

        <div class="metric-card-inst">
            <div class="metric-icon-inst bg-green">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="metric-content-inst">
                <h3><?= count($reservasHoy) ?></h3>
                <p>Reservas Hoy</p>
                <span class="metric-change-inst <?= count($reservasHoy) > 0 ? 'positive' : 'neutral' ?>">
                    <?= count($reservasHoy) > 0 ? 'Reservas activas' : 'Sin reservas hoy' ?>
                </span>
            </div>
        </div>

        <div class="metric-card-inst">
            <div class="metric-icon-inst bg-orange">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="metric-content-inst">
                <h3>S/. <?= number_format($estadisticasMes['ingresos'], 2) ?></h3>
                <p>Ingresos Este Mes</p>
                <span class="metric-change-inst <?= $estadisticasMes['ingresos'] > 0 ? 'positive' : 'neutral' ?>">
                    <?= $estadisticasMes['reservas_completadas'] ?> reservas completadas
                </span>
            </div>
        </div>

        <div class="metric-card-inst">
            <div class="metric-icon-inst bg-purple">
                <i class="fas fa-star"></i>
            </div>
            <div class="metric-content-inst">
                <h3><?= number_format($datosInstitucion['calificacion_promedio'], 1) ?></h3>
                <p>Calificación Promedio</p>
                <span class="metric-change-inst neutral">
                    Basada en <?= $datosInstitucion['total_instalaciones'] ?> instalación(es)
                </span>
            </div>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="dashboard-main-content-inst">
        <!-- Panel Izquierdo -->
        <div class="main-panel-inst">
            <!-- Mis Instalaciones -->
            <div class="content-card-inst">
                <div class="card-header-inst">
                    <h2><i class="fas fa-building"></i> Mis Instalaciones</h2>
                    <a href="areas_deportivas.php" class="btn-outline-inst">Ver Áreas Deportivas</a>
                </div>
                <div class="instalaciones-grid-inst">
                    <?php if (!empty($misInstalaciones)): ?>
                        <?php foreach (array_slice($misInstalaciones, 0, 3) as $instalacion): ?>
                        <div class="instalacion-card-inst" data-instalacion-id="<?= $instalacion['id'] ?>">
                            <div class="instalacion-imagen-inst">
                                <?php if (!empty($instalacion['imagen'])): ?>
                                    <img src="<?= htmlspecialchars($instalacion['imagen']) ?>" alt="<?= htmlspecialchars($instalacion['nombre']) ?>">
                                <?php else: ?>
                                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: linear-gradient(135deg, #e9ecef, #f8f9fa); color: #6c757d; cursor: pointer;" onclick="cambiarImagenInstalacion(<?= $instalacion['id'] ?>)">
                                        <div style="text-align: center;">
                                            <i class="fas fa-camera" style="font-size: 2rem; margin-bottom: 10px;"></i>
                                            <p style="margin: 0; font-size: 14px;">Agregar Imagen</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="instalacion-estado-inst activa">Activa</div>
                            </div>
                            <div class="instalacion-info-inst">
                                <h4><?= htmlspecialchars($instalacion['nombre']) ?></h4>
                                <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($instalacion['direccion']) ?></p>
                                <p><i class="fas fa-dollar-sign"></i> 
                                    <?php if ($instalacion['tarifa_promedio'] > 0): ?>
                                        S/. <?= number_format($instalacion['tarifa_promedio'], 2) ?> promedio/hora
                                    <?php else: ?>
                                        Sin áreas deportivas
                                    <?php endif; ?>
                                </p>
                                <div class="instalacion-stats-inst">
                                    <span><i class="fas fa-star"></i> <?= number_format($instalacion['calificacion'], 1) ?></span>
                                    <span><i class="fas fa-running"></i> <?= $instalacion['total_areas'] ?> áreas</span>
                                </div>
                            </div>
                            <div class="instalacion-actions-inst">
                                <button class="btn-small-inst btn-edit" onclick="editarInstalacion(<?= $instalacion['id'] ?>)">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                <button class="btn-small-inst btn-schedule" onclick="gestionarHorarios(<?= $instalacion['id'] ?>)">
                                    <i class="fas fa-clock"></i> Horarios
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state-inst">
                            <i class="fas fa-building"></i>
                            <h3>¡Registra tu primera instalación!</h3>
                            <p>Aún no tienes instalaciones registradas. Comienza agregando tu primera instalación deportiva.</p>
                            <button class="btn-primary-inst" style="margin-top: 15px;">
                                <i class="fas fa-plus"></i> Nueva Instalación
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reservas de Hoy -->
            <?php if (!empty($reservasHoy)): ?>
            <div class="content-card-inst">
                <div class="card-header-inst">
                    <h2><i class="fas fa-calendar-check"></i> Reservas de Hoy</h2>
                    <button class="btn-outline-inst">Ver Todas</button>
                </div>
                <div class="reservas-hoy-list">
                    <?php foreach ($reservasHoy as $reserva): ?>
                    <div class="reserva-item">
                        <div class="reserva-info">
                            <h4><?= htmlspecialchars($reserva['instalacion_nombre']) ?></h4>
                            <p><i class="fas fa-user"></i> <?= htmlspecialchars($reserva['usuario_nombre']) ?></p>
                            <p><i class="fas fa-running"></i> <?= htmlspecialchars($reserva['deporte_nombre']) ?></p>
                        </div>
                        <div class="reserva-tiempo">
                            <span class="hora"><?= date('H:i', strtotime($reserva['hora_inicio'])) ?> - <?= date('H:i', strtotime($reserva['hora_fin'])) ?></span>
                            <span class="estado estado-<?= $reserva['estado'] ?>"><?= ucfirst($reserva['estado']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Mensaje informativo -->
            <div class="content-card-inst">
                <div class="card-header-inst">
                    <h2><i class="fas fa-info-circle"></i> Información del Sistema</h2>
                </div>
                <div class="info-content">
                    <div class="alert alert-info">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <h4>¡Dashboard de Instalaciones Deportivas Actualizado!</h4>
                            <p>Panel de gestión completo con nuevas funcionalidades.</p>
                            <ul style="margin-top: 10px; padding-left: 20px;">
                                <li>✅ Gestión de imágenes con ImgBB</li>
                                <li>✅ Calificaciones basadas en áreas deportivas</li>
                                <li>✅ Tarifas promedio calculadas</li>
                                <li>✅ Instalaciones: <?= count($misInstalaciones) ?></li>
                                <li>✅ Reservas hoy: <?= count($reservasHoy) ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Derecho - Sidebar -->
        <div class="sidebar-panel-inst">
            <!-- Usuario actual -->
            <div class="sidebar-card-inst">
                <h3><i class="fas fa-user"></i> Sesión Actual</h3>
                <div class="user-session-info">
                    <p><strong>Usuario:</strong> <?= $_SESSION['username'] ?></p>
                    <p><strong>Tipo:</strong> Instalación Deportiva</p>
                    <p><strong>ID:</strong> <?= $_SESSION['user_id'] ?></p>
                    <p><strong>Estado:</strong> <span style="color: green;">Conectado</span></p>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="sidebar-card-inst">
                <h3><i class="fas fa-chart-bar"></i> Estadísticas</h3>
                <div class="stats-quick">
                    <div class="stat-quick-item">
                        <span class="number"><?= $estadisticasMes['reservas_completadas'] ?></span>
                        <span class="label">Reservas este mes</span>
                    </div>
                    <div class="stat-quick-item">
                        <span class="number"><?= $estadisticasMes['nuevos_clientes'] ?></span>
                        <span class="label">Nuevos clientes</span>
                    </div>
                    <div class="stat-quick-item">
                        <span class="number"><?= number_format($datosInstitucion['calificacion_promedio'], 1) ?></span>
                        <span class="label">Calificación promedio</span>
                    </div>
                </div>
            </div>

            <!-- Acciones disponibles -->
            <div class="sidebar-card-inst">
                <h3><i class="fas fa-cogs"></i> Funcionalidades</h3>
                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-building"></i>
                        <span>Gestión de instalaciones</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-running"></i>
                        <span>Áreas deportivas</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-calendar"></i>
                        <span>Control de horarios</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-users"></i>
                        <span>Gestión de reservas</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-trophy"></i>
                        <span>Organización de torneos</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-camera"></i>
                        <span>Gestión de imágenes</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../../Public/js/dashboard_instituciones.js"></script>

<?php include_once 'footer.php'; ?>