// dashboard_instituciones.js
const IMGBB_API_KEY = 'f94d58c09424ff225d85feee613de3a6'; // Tu API key actual

// Variables globales
let currentImageUrl = null;

// Event listeners al cargar el DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard Instituciones cargado - usando InsDeporController existente');
    
    // Inicializar componentes
    initQuickActions();
    initNotifications();
    initImageUpload();
});

// ✅ NUEVA FUNCIÓN: Inicializar upload de imágenes
function initImageUpload() {
    // Buscar todas las instalaciones con imágenes
    const instalacionCards = document.querySelectorAll('.instalacion-card-inst');
    
    instalacionCards.forEach(card => {
        const imagen = card.querySelector('.instalacion-imagen-inst img');
        const instalacionId = card.dataset.instalacionId;
        
        if (imagen && instalacionId) {
            // Agregar overlay para cambiar imagen
            const overlay = document.createElement('div');
            overlay.className = 'image-change-overlay';
            overlay.innerHTML = `
                <button class="btn-change-instalacion-image" onclick="cambiarImagenInstalacion(${instalacionId})">
                    <i class="fas fa-camera"></i>
                    <span>Cambiar Imagen</span>
                </button>
            `;
            
            const imagenContainer = imagen.parentElement;
            imagenContainer.appendChild(overlay);
            
            // Mostrar overlay al hacer hover
            imagenContainer.addEventListener('mouseenter', () => {
                overlay.style.display = 'flex';
            });
            
            imagenContainer.addEventListener('mouseleave', () => {
                overlay.style.display = 'none';
            });
        }
    });
}

// ✅ NUEVA FUNCIÓN: Cambiar imagen de instalación
function cambiarImagenInstalacion(instalacionId) {
    // Crear input file dinámicamente
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.style.display = 'none';
    
    input.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            subirImagenInstalacion(file, instalacionId);
        }
    });
    
    document.body.appendChild(input);
    input.click();
    document.body.removeChild(input);
}

// ✅ NUEVA FUNCIÓN: Subir imagen de instalación
async function subirImagenInstalacion(file, instalacionId) {
    // Validar API KEY
    if (IMGBB_API_KEY === 'TU_API_KEY_AQUI') {
        alert('ERROR: Debes configurar tu API KEY de ImgBB');
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
    
    // Mostrar indicador de carga
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'upload-loading-overlay';
    loadingOverlay.innerHTML = `
        <div class="upload-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Subiendo imagen...</p>
        </div>
    `;
    document.body.appendChild(loadingOverlay);
    
    try {
        // Crear FormData para ImgBB
        const formData = new FormData();
        formData.append('image', file);
        
        // Subir a ImgBB
        const response = await fetch(`https://api.imgbb.com/1/upload?key=${IMGBB_API_KEY}`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Actualizar imagen en la base de datos
            const updateResponse = await fetch('../../Models/InsDeporModel.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=updateInstalacionImage&instalacionId=${instalacionId}&imagenUrl=${encodeURIComponent(result.data.url)}`
            });
            
            const updateResult = await updateResponse.text();
            
            if (updateResult.includes('success')) {
                // Actualizar imagen en el DOM
                const instalacionCard = document.querySelector(`[data-instalacion-id="${instalacionId}"]`);
                if (instalacionCard) {
                    const imagen = instalacionCard.querySelector('.instalacion-imagen-inst img');
                    if (imagen) {
                        imagen.src = result.data.url;
                    }
                }
                
                showNotification('Imagen actualizada exitosamente', 'success');
            } else {
                throw new Error('Error al guardar imagen en la base de datos');
            }
            
        } else {
            throw new Error(result.error?.message || 'Error al subir imagen');
        }
        
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al actualizar imagen: ' + error.message, 'error');
    } finally {
        // Remover indicador de carga
        document.body.removeChild(loadingOverlay);
    }
}

// Funciones para las acciones del dashboard
function editarInstalacion(instalacionId) {
    console.log('Editando instalación:', instalacionId);
    // Redirigir a página de edición o abrir modal
    window.location.href = `instalaciones.php?edit=${instalacionId}`;
}

function gestionarHorarios(instalacionId) {
    console.log('Gestionando horarios de instalación:', instalacionId);
    // Redirigir a página de horarios o abrir modal
    window.location.href = `horarios.php?instalacion=${instalacionId}`;
}

function contactarCliente(telefono) {
    console.log('Contactando cliente:', telefono);
    // Abrir aplicación de teléfono o WhatsApp
    window.open(`tel:${telefono}`, '_self');
}

function nuevaInstalacion() {
    console.log('Creando nueva instalación');
    window.location.href = 'instalaciones.php?action=new';
}

function gestionarTodosLosHorarios() {
    console.log('Gestionando todos los horarios');
    window.location.href = 'horarios.php';
}

function bloquearHorario() {
    console.log('Bloqueando horario');
    // Abrir modal de bloqueo de horario
    alert('Función de bloqueo de horario - próximamente');
}

function crearTorneo() {
    console.log('Creando torneo');
    window.location.href = 'torneos.php?action=new';
}

function nuevaPromocion() {
    console.log('Creando promoción');
    window.location.href = 'promociones.php?action=new';
}

// Acciones rápidas del sidebar
function initQuickActions() {
    const quickActionBtns = document.querySelectorAll('.quick-action-btn-inst');
    
    quickActionBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.querySelector('span').textContent.trim();
            handleQuickAction(action);
        });
    });
}

function handleQuickAction(action) {
    switch(action) {
        case 'Nueva Instalación':
            nuevaInstalacion();
            break;
        case 'Bloquear Horario':
            bloquearHorario();
            break;
        case 'Crear Torneo':
            crearTorneo();
            break;
        case 'Nueva Promoción':
            nuevaPromocion();
            break;
        default:
            console.log('Acción no reconocida:', action);
    }
}

// Gestión de notificaciones
function initNotifications() {
    const notificationItems = document.querySelectorAll('.notification-item-inst');
    
    notificationItems.forEach(item => {
        item.addEventListener('click', function() {
            this.classList.remove('nueva');
            markAsRead(this);
        });
    });
}

function markAsRead(notificationElement) {
    console.log('Notificación marcada como leída');
    // Aquí iría la lógica para marcar como leída en el backend
}

// ✅ FUNCIÓN MEJORADA: Mostrar notificaciones
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification-toast ${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${type === 'success' ? 'linear-gradient(135deg, #28a745, #20c997)' : 'linear-gradient(135deg, #dc3545, #e74c3c)'};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 1000;
        animation: slideInRight 0.3s ease;
        max-width: 300px;
    `;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span style="margin-left: 10px;">${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 4000);
}

// ✅ ESTILOS ADICIONALES
const additionalStyles = document.createElement('style');
additionalStyles.textContent = `
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

    .image-change-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: none;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-change-instalacion-image {
        background: rgba(255, 255, 255, 0.9);
        color: #2c5aa0;
        border: none;
        padding: 12px 20px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn-change-instalacion-image:hover {
        background: white;
        transform: scale(1.05);
    }

    .upload-loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2000;
    }

    .upload-spinner {
        background: white;
        padding: 30px;
        border-radius: 12px;
        text-align: center;
        color: #2c5aa0;
    }

    .upload-spinner i {
        font-size: 2rem;
        margin-bottom: 15px;
    }

    .upload-spinner p {
        margin: 0;
        font-weight: 600;
    }
`;
document.head.appendChild(additionalStyles);