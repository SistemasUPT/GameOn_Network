const IMGBB_API_KEY = 'f94d58c09424ff225d85feee613de3a6'; // Tu API key actual

// Variables globales
let currentImageUrl = null;

// Event listeners al cargar el DOM
document.addEventListener('DOMContentLoaded', function() {
    inicializarEventos();
    setupImageUpload('imagenArea', 'imagePreview', 'uploadProgress');
    setupImageUpload('editImagenArea', 'editImagePreview', 'editUploadProgress');
});

// Inicializar eventos
function inicializarEventos() {
    const nuevaAreaBtn = document.getElementById('nuevaArea');
    const cancelarNuevaBtn = document.getElementById('cancelarNuevaArea');
    const cancelarEditarBtn = document.getElementById('cancelarEditarArea');
    const formNueva = document.getElementById('formNuevaArea');
    const formEditar = document.getElementById('formEditarArea');
    
    if (nuevaAreaBtn) nuevaAreaBtn.addEventListener('click', mostrarFormularioNueva);
    if (cancelarNuevaBtn) cancelarNuevaBtn.addEventListener('click', cancelarFormularioNueva);
    if (cancelarEditarBtn) cancelarEditarBtn.addEventListener('click', cancelarFormularioEditar);
    if (formNueva) formNueva.addEventListener('submit', crearNuevaArea);
    if (formEditar) formEditar.addEventListener('submit', actualizarArea);
}

// Mostrar formulario nueva área
function mostrarFormularioNueva() {
    document.getElementById('formularioNuevaArea').classList.add('active');
}

// Cancelar formulario nueva área
function cancelarFormularioNueva() {
    document.getElementById('formularioNuevaArea').classList.remove('active');
    resetForm('formNuevaArea');
}

// Cancelar formulario editar área
function cancelarFormularioEditar() {
    document.getElementById('formularioEditarArea').classList.remove('active');
    resetForm('formEditarArea');
}

// Filtrar por instalación
function filtrarPorInstalacion() {
    const select = document.getElementById('filtroInstalacion');
    const instalacionId = select.value;
    
    if (instalacionId) {
        window.location.href = 'areas_deportivas.php?instalacion=' + instalacionId;
    } else {
        window.location.href = 'areas_deportivas.php';
    }
}

// Configurar upload de imágenes
function setupImageUpload(fileInputId, previewId, progressId) {
    const fileInput = document.getElementById(fileInputId);
    const preview = document.getElementById(previewId);
    const progress = document.getElementById(progressId);
    
    if (!fileInput || !preview || !progress) return;
    
    // Click en preview para abrir selector
    preview.addEventListener('click', () => fileInput.click());
    
    // Drag and drop
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
    
    // Cambio en input file
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleImageUpload(e.target.files[0], previewId, progressId);
        }
    });
}

// Manejar subida de imagen
async function handleImageUpload(file, previewId, progressId) {
    // Validar API KEY
    if (IMGBB_API_KEY === 'TU_API_KEY_AQUI') {
        alert('ERROR: Debes configurar tu API KEY de ImgBB en el archivo JS');
        return;
    }
    
    // Validar archivo
    if (!file.type.startsWith('image/')) {
        alert('Por favor selecciona una imagen válida');
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) { // 5MB
        alert('La imagen es muy pesada. Máximo 5MB');
        return;
    }
    
    const preview = document.getElementById(previewId);
    const progress = document.getElementById(progressId);
    const progressFill = progress.querySelector('.progress-fill');
    const progressText = progress.querySelector('.progress-text');
    
    // Mostrar progreso
    progress.style.display = 'block';
    progressFill.style.width = '0%';
    progressText.textContent = '0%';
    
    try {
        // Crear FormData para ImgBB
        const formData = new FormData();
        formData.append('image', file);
        
        // Simular progreso inicial
        progressFill.style.width = '30%';
        progressText.textContent = '30%';
        
        // Subir a ImgBB
        const response = await fetch(`https://api.imgbb.com/1/upload?key=${IMGBB_API_KEY}`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Imagen subida exitosamente
            currentImageUrl = result.data.url;
            
            // Completar progreso
            progressFill.style.width = '100%';
            progressText.textContent = '100%';
            
            // Mostrar preview
            const fileInputId = previewId.replace('Preview', 'Area');
            preview.innerHTML = `
                <img src="${result.data.url}" alt="Preview" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                <div class="image-overlay">
                    <button type="button" class="btn-change-image" onclick="document.getElementById('${fileInputId}').click()">
                        <i class="fas fa-edit"></i> Cambiar
                    </button>
                </div>
            `;
            
            // Ocultar progreso después de un tiempo
            setTimeout(() => {
                progress.style.display = 'none';
            }, 1000);
            
        } else {
            throw new Error(result.error?.message || 'Error al subir imagen');
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Error al subir la imagen: ' + error.message);
        progress.style.display = 'none';
    }
}

// Crear nueva área
async function crearNuevaArea(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    if (currentImageUrl) {
        formData.append('imagen_url', currentImageUrl);
    }
    formData.append('action', 'create');
    
    try {
        const response = await fetch('../../Models/AreasDeportivasModel.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.text();
        
        if (result.includes('success')) {
            alert('Área deportiva creada exitosamente');
            location.reload();
        } else {
            alert('Error al crear área deportiva: ' + result);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al crear área deportiva');
    }
}

// ✅ FUNCIÓN CORREGIDA: Editar área con datos reales
async function editarArea(areaId) {
    try {
        // Resetear currentImageUrl
        currentImageUrl = null;
        
        // Obtener datos reales del área desde la base de datos
        const response = await fetch(`../../Models/AreasDeportivasModel.php?action=get&id=${areaId}`);
        const result = await response.json();
        
        if (!result.success) {
            alert('Error: ' + result.message);
            return;
        }
        
        const area = result.data;
        console.log('Datos del área:', area); // Para debug
        
        // Llenar formulario de edición con datos reales
        document.getElementById('editAreaId').value = area.id;
        document.getElementById('editInstalacionArea').value = area.institucion_deportiva_id;
        document.getElementById('editDeporteArea').value = area.deporte_id;
        document.getElementById('editNombreArea').value = area.nombre_area;
        document.getElementById('editCapacidad').value = area.capacidad_jugadores || '';
        document.getElementById('editTarifaHora').value = area.tarifa_por_hora;
        document.getElementById('editEstadoArea').value = area.estado;
        document.getElementById('editDescripcionArea').value = area.descripcion || '';
        
        // Mostrar imagen actual si existe
        const editImagePreview = document.getElementById('editImagePreview');
        if (area.imagen_area && area.imagen_area.trim() !== '') {
            currentImageUrl = area.imagen_area;
            editImagePreview.innerHTML = `
                <img src="${area.imagen_area}" alt="Imagen actual" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                <div class="image-overlay">
                    <button type="button" class="btn-change-image" onclick="document.getElementById('editImagenArea').click()">
                        <i class="fas fa-edit"></i> Cambiar
                    </button>
                </div>
            `;
        } else {
            // No hay imagen, mostrar placeholder
            editImagePreview.innerHTML = `
                <div class="upload-placeholder">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Arrastra una imagen aquí o haz clic para seleccionar</p>
                    <small>Máximo 5MB - JPG, PNG, GIF</small>
                </div>
            `;
        }
        
        // Mostrar formulario
        document.getElementById('formularioEditarArea').classList.add('active');
        
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar datos del área: ' + error.message);
    }
}

// Actualizar área
async function actualizarArea(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    formData.append('action', 'update');
    if (currentImageUrl) {
        formData.append('imagen_url', currentImageUrl);
    }
    
    try {
        const response = await fetch('../../Models/AreasDeportivasModel.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.text();
        
        if (result.includes('success')) {
            alert('Área deportiva actualizada exitosamente');
            location.reload();
        } else {
            alert('Error al actualizar área deportiva: ' + result);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al actualizar área deportiva');
    }
}

// Eliminar área
async function eliminarArea(areaId) {
    if (confirm('¿Estás seguro de que deseas eliminar esta área deportiva?')) {
        try {
            const response = await fetch('../../Models/AreasDeportivasModel.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete',
                    id: areaId
                })
            });
            
            const result = await response.text();
            
            if (result.includes('success')) {
                alert('Área deportiva eliminada exitosamente');
                location.reload();
            } else {
                alert('Error al eliminar área deportiva: ' + result);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al eliminar área deportiva');
        }
    }
}

// ✅ FUNCIONES DE HORARIOS
// Gestionar horarios de área
async function gestionarHorariosArea(areaId, nombreArea) {
    try {
        // Mostrar modal
        document.getElementById('modalAreaId').value = areaId;
        document.getElementById('modalAreaNombre').textContent = nombreArea;
        
        // Obtener horarios actuales del área
        const response = await fetch(`../../Models/AreasDeportivasModel.php?action=getHorarios&id=${areaId}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            // Llenar formulario con horarios existentes
            result.data.forEach(horario => {
                const disponibleCheckbox = document.getElementById(`disponible_${horario.dia}`);
                const aperturaInput = document.getElementById(`apertura_${horario.dia}`);
                const cierreInput = document.getElementById(`cierre_${horario.dia}`);
                
                if (disponibleCheckbox && aperturaInput && cierreInput) {
                    disponibleCheckbox.checked = horario.disponible == 1;
                    aperturaInput.value = horario.hora_apertura;
                    cierreInput.value = horario.hora_cierre;
                    aperturaInput.disabled = horario.disponible != 1;
                    cierreInput.disabled = horario.disponible != 1;
                    
                    // Agregar/quitar clase disabled al contenedor
                    const horarioItem = disponibleCheckbox.closest('.horario-item');
                    if (horario.disponible != 1) {
                        horarioItem.classList.add('disabled');
                    } else {
                        horarioItem.classList.remove('disabled');
                    }
                }
            });
        }
        
        // Mostrar modal
        document.getElementById('modalHorarios').classList.add('active');
        document.body.style.overflow = 'hidden';
        
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar horarios del área');
    }
}

// Cerrar modal de horarios
function cerrarModalHorarios() {
    document.getElementById('modalHorarios').classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Guardar horarios
async function guardarHorarios() {
    const areaId = document.getElementById('modalAreaId').value;
    const diasSemana = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];
    
    const horarios = [];
    
    diasSemana.forEach(dia => {
        const disponible = document.getElementById(`disponible_${dia}`).checked;
        const apertura = document.getElementById(`apertura_${dia}`).value;
        const cierre = document.getElementById(`cierre_${dia}`).value;
        
        horarios.push({
            dia: dia,
            disponible: disponible ? 1 : 0,
            hora_apertura: disponible ? apertura : '00:00',
            hora_cierre: disponible ? cierre : '00:00'
        });
    });
    
    try {
        const response = await fetch('../../Models/AreasDeportivasModel.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'updateHorarios',
                areaId: areaId,
                horarios: horarios
            })
        });
        
        const result = await response.text();
        
        if (result.includes('success')) {
            alert('Horarios actualizados exitosamente');
            cerrarModalHorarios();
            location.reload();
        } else {
            alert('Error al actualizar horarios: ' + result);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al guardar horarios');
    }
}

// Event listeners para checkboxes de disponibilidad
document.addEventListener('DOMContentLoaded', function() {
    // Agregar event listeners para checkboxes de horarios
    const diasSemana = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];
    
    diasSemana.forEach(dia => {
        const checkbox = document.getElementById(`disponible_${dia}`);
        if (checkbox) {
            checkbox.addEventListener('change', function() {
                const aperturaInput = document.getElementById(`apertura_${dia}`);
                const cierreInput = document.getElementById(`cierre_${dia}`);
                const horarioItem = this.closest('.horario-item');
                
                if (this.checked) {
                    aperturaInput.disabled = false;
                    cierreInput.disabled = false;
                    horarioItem.classList.remove('disabled');
                } else {
                    aperturaInput.disabled = true;
                    cierreInput.disabled = true;
                    horarioItem.classList.add('disabled');
                }
            });
        }
    });
    
    // Cerrar modal al hacer clic fuera
    document.getElementById('modalHorarios').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModalHorarios();
        }
    });
});

// Resetear formulario
function resetForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
    }
    currentImageUrl = null;
    
    // Resetear previews de imagen
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