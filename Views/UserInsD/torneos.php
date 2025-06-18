<?php
session_start();

// Verificar si el usuario está autenticado como institución deportiva
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'instalacion') {
    header("Location: ../Auth/login.php");
    exit();
}

// Usar controladores
require_once '../../Controllers/InsDeporController.php';

$insDeporController = new InsDeporController();
$usuarioInstalacionId = $_SESSION['user_id'];

// Obtener información del usuario para mostrar torneos relevantes
$misInstalaciones = $insDeporController->getInstalacionesPorUsuario($usuarioInstalacionId);

include_once 'header.php';
?>

<link rel="stylesheet" href="../../Public/cssInsDepor/torneos.css">

<div class="torneos-container">
    <!-- Header de torneos -->
    <div class="torneos-header">
        <div class="header-content">
            <h2><i class="fas fa-trophy"></i> Gestión de Torneos</h2>
            <p>Administra y organiza torneos deportivos en tus instalaciones</p>
        </div>
        <div class="header-actions">
            <button class="btn-primary-torneos" onclick="abrirModalCrearTorneo()">
                <i class="fas fa-plus"></i> Crear Torneo
            </button>
            <button class="btn-primary-torneos" onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i> Actualizar
            </button>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="torneos-stats">
        <div class="stat-card-torneo">
            <div class="stat-icon-torneo green">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="stat-content-torneo">
                <h3 id="totalTorneos">-</h3>
                <p>Total Torneos</p>
            </div>
        </div>
        <div class="stat-card-torneo">
            <div class="stat-icon-torneo blue">
                <i class="fas fa-play-circle"></i>
            </div>
            <div class="stat-content-torneo">
                <h3 id="torneosActivos">-</h3>
                <p>En Curso</p>
            </div>
        </div>
        <div class="stat-card-torneo">
            <div class="stat-icon-torneo orange">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content-torneo">
                <h3 id="torneosProximos">-</h3>
                <p>Próximos</p>
            </div>
        </div>
        <div class="stat-card-torneo">
            <div class="stat-icon-torneo purple">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content-torneo">
                <h3 id="torneosFinalizados">-</h3>
                <p>Finalizados</p>
            </div>
        </div>
    </div>

    <!-- Filtros y controles -->
    <div class="torneos-controls">
        <div class="filtros-torneos">
            <div class="filtro-grupo">
                <label for="filtroEstado">Estado:</label>
                <select id="filtroEstado">
                    <option value="">Todos los estados</option>
                    <option value="proximo">Próximo</option>
                    <option value="inscripciones_abiertas">Inscripciones Abiertas</option>
                    <option value="inscripciones_cerradas">Inscripciones Cerradas</option>
                    <option value="activo">En Curso</option>
                    <option value="finalizado">Finalizado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            <div class="filtro-grupo">
                <label for="filtroDeporte">Deporte:</label>
                <select id="filtroDeporte">
                    <option value="">Todos los deportes</option>
                    <option value="1">Fútbol</option>
                    <option value="2">Vóley</option>
                    <option value="3">Básquet</option>
                </select>
            </div>
        </div>
        
        <div class="vista-controles">
            <button class="btn-vista active" data-vista="grid" title="Vista en tarjetas">
                <i class="fas fa-th-large"></i>
            </button>
            <button class="btn-vista" data-vista="lista" title="Vista en lista">
                <i class="fas fa-list"></i>
            </button>
        </div>
    </div>

    <!-- Grid de torneos (Vista por defecto) -->
    <div class="torneos-grid" id="torneosGrid">
        <!-- Los torneos se cargarán dinámicamente aquí -->
    </div>

    <!-- Lista de torneos (Vista alternativa) -->
    <div class="torneos-lista" id="torneosLista" style="display: none;">
        <!-- La lista se cargará dinámicamente aquí -->
    </div>
</div>

<!-- Modal para crear/editar torneo -->
<div class="modal-overlay" id="modalTorneo" style="display: none;">
    <div class="modal-torneo">
        <div class="modal-header">
            <h3 id="modalTitulo"><i class="fas fa-trophy"></i> Crear Nuevo Torneo</h3>
            <button class="btn-close" onclick="cerrarModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="formTorneo">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label for="nombreTorneo" style="display: block; margin-bottom: 5px; font-weight: 600;">Nombre del Torneo:</label>
                        <input type="text" id="nombreTorneo" name="nombre" required 
                               style="width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px;" 
                               placeholder="Ej: Copa de Fútbol 2025">
                    </div>
                    <div>
                        <label for="deporteTorneo" style="display: block; margin-bottom: 5px; font-weight: 600;">Deporte:</label>
                        <select id="deporteTorneo" name="deporte_id" required 
                                style="width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px;">
                            <option value="">Seleccionar deporte</option>
                            <option value="1">Fútbol</option>
                            <option value="2">Vóley</option>
                            <option value="3">Básquet</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="descripcionTorneo" style="display: block; margin-bottom: 5px; font-weight: 600;">Descripción:</label>
                    <textarea id="descripcionTorneo" name="descripcion" rows="3" 
                              style="width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px;" 
                              placeholder="Describe el torneo, reglas especiales, etc."></textarea>
                </div>

                <!-- ✅ NUEVO: Horarios del torneo -->
                <div style="margin-bottom: 20px;">
                    <label for="horarioTorneo" style="display: block; margin-bottom: 5px; font-weight: 600;">Horarios del Torneo:</label>
                    <select id="horarioTorneo" name="horario_torneo" required onchange="calcularHorarios()"
                            style="width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px;">
                        <option value="">Seleccionar horario</option>
                        <option value="mananas">Mañanas (Lunes a Domingo)</option>
                        <option value="tardes">Tardes (Lunes a Domingo)</option>
                        <option value="fines_semana">Solo Fines de Semana</option>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label for="fechaInicio" style="display: block; margin-bottom: 5px; font-weight: 600;">Fecha de Inicio:</label>
                        <input type="date" id="fechaInicio" name="fecha_inicio" required onchange="calcularHorarios()"
                               style="width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px;">
                    </div>
                    <div>
                        <label for="fechaFin" style="display: block; margin-bottom: 5px; font-weight: 600; opacity: 0.5;">Fecha de Fin:</label>
                        <input type="date" id="fechaFin" name="fecha_fin" disabled
                               style="width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px; background: #f8f9fa;">
                        <small style="color: #666;">Se calculará automáticamente</small>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label for="fechaInscripcionInicio" style="display: block; margin-bottom: 5px; font-weight: 600;">Inicio Inscripciones:</label>
                        <input type="date" id="fechaInscripcionInicio" name="fecha_inscripcion_inicio" required 
                               style="width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px;">
                    </div>
                    <div>
                        <label for="fechaInscripcionFin" style="display: block; margin-bottom: 5px; font-weight: 600;">Fin Inscripciones:</label>
                        <input type="date" id="fechaInscripcionFin" name="fecha_inscripcion_fin" required 
                               style="width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label for="maxEquipos" style="display: block; margin-bottom: 5px; font-weight: 600;">Máx. Equipos:</label>
                        <div style="position: relative;">
                            <input type="number" id="maxEquipos" name="max_equipos" value="10" min="4" max="15" onchange="validarEquipos(); calcularHorarios()"
                                   style="width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px;">
                            <small style="color: #28a745;">Recomendado: 10 equipos</small>
                        </div>
                        <button type="button" id="btnSolicitudIPD" onclick="solicitarIPD()" 
                                style="display: none; margin-top: 5px; padding: 5px 10px; background: #ffc107; color: #333; border: none; border-radius: 4px; font-size: 0.8em;">
                            + de 15 equipos (Solicitar IPD)
                        </button>
                    </div>
                    <div>
                        <label for="costoInscripcion" style="display: block; margin-bottom: 5px; font-weight: 600;">Costo Inscripción:</label>
                        <input type="number" id="costoInscripcion" name="costo_inscripcion" value="0" min="0" step="0.01" 
                               style="width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px;">
                    </div>
                    <div>
                        <label for="modalidadTorneo" style="display: block; margin-bottom: 5px; font-weight: 600;">Modalidad:</label>
                        <select id="modalidadTorneo" name="modalidad" onchange="calcularHorarios()"
                                style="width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px;">
                            <option value="eliminacion_simple">Eliminación Simple</option>
                            <option value="eliminacion_doble" disabled style="color: #999;">Eliminación Doble (Próximamente)</option>
                            <option value="todos_contra_todos" disabled style="color: #999;">Todos vs Todos (Próximamente)</option>
                            <option value="grupos_eliminatoria" disabled style="color: #999;">Grupos + Eliminatoria (Próximamente)</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label for="sedeTorneo" style="display: block; margin-bottom: 5px; font-weight: 600;">Sede del Torneo:</label>
                        <select id="sedeTorneo" name="institucion_sede_id" required onchange="calcularHorarios()"
                                style="width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px;">
                            <option value="">Seleccionar sede</option>
                            <?php foreach ($misInstalaciones as $instalacion): ?>
                                <option value="<?= $instalacion['id'] ?>"><?= htmlspecialchars($instalacion['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- ✅ ACTUALIZADO: Subida de imagen con ImgBB API -->
                    <div style="margin-bottom: 20px;">
                        <label for="imagenTorneo" style="display: block; margin-bottom: 5px; font-weight: 600;">Imagen del Torneo:</label>
                        <div style="display: flex; gap: 15px; align-items: flex-start;">
                            <div style="flex: 1;">
                                <input type="file" id="imagenTorneo" name="imagen_torneo" accept="image/*" onchange="subirImagen(this)"
                                       style="width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px;">
                                <small style="color: #666; display: block; margin-top: 5px;">
                                    Formatos: JPG, PNG, GIF (máx. 5MB)
                                </small>
                            </div>
                            <div id="previewImagen" style="width: 100px; height: 60px; border: 2px dashed #e9ecef; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;">
                                <i class="fas fa-image" style="color: #ccc; font-size: 24px;"></i>
                            </div>
                        </div>
                        <div id="uploadStatus" style="margin-top: 10px; display: none;">
                            <div class="upload-progress" style="background: #f8f9fa; border-radius: 8px; height: 6px; overflow: hidden;">
                                <div id="progressBar" style="height: 100%; background: linear-gradient(90deg, #28a745, #20c997); width: 0%; transition: width 0.3s ease;"></div>
                            </div>
                            <small id="uploadText" style="color: #28a745; font-weight: 600; margin-top: 5px; display: block;"></small>
                        </div>
                        <input type="hidden" id="imagenTorneoURL" name="imagen_torneo_url" value="">
                    </div>
                </div>

                <!-- ✅ NUEVO: Previsualización de horarios -->
                <div id="previsualizacionHorarios" style="display: none; margin-bottom: 20px; padding: 20px; background: #f8f9fa; border-radius: 8px; border: 2px dashed #28a745;">
                    <h4 style="margin: 0 0 15px 0; color: #28a745;"><i class="fas fa-calendar-check"></i> Previsualización del Torneo</h4>
                    <div id="infoTorneo" style="margin-bottom: 15px;"></div>
                    <div id="bracketsPreview" style="margin-bottom: 15px;"></div>
                    <div id="horariosRecomendados"></div>
                </div>

                <!-- ✅ ACTUALIZADO: Campo de premios con formato obligatorio y emojis -->
                <div style="margin-bottom: 20px;">
                    <label for="premioDescripcion" style="display: block; margin-bottom: 5px; font-weight: 600;">Descripción de Premios:</label>
                    <textarea id="premioDescripcion" name="premio_descripcion" rows="4" required readonly
                              style="width: 100%; padding: 10px; border: 2px solid #e9ecef; border-radius: 8px; background: #f8f9fa; color: #495057; font-weight: 500;"
                              onclick="habilitarEdicionPremios()">🥇 1er puesto: 
🥈 2do puesto: 
🥉 3er puesto: </textarea>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        <strong>📋 Instrucciones:</strong> Haz clic en el campo para completar la descripción de cada premio
                    </small>
                </div>
            </form>
        </div>
        <div class="modal-actions">
            <button class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-primary-torneos" onclick="guardarTorneo()">
                <i class="fas fa-save"></i> Guardar Torneo
            </button>
        </div>
    </div>
</div>

<!-- Modal para ver detalles del torneo -->
<div class="modal-overlay" id="modalDetalles" style="display: none;">
    <div class="modal-torneo">
        <div class="modal-header">
            <h3><i class="fas fa-info-circle"></i> Detalles del Torneo</h3>
            <button class="btn-close" onclick="cerrarModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="detallesContent">
            <!-- Contenido de detalles se carga dinámicamente -->
        </div>
        <div class="modal-actions">
            <button class="btn-secondary" onclick="cerrarModal()">Cerrar</button>
        </div>
    </div>
</div>

<script src="../../Public/js/torneos_insdepor.js"></script>

<?php include_once 'footer.php'; ?>