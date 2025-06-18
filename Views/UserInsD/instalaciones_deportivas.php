<?php
session_start();

// Verificar si el usuario está autenticado como institución deportiva
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'instalacion') {
    header("Location: ../Auth/login.php");
    exit();
}

// Usar los controladores
require_once '../../Controllers/InsDeporController.php';

$insDeporController = new InsDeporController();

$usuarioInstalacionId = $_SESSION['user_id'];

// Obtener todas las instalaciones del usuario
$todasLasInstalaciones = $insDeporController->getInstalacionesPorUsuario($usuarioInstalacionId);

// ✅ FILTRO POR NOMBRE
$nombreBusqueda = isset($_GET['nombre']) ? trim($_GET['nombre']) : '';

// Aplicar filtro
$instalacionesDeportivas = $todasLasInstalaciones;

// Filtrar por nombre
if ($nombreBusqueda) {
    $instalacionesDeportivas = array_filter($instalacionesDeportivas, function($instalacion) use ($nombreBusqueda) {
        return stripos($instalacion['nombre'], $nombreBusqueda) !== false;
    });
}

// Estadísticas para el filtro
$totalInstalaciones = count($todasLasInstalaciones);
$instalacionesFiltradas = count($instalacionesDeportivas);

// Incluir cabecera
include_once 'header.php';
?>
<link rel="stylesheet" href="../../Public/cssInsDepor/insta_depor.css">
<!-- ✅ LEAFLET CSS Y JS (REEMPLAZA GOOGLE MAPS) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
      crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<div class="instalaciones-container">
    <!-- Sección de Instalaciones Deportivas -->
    <div class="instalaciones-section">
        <div class="instalaciones-header">
            <h2><i class="fas fa-building"></i> Gestión de Instalaciones Deportivas</h2>
            <button class="btn-primary" id="nuevaInstalacion">
                <i class="fas fa-plus"></i> Nueva Instalación
            </button>
        </div>

        <!-- ✅ FILTRO POR NOMBRE -->
        <div class="instalaciones-filter">
            <div class="filter-header">
                <h3><i class="fas fa-search"></i> Buscar Instalaciones</h3>
                <div class="filter-stats">
                    <div class="filter-stat">
                        <i class="fas fa-chart-bar"></i>
                        <span class="number"><?= $totalInstalaciones ?></span>
                        <span class="label">Total</span>
                    </div>
                    <div class="filter-stat">
                        <i class="fas fa-search"></i>
                        <span class="number"><?= $instalacionesFiltradas ?></span>
                        <span class="label">Mostradas</span>
                    </div>
                </div>
            </div>
            <form method="GET" class="filter-controls">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="nombre">Buscar por nombre:</label>
                        <input type="text" name="nombre" id="nombre" class="filter-input" 
                               placeholder="Nombre de la instalación..." value="<?= htmlspecialchars($nombreBusqueda) ?>">
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn-filter">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <a href="instalaciones_deportivas.php" class="btn-filter-reset">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Formulario de nueva instalación (oculto por defecto) -->
        <div class="nueva-instalacion-form" id="formularioNuevaInstalacion">
            <div class="form-header">
                <h2><i class="fas fa-plus-circle"></i> Nueva Instalación Deportiva</h2>
                <button class="btn-secondary" id="cancelarNuevaInstalacion">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </div>
            <form id="formNuevaInstalacion">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombreInstalacion">Nombre de la Instalación</label>
                        <input type="text" id="nombreInstalacion" name="nombreInstalacion" placeholder="Ej: Complejo Deportivo Los Andes" required>
                    </div>
                    <div class="form-group">
                        <label for="telefonoInstalacion">Teléfono</label>
                        <input type="tel" id="telefonoInstalacion" name="telefonoInstalacion" placeholder="052-123456" required>
                    </div>
                    <div class="form-group">
                        <label for="emailInstalacion">Email</label>
                        <input type="email" id="emailInstalacion" name="emailInstalacion" placeholder="contacto@instalacion.com" required>
                    </div>
                    <div class="form-group">
                        <label for="tarifaInstalacion">Tarifa Base (S/.)</label>
                        <input type="number" id="tarifaInstalacion" name="tarifaInstalacion" step="0.01" min="0" placeholder="50.00" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="direccionInstalacion">Dirección</label>
                        <input type="text" id="direccionInstalacion" name="direccionInstalacion" placeholder="Av. Bolognesi 1234, Tacna" required>
                    </div>
                    
                    <!-- ✅ CONTENEDOR DEL MAPA AGREGADO -->
                    <div class="form-group full-width">
                        <label>Ubicación en el Mapa</label>
                        <div class="map-container">
                            <h4><i class="fas fa-map-marker-alt"></i> Selecciona la ubicación exacta</h4>
                            <div id="mapInstalacion" style="width: 100%; height: 300px; border: 2px solid #dee2e6; border-radius: 8px;"></div>
                            <div class="map-instructions">
                                Haz clic en el mapa o arrastra el marcador para seleccionar la ubicación exacta
                            </div>
                            <div class="coordenadas-info"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="latitudInstalacion">Latitud</label>
                        <input type="number" id="latitudInstalacion" name="latitudInstalacion" step="0.00000001" placeholder="-18.00660000" required readonly>
                    </div>
                    <div class="form-group">
                        <label for="longitudInstalacion">Longitud</label>
                        <input type="number" id="longitudInstalacion" name="longitudInstalacion" step="0.00000001" placeholder="-70.24630000" required readonly>
                    </div>
                    <div class="form-group full-width">
                        <label for="descripcionInstalacion">Descripción</label>
                        <textarea id="descripcionInstalacion" name="descripcionInstalacion" rows="3" placeholder="Descripción de la instalación deportiva"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="imagenInstalacion">Imagen de la Instalación</label>
                        <div class="image-upload-container">
                            <input type="file" id="imagenInstalacion" name="imagenInstalacion" accept="image/*" class="file-input">
                            <div class="image-preview" id="imagePreview">
                                <div class="upload-placeholder">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p>Arrastra una imagen aquí o haz clic para seleccionar</p>
                                    <small>Máximo 5MB - JPG, PNG, GIF</small>
                                </div>
                            </div>
                            <div class="upload-progress" id="uploadProgress" style="display: none;">
                                <div class="progress-bar">
                                    <div class="progress-fill" id="progressFill"></div>
                                </div>
                                <span class="progress-text" id="progressText">0%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Crear Instalación
                    </button>
                </div>
            </form>
        </div>

        <!-- Formulario de editar instalación (oculto por defecto) -->
        <div class="nueva-instalacion-form" id="formularioEditarInstalacion">
            <div class="form-header">
                <h2><i class="fas fa-edit"></i> Editar Instalación Deportiva</h2>
                <button class="btn-secondary" id="cancelarEditarInstalacion">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </div>
            <form id="formEditarInstalacion">
                <input type="hidden" id="editInstalacionId" name="instalacionId">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="editNombreInstalacion">Nombre de la Instalación</label>
                        <input type="text" id="editNombreInstalacion" name="nombreInstalacion" required>
                    </div>
                    <div class="form-group">
                        <label for="editTelefonoInstalacion">Teléfono</label>
                        <input type="tel" id="editTelefonoInstalacion" name="telefonoInstalacion" required>
                    </div>
                    <div class="form-group">
                        <label for="editEmailInstalacion">Email</label>
                        <input type="email" id="editEmailInstalacion" name="emailInstalacion" required>
                    </div>
                    <div class="form-group">
                        <label for="editTarifaInstalacion">Tarifa Base (S/.)</label>
                        <input type="number" id="editTarifaInstalacion" name="tarifaInstalacion" step="0.01" min="0" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="editDireccionInstalacion">Dirección</label>
                        <input type="text" id="editDireccionInstalacion" name="direccionInstalacion" required>
                    </div>
                    
                    <!-- ✅ CONTENEDOR DEL MAPA PARA EDICIÓN -->
                    <div class="form-group full-width">
                        <label>Ubicación en el Mapa</label>
                        <div class="map-container">
                            <h4><i class="fas fa-map-marker-alt"></i> Actualiza la ubicación exacta</h4>
                            <div id="mapEditarInstalacion" style="width: 100%; height: 300px; border: 2px solid #dee2e6; border-radius: 8px;"></div>
                            <div class="map-instructions">
                                Haz clic en el mapa o arrastra el marcador para actualizar la ubicación exacta
                            </div>
                            <div class="coordenadas-info-edit"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editLatitudInstalacion">Latitud</label>
                        <input type="number" id="editLatitudInstalacion" name="latitudInstalacion" step="0.00000001" required readonly>
                    </div>
                    <div class="form-group">
                        <label for="editLongitudInstalacion">Longitud</label>
                        <input type="number" id="editLongitudInstalacion" name="longitudInstalacion" step="0.00000001" required readonly>
                    </div>
                    <div class="form-group full-width">
                        <label for="editDescripcionInstalacion">Descripción</label>
                        <textarea id="editDescripcionInstalacion" name="descripcionInstalacion" rows="3"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="editImagenInstalacion">Imagen de la Instalación</label>
                        <div class="image-upload-container">
                            <input type="file" id="editImagenInstalacion" name="imagenInstalacion" accept="image/*" class="file-input">
                            <div class="image-preview" id="editImagePreview">
                                <div class="upload-placeholder">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p>Arrastra una imagen aquí o haz clic para cambiar</p>
                                    <small>Máximo 5MB - JPG, PNG, GIF</small>
                                </div>
                            </div>
                            <div class="upload-progress" id="editUploadProgress" style="display: none;">
                                <div class="progress-bar">
                                    <div class="progress-fill" id="editProgressFill"></div>
                                </div>
                                <span class="progress-text" id="editProgressText">0%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>

        <!-- Grid de instalaciones deportivas -->
        <div class="instalaciones-grid">
            <?php if (!empty($instalacionesDeportivas)): ?>
                <?php foreach ($instalacionesDeportivas as $instalacion): ?>
                <div class="instalacion-card">
                    <div class="instalacion-image">
                        <?php if (!empty($instalacion['imagen'])): ?>
                            <img src="<?= htmlspecialchars($instalacion['imagen']) ?>" alt="<?= htmlspecialchars($instalacion['nombre']) ?>">
                        <?php else: ?>
                            <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: linear-gradient(135deg, #e9ecef, #f8f9fa); color: #6c757d;">
                                <i class="fas fa-building" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="instalacion-calificacion">
                            <i class="fas fa-star"></i> <?= number_format($instalacion['calificacion'], 1) ?>
                        </div>
                    </div>
                    <div class="instalacion-content">
                        <h3><?= htmlspecialchars($instalacion['nombre']) ?></h3>
                        <p class="instalacion-description">
                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($instalacion['direccion']) ?>
                        </p>
                        <p class="instalacion-description">
                            <i class="fas fa-phone"></i> <?= htmlspecialchars($instalacion['telefono']) ?>
                        </p>
                        <p class="instalacion-description">
                            <i class="fas fa-envelope"></i> <?= htmlspecialchars($instalacion['email']) ?>
                        </p>
                        
                        <div class="instalacion-details">
                            <div class="detail-item">
                                <i class="fas fa-running"></i>
                                <span>Áreas: <span class="value"><?= $instalacion['total_areas'] ?> espacios</span></span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-dollar-sign"></i>
                                <span>Tarifa promedio: 
                                    <span class="value">
                                        <?php if ($instalacion['tarifa_promedio'] > 0): ?>
                                            S/. <?= number_format($instalacion['tarifa_promedio'], 2) ?>/hora
                                        <?php else: ?>
                                            Sin áreas configuradas
                                        <?php endif; ?>
                                    </span>
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($instalacion['descripcion'])): ?>
                        <div class="instalacion-descripcion">
                            <p><?= htmlspecialchars($instalacion['descripcion']) ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="instalacion-actions">
                            <button class="btn-small btn-success" onclick="editarInstalacion(<?= $instalacion['id'] ?>)">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button class="btn-small btn-info" onclick="verAreas(<?= $instalacion['id'] ?>)">
                                <i class="fas fa-running"></i> Ver Áreas
                            </button>
                            <button class="btn-small btn-warning" onclick="gestionarHorarios(<?= $instalacion['id'] ?>)">
                                <i class="fas fa-clock"></i> Horarios
                            </button>
                            <button class="btn-small btn-danger" onclick="eliminarInstalacion(<?= $instalacion['id'] ?>)">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php elseif ($nombreBusqueda): ?>
                <div class="no-selection">
                    <i class="fas fa-search"></i>
                    <h3>No se encontraron instalaciones</h3>
                    <p>No hay instalaciones que coincidan con la búsqueda "<?= htmlspecialchars($nombreBusqueda) ?>".</p>
                    <a href="instalaciones_deportivas.php" class="btn-primary" style="margin-top: 15px;">
                        <i class="fas fa-times"></i> Limpiar Búsqueda
                    </a>
                </div>
            <?php else: ?>
                <div class="empty-instalaciones">
                    <i class="fas fa-building"></i>
                    <h3>¡Crea tu primera instalación deportiva!</h3>
                    <p>Aún no tienes instalaciones deportivas registradas. Comienza agregando tu primera instalación.</p>
                    <button class="btn-primary" onclick="document.getElementById('nuevaInstalacion').click()" style="margin-top: 15px;">
                        <i class="fas fa-plus"></i> Nueva Instalación
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../../Public/js/insta_depor.js"></script>

<?php include_once 'footer.php'; ?>