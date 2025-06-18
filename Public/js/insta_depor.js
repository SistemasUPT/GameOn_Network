// Configuración de ImgBB API
const IMGBB_API_KEY = 'f94d58c09424ff225d85feee613de3a6'; // Tu API key actual

// Variables globales para mapas Leaflet
let currentImageUrl = null;
let map = null;
let mapEdit = null;
let marker = null;
let markerEdit = null;
let selectedCoordinates = { lat: -18.0066, lng: -70.2463 }; // Tacna por defecto

// Event listeners al cargar el DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Sistema de instalaciones deportivas con Leaflet Maps inicializado');
    inicializarEventos();
    setupImageUpload('imagenInstalacion', 'imagePreview', 'uploadProgress');
    setupImageUpload('editImagenInstalacion', 'editImagePreview', 'editUploadProgress');
});

// Inicializar eventos
function inicializarEventos() {
    const nuevaInstalacionBtn = document.getElementById('nuevaInstalacion');
    const cancelarNuevaBtn = document.getElementById('cancelarNuevaInstalacion');
    const cancelarEditarBtn = document.getElementById('cancelarEditarInstalacion');
    const formNueva = document.getElementById('formNuevaInstalacion');
    const formEditar = document.getElementById('formEditarInstalacion');
    
    if (nuevaInstalacionBtn) nuevaInstalacionBtn.addEventListener('click', mostrarFormularioNueva);
    if (cancelarNuevaBtn) cancelarNuevaBtn.addEventListener('click', cancelarFormularioNueva);
    if (cancelarEditarBtn) cancelarEditarBtn.addEventListener('click', cancelarFormularioEditar);
    if (formNueva) formNueva.addEventListener('submit', crearNuevaInstalacion);
    if (formEditar) formEditar.addEventListener('submit', actualizarInstalacion);
}

// ✅ LEAFLET MAPS - Inicializar mapa
function initMap(containerId = 'mapInstalacion', isEdit = false) {
    const mapContainer = document.getElementById(containerId);
    if (!mapContainer) {
        console.log('Contenedor del mapa no disponible:', containerId);
        return;
    }

    // Limpiar contenedor si ya existe un mapa
    mapContainer.innerHTML = '';

    // Crear el mapa con Leaflet
    const currentMap = L.map(containerId, {
        center: [selectedCoordinates.lat, selectedCoordinates.lng],
        zoom: 15,
        zoomControl: true,
        scrollWheelZoom: true
    });

    // Agregar tiles de OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(currentMap);

    // Crear marcador personalizado
    const customIcon = L.divIcon({
        className: 'custom-marker',
        html: '<i class="fas fa-map-marker-alt" style="color: #2c5aa0; font-size: 24px;"></i>',
        iconSize: [24, 24],
        iconAnchor: [12, 12]
    });

    // Crear marcador
    const currentMarker = L.marker([selectedCoordinates.lat, selectedCoordinates.lng], {
        icon: customIcon,
        draggable: true
    }).addTo(currentMap);

    // Asignar a variables globales
    if (isEdit) {
        mapEdit = currentMap;
        markerEdit = currentMarker;
    } else {
        map = currentMap;
        marker = currentMarker;
    }

    // Event listener para arrastrar marcador
    currentMarker.on('dragend', function(e) {
        const position = e.target.getLatLng();
        selectedCoordinates = {
            lat: position.lat,
            lng: position.lng
        };
        updateCoordinatesInputs(isEdit);
        updateAddressFromCoordinates(isEdit);
    });

    // Event listener para click en el mapa
    currentMap.on('click', function(e) {
        selectedCoordinates = {
            lat: e.latlng.lat,
            lng: e.latlng.lng
        };
        currentMarker.setLatLng([selectedCoordinates.lat, selectedCoordinates.lng]);
        updateCoordinatesInputs(isEdit);
        updateAddressFromCoordinates(isEdit);
    });

    // Actualizar inputs iniciales
    updateCoordinatesInputs(isEdit);
    
    console.log(`✅ Mapa Leaflet ${isEdit ? 'de edición' : 'nuevo'} inicializado correctamente`);
}

// Actualizar inputs de coordenadas
function updateCoordinatesInputs(isEdit = false) {
    const prefix = isEdit ? 'edit' : '';
    const latInput = document.getElementById(`${prefix}LatitudInstalacion`);
    const lngInput = document.getElementById(`${prefix}LongitudInstalacion`);
    
    if (latInput) latInput.value = selectedCoordinates.lat.toFixed(8);
    if (lngInput) lngInput.value = selectedCoordinates.lng.toFixed(8);
    
    // Actualizar info visual si existe
    updateCoordinatesDisplay(isEdit);
}

// Actualizar display visual de coordenadas
function updateCoordinatesDisplay(isEdit = false) {
    const className = isEdit ? '.coordenadas-info-edit' : '.coordenadas-info';
    const coordenadasInfo = document.querySelector(className);
    if (coordenadasInfo) {
        coordenadasInfo.innerHTML = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 10px;">
                <div style="background: #f8f9fa; padding: 10px; border-radius: 6px; text-align: center;">
                    <strong style="display: block; color: #2c5aa0; font-size: 14px; margin-bottom: 5px;">Latitud</strong>
                    <span style="font-size: 12px; color: #6c757d; font-family: monospace;">${selectedCoordinates.lat.toFixed(8)}</span>
                </div>
                <div style="background: #f8f9fa; padding: 10px; border-radius: 6px; text-align: center;">
                    <strong style="display: block; color: #2c5aa0; font-size: 14px; margin-bottom: 5px;">Longitud</strong>
                    <span style="font-size: 12px; color: #6c757d; font-family: monospace;">${selectedCoordinates.lng.toFixed(8)}</span>
                </div>
            </div>
        `;
    }
}

// ✅ GEOCODIFICACIÓN con API gratuita de Nominatim
async function updateAddressFromCoordinates(isEdit = false) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${selectedCoordinates.lat}&lon=${selectedCoordinates.lng}&zoom=18&addressdetails=1`);
        const data = await response.json();
        
        if (data && data.display_name) {
            const prefix = isEdit ? 'edit' : '';
            const direccionInput = document.getElementById(`${prefix}DireccionInstalacion`);
            if (direccionInput && !direccionInput.value.trim()) {
                direccionInput.value = data.display_name;
            }
        }
    } catch (error) {
        console.log('No se pudo obtener la dirección automáticamente');
    }
}

// Buscar dirección y centrar mapa
async function searchAddress(address, isEdit = false) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1&countrycodes=pe`);
        const data = await response.json();
        
        if (data && data.length > 0) {
            const location = data[0];
            selectedCoordinates = {
                lat: parseFloat(location.lat),
                lng: parseFloat(location.lon)
            };
            
            const currentMap = isEdit ? mapEdit : map;
            const currentMarker = isEdit ? markerEdit : marker;
            
            if (currentMap && currentMarker) {
                currentMap.setView([selectedCoordinates.lat, selectedCoordinates.lng], 15);
                currentMarker.setLatLng([selectedCoordinates.lat, selectedCoordinates.lng]);
                updateCoordinatesInputs(isEdit);
            }
            
            showNotification('Ubicación encontrada y actualizada', 'success');
        } else {
            showNotification('No se pudo encontrar la dirección', 'error');
        }
    } catch (error) {
        console.error('Error buscando dirección:', error);
        showNotification('Error al buscar la dirección', 'error');
    }
}

// Mostrar formulario nueva instalación
function mostrarFormularioNueva() {
    document.getElementById('formularioNuevaInstalacion').classList.add('active');
    
    // Resetear coordenadas a Tacna por defecto
    selectedCoordinates = { lat: -18.0066, lng: -70.2463 };
    
    // Inicializar mapa después de mostrar el formulario
    setTimeout(() => {
        initMap('mapInstalacion', false);
        
        // Agregar listener al campo de dirección
        const direccionInput = document.getElementById('direccionInstalacion');
        if (direccionInput) {
            direccionInput.removeEventListener('blur', direccionBlurHandler);
            direccionInput.addEventListener('blur', direccionBlurHandler);
        }
    }, 300);
}

// Handler para el evento blur de dirección
function direccionBlurHandler() {
    if (this.value.trim()) {
        searchAddress(this.value.trim(), false);
    }
}

// Editar instalación
async function editarInstalacion(instalacionId) {
    try {
        currentImageUrl = null;
        
        // Obtener datos de la instalación
        const response = await fetch(`../../Models/InsDeporModel.php?action=get&id=${instalacionId}`);
        const result = await response.json();
        
        if (!result.success) {
            showNotification('Error: ' + result.message, 'error');
            return;
        }
        
        const instalacion = result.data;
        console.log('Datos de la instalación:', instalacion);
        
        // Llenar formulario de edición
        document.getElementById('editInstalacionId').value = instalacion.id;
        document.getElementById('editNombreInstalacion').value = instalacion.nombre;
        document.getElementById('editDireccionInstalacion').value = instalacion.direccion;
        document.getElementById('editLatitudInstalacion').value = instalacion.latitud;
        document.getElementById('editLongitudInstalacion').value = instalacion.longitud;
        document.getElementById('editTarifaInstalacion').value = instalacion.tarifa || 0;
        document.getElementById('editTelefonoInstalacion').value = instalacion.telefono;
        document.getElementById('editEmailInstalacion').value = instalacion.email;
        document.getElementById('editDescripcionInstalacion').value = instalacion.descripcion || '';
        
        // Actualizar coordenadas seleccionadas
        selectedCoordinates = {
            lat: parseFloat(instalacion.latitud) || -18.0066,
            lng: parseFloat(instalacion.longitud) || -70.2463
        };
        
        // Mostrar imagen actual si existe
        const editImagePreview = document.getElementById('editImagePreview');
        if (instalacion.imagen && instalacion.imagen.trim() !== '') {
            currentImageUrl = instalacion.imagen;
            editImagePreview.innerHTML = `
                <img src="${instalacion.imagen}" alt="Imagen actual" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                <div class="image-overlay">
                    <button type="button" class="btn-change-image" onclick="document.getElementById('editImagenInstalacion').click()">
                        <i class="fas fa-edit"></i> Cambiar
                    </button>
                </div>
            `;
        } else {
            editImagePreview.innerHTML = `
                <div class="upload-placeholder">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Arrastra una imagen aquí o haz clic para seleccionar</p>
                    <small>Máximo 5MB - JPG, PNG, GIF</small>
                </div>
            `;
        }
        
        // Mostrar formulario
        document.getElementById('formularioEditarInstalacion').classList.add('active');
        
        // Inicializar mapa para edición
        setTimeout(() => {
            initMap('mapEditarInstalacion', true);
            
            const direccionInput = document.getElementById('editDireccionInstalacion');
            if (direccionInput) {
                direccionInput.removeEventListener('blur', direccionEditBlurHandler);
                direccionInput.addEventListener('blur', direccionEditBlurHandler);
            }
        }, 300);
        
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al cargar datos de la instalación: ' + error.message, 'error');
    }
}

// Handler para el evento blur de dirección en edición
function direccionEditBlurHandler() {
    if (this.value.trim()) {
        searchAddress(this.value.trim(), true);
    }
}

// Cancelar formulario nueva instalación
function cancelarFormularioNueva() {
    document.getElementById('formularioNuevaInstalacion').classList.remove('active');
    resetForm('formNuevaInstalacion');
    
    if (map) {
        map.remove();
        map = null;
        marker = null;
    }
}

// Cancelar formulario editar instalación
function cancelarFormularioEditar() {
    document.getElementById('formularioEditarInstalacion').classList.remove('active');
    resetForm('formEditarInstalacion');
    
    if (mapEdit) {
        mapEdit.remove();
        mapEdit = null;
        markerEdit = null;
    }
}

// Configurar upload de imágenes
function setupImageUpload(fileInputId, previewId, progressId) {
    const fileInput = document.getElementById(fileInputId);
    const preview = document.getElementById(previewId);
    const progress = document.getElementById(progressId);
    
    if (!fileInput || !preview || !progress) return;
    
    preview.addEventListener('click', () => fileInput.click());
    
    preview.addEventListener('dragover', (e) => {
        e.preventDefault();
        preview.classList.add('drag-over');
    });
    
    preview.addEventListener('dragleave', () => {
        preview.classList.remove('drag-over');
    });
    
    preview.addEventListener('drop', (e) => {
        e.preventDefault();
        preview.classList.remove('drag-over');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleImageUpload(files[0], previewId, progressId);
        }
    });
    
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleImageUpload(e.target.files[0], previewId, progressId);
        }
    });
}

// Manejar subida de imagen
async function handleImageUpload(file, previewId, progressId) {
    if (IMGBB_API_KEY === 'TU_API_KEY_AQUI') {
        alert('ERROR: Debes configurar tu API KEY de ImgBB en el archivo JS');
        return;
    }
    
    if (!file.type.startsWith('image/')) {
        alert('Por favor selecciona una imagen válida');
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) {
        alert('La imagen es muy pesada. Máximo 5MB');
        return;
    }
    
    const preview = document.getElementById(previewId);
    const progress = document.getElementById(progressId);
    const progressFill = progress.querySelector('.progress-fill');
    const progressText = progress.querySelector('.progress-text');
    
    progress.style.display = 'block';
    progressFill.style.width = '0%';
    progressText.textContent = '0%';
    
    try {
        const formData = new FormData();
        formData.append('image', file);
        
        progressFill.style.width = '30%';
        progressText.textContent = '30%';
        
        const response = await fetch(`https://api.imgbb.com/1/upload?key=${IMGBB_API_KEY}`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            currentImageUrl = result.data.url;
            
            progressFill.style.width = '100%';
            progressText.textContent = '100%';
            
            const fileInputId = previewId.replace('Preview', 'Instalacion');
            preview.innerHTML = `
                <img src="${result.data.url}" alt="Preview" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                <div class="image-overlay">
                    <button type="button" class="btn-change-image" onclick="document.getElementById('${fileInputId}').click()">
                        <i class="fas fa-edit"></i> Cambiar
                    </button>
                </div>
            `;
            
            setTimeout(() => {
                progress.style.display = 'none';
            }, 1000);
            
            showNotification('Imagen subida exitosamente', 'success');
            
        } else {
            throw new Error(result.error?.message || 'Error al subir imagen');
        }
        
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al subir la imagen: ' + error.message, 'error');
        progress.style.display = 'none';
    }
}

// Crear nueva instalación
async function crearNuevaInstalacion(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    if (currentImageUrl) {
        formData.append('imagen_url', currentImageUrl);
    }
    formData.append('action', 'create');
    
    try {
        const response = await fetch('../../Models/InsDeporModel.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.text();
        
        if (result.includes('success')) {
            showNotification('Instalación deportiva creada exitosamente', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error al crear instalación deportiva: ' + result, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al crear instalación deportiva', 'error');
    }
}

// Actualizar instalación
async function actualizarInstalacion(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    formData.append('action', 'update');
    if (currentImageUrl) {
        formData.append('imagen_url', currentImageUrl);
    }
    
    try {
        const response = await fetch('../../Models/InsDeporModel.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.text();
        
        if (result.includes('success')) {
            showNotification('Instalación deportiva actualizada exitosamente', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error al actualizar instalación deportiva: ' + result, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al actualizar instalación deportiva', 'error');
    }
}

// Eliminar instalación
async function eliminarInstalacion(instalacionId) {
    if (confirm('¿Estás seguro de que deseas eliminar esta instalación deportiva? Esta acción eliminará también todas sus áreas deportivas asociadas.')) {
        try {
            const response = await fetch('../../Models/InsDeporModel.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete',
                    id: instalacionId
                })
            });
            
            const result = await response.text();
            
            if (result.includes('success')) {
                showNotification('Instalación deportiva eliminada exitosamente', 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification('Error al eliminar instalación deportiva: ' + result, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al eliminar instalación deportiva', 'error');
        }
    }
}

// Funciones auxiliares
function verAreas(instalacionId) {
    window.location.href = `areas_deportivas.php?instalacion=${instalacionId}`;
}

function gestionarHorarios(instalacionId) {
    showNotification('Función de horarios por implementar', 'info');
}

function resetForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
    }
    currentImageUrl = null;
    
    const previews = document.querySelectorAll('.image-preview');
    previews.forEach(preview => {
        preview.innerHTML = `
            <div class="upload-placeholder">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Arrastra una imagen aquí o haz clic para seleccionar</p>
                <small>Máximo 5MB - JPG, PNG, GIF</small>
            </div>
        `;
    });
}

// Mostrar notificaciones
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification-toast ${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${type === 'success' ? 'linear-gradient(135deg, #28a745, #20c997)' : 
                     type === 'error' ? 'linear-gradient(135deg, #dc3545, #e74c3c)' :
                     type === 'info' ? 'linear-gradient(135deg, #17a2b8, #20c997)' :
                     'linear-gradient(135deg, #ffc107, #fd7e14)'};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 1000;
        animation: slideInRight 0.3s ease;
        max-width: 300px;
        font-weight: 600;
    `;
    notification.innerHTML = `
        <i class="fas fa-${
            type === 'success' ? 'check-circle' : 
            type === 'error' ? 'exclamation-circle' :
            type === 'info' ? 'info-circle' : 'exclamation-triangle'
        }"></i>
        <span style="margin-left: 10px;">${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 4000);
}

// Estilos adicionales para Leaflet
const leafletStyles = document.createElement('style');
leafletStyles.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .custom-marker {
        background: transparent;
        border: none;
        text-align: center;
    }

    .leaflet-popup-content-wrapper {
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .leaflet-popup-content {
        font-family: 'Inter', sans-serif;
        color: #2c3e50;
    }
`;
document.head.appendChild(leafletStyles);