// Variables globales
let torneosData = [];
let vistaActual = 'grid';
let filtroEstado = '';
let filtroDeporte = '';

// Inicializar al cargar DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Sistema de gestión de torneos inicializado');
    inicializarEventos();
    cargarTorneosReales(); // ✅ CAMBIO: Cargar datos reales
});

// ✅ NUEVA FUNCIÓN: Cargar torneos reales desde la base de datos
async function cargarTorneosReales() {
    try {
        showNotification('Cargando torneos...', 'info');
        
        const response = await fetch('../../Controllers/TorneosController.php?action=obtener_torneos');
        const result = await response.json();
        
        if (result.success) {
            torneosData = result.torneos;
            actualizarEstadisticas();
            mostrarTorneos(torneosData);
            
            if (torneosData.length === 0) {
                showNotification('No tienes torneos creados aún', 'info');
            } else {
                showNotification(`${torneosData.length} torneos cargados exitosamente`, 'success');
            }
        } else {
            console.error('Error cargando torneos:', result.message);
            showNotification('Error al cargar torneos: ' + result.message, 'error');
            // Fallback a datos ficticios en caso de error
            cargarTorneosFicticios();
        }
    } catch (error) {
        console.error('Error de conexión:', error);
        showNotification('Error de conexión al cargar torneos', 'error');
        // Fallback a datos ficticios
        cargarTorneosFicticios();
    }
}

// ✅ NUEVA FUNCIÓN: Guardar torneo
async function guardarTorneo() {
    const form = document.getElementById('formTorneo');
    const formData = new FormData(form);
    
    // Validar que los premios estén completos
    const premiosTexto = formData.get('premio_descripcion');
    if (!validarPremios(premiosTexto)) {
        showNotification('Por favor, complete la descripción de todos los premios (1er, 2do y 3er puesto)', 'error');
        document.getElementById('premioDescripcion').focus();
        return;
    }
    
    // Validar datos del formulario
    const requiredFields = ['nombre', 'deporte_id', 'institucion_sede_id', 'fecha_inicio', 'fecha_inscripcion_inicio', 'fecha_inscripcion_fin'];
    for (let field of requiredFields) {
        if (!formData.get(field)) {
            showNotification(`El campo ${field} es requerido`, 'error');
            return;
        }
    }
    
    // Obtener URL de imagen subida
    const imagenURL = document.getElementById('imagenTorneoURL').value;
    
    // Crear objeto con datos del torneo
    const torneoData = {
        nombre: formData.get('nombre'),
        descripcion: formData.get('descripcion'),
        deporte_id: parseInt(formData.get('deporte_id')),
        institucion_sede_id: parseInt(formData.get('institucion_sede_id')),
        max_equipos: parseInt(formData.get('max_equipos')),
        fecha_inicio: formData.get('fecha_inicio'),
        fecha_fin: formData.get('fecha_fin'),
        fecha_inscripcion_inicio: formData.get('fecha_inscripcion_inicio'),
        fecha_inscripcion_fin: formData.get('fecha_inscripcion_fin'),
        modalidad: formData.get('modalidad'),
        premio_descripcion: premiosTexto,
        costo_inscripcion: parseFloat(formData.get('costo_inscripcion')) || 0,
        imagen_torneo: imagenURL || null,
        areas_deportivas: window.areasSeleccionadasTorneo || {} // ✅ NUEVO: Incluir áreas seleccionadas
    };
    
    try {
        showNotification('Creando torneo...', 'info');
        
        const response = await fetch('../../Controllers/TorneosController.php?action=crear_torneo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(torneoData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Torneo creado exitosamente', 'success');
            cerrarModal();
            resetearFormulario();
            
            // Recargar torneos
            cargarTorneosReales();
        } else {
            showNotification('Error al crear torneo: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error de conexión al crear torneo', 'error');
    }
}

// ✅ FUNCIÓN MEJORADA: Ver detalles con datos reales
async function verDetalles(torneoId) {
    try {
        const response = await fetch(`../../Controllers/TorneosController.php?action=obtener_detalles&torneo_id=${torneoId}`);
        const result = await response.json();
        
        if (result.success) {
            const torneo = result.torneo;
            const equipos = result.equipos_inscritos;
            
            const modalContent = document.getElementById('detallesContent');
            modalContent.innerHTML = `
                <div class="torneo-detalles">
                    <div class="detalle-header">
                        <h3>${torneo.nombre}</h3>
                        <span class="badge estado-${torneo.estado.replace(/_/g, '-')}">${formatearEstado(torneo.estado)}</span>
                    </div>
                    
                    <div class="detalle-info">
                        <div class="info-grid">
                            <div class="info-item">
                                <strong>Deporte:</strong> ${torneo.deporte_nombre}
                            </div>
                            <div class="info-item">
                                <strong>Sede:</strong> ${torneo.sede_nombre}
                            </div>
                            <div class="info-item">
                                <strong>Modalidad:</strong> ${formatearModalidad(torneo.modalidad)}
                            </div>
                            <div class="info-item">
                                <strong>Equipos:</strong> ${torneo.equipos_inscritos}/${torneo.max_equipos}
                            </div>
                            <div class="info-item">
                                <strong>Inicio:</strong> ${formatearFecha(torneo.fecha_inicio)}
                            </div>
                            <div class="info-item">
                                <strong>Inscripciones:</strong> ${formatearFecha(torneo.fecha_inscripcion_fin)}
                            </div>
                        </div>
                        
                        <div class="descripcion">
                            <strong>Descripción:</strong>
                            <p>${torneo.descripcion}</p>
                        </div>
                        
                        <div class="premios">
                            <strong>Premios:</strong>
                            <p style="white-space: pre-line;">${torneo.premio_descripcion}</p>
                        </div>
                        
                        ${equipos.length > 0 ? `
                            <div class="equipos-inscritos">
                                <strong>Equipos Inscritos:</strong>
                                <div class="equipos-lista">
                                    ${equipos.map(equipo => `
                                        <div class="equipo-item">
                                            <span class="equipo-nombre">${equipo.equipo_nombre}</span>
                                            <span class="equipo-lider">${equipo.lider_nombre} ${equipo.lider_apellidos}</span>
                                            <span class="equipo-miembros">${equipo.total_miembros} miembros</span>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        ` : '<p>No hay equipos inscritos aún.</p>'}
                    </div>
                </div>
            `;
            
            document.getElementById('modalDetalles').style.display = 'flex';
        } else {
            showNotification('Error al cargar detalles: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error de conexión al cargar detalles', 'error');
    }
}
// Inicializar eventos
function inicializarEventos() {
    // Event listeners para filtros
    const filtroEstadoSelect = document.getElementById('filtroEstado');
    const filtroDeporteSelect = document.getElementById('filtroDeporte');
    
    if (filtroEstadoSelect) {
        filtroEstadoSelect.addEventListener('change', function() {
            filtroEstado = this.value;
            aplicarFiltros();
        });
    }
    
    if (filtroDeporteSelect) {
        filtroDeporteSelect.addEventListener('change', function() {
            filtroDeporte = this.value;
            aplicarFiltros();
        });
    }
    
    // Event listeners para vista
    const btnsVista = document.querySelectorAll('.btn-vista');
    btnsVista.forEach(btn => {
        btn.addEventListener('click', function() {
            const vista = this.dataset.vista;
            cambiarVista(vista);
        });
    });
    
    // Event listeners para modales
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            cerrarModal();
        }
        
        if (e.target.closest('.btn-close')) {
            cerrarModal();
        }
    });
}

// ✅ FUNCIÓN: Mostrar torneos en la vista actual
function mostrarTorneos(torneos) {
    if (vistaActual === 'grid') {
        mostrarTorneosGrid(torneos);
    } else {
        mostrarTorneosLista(torneos);
    }
}

// ✅ FUNCIÓN: Mostrar torneos en vista grid
function mostrarTorneosGrid(torneos) {
    const container = document.getElementById('torneosGrid');
    const listaContainer = document.getElementById('torneosLista');
    
    if (!container) return;
    
    container.style.display = 'grid';
    if (listaContainer) listaContainer.style.display = 'none';
    
    if (torneos.length === 0) {
        container.innerHTML = `
            <div class="empty-torneos" style="grid-column: 1/-1;">
                <i class="fas fa-trophy"></i>
                <h3>No hay torneos disponibles</h3>
                <p>Aún no se han creado torneos. ¡Crea el primer torneo!</p>
                <button class="btn-primary-torneos" onclick="abrirModalCrearTorneo()">
                    <i class="fas fa-plus"></i> Crear Primer Torneo
                </button>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    torneos.forEach((torneo, index) => {
        const iconoDeporte = obtenerIconoDeporte(torneo.deporte_id);
        const estadoClass = `estado-${torneo.estado.replace(/_/g, '-')}`;
        const estadoTexto = formatearEstado(torneo.estado);
        const progreso = calcularProgreso(torneo);
        
        html += `
            <div class="torneo-card" style="animation-delay: ${index * 0.1}s">
                <div class="torneo-imagen">
                    ${torneo.imagen_torneo ? 
                        `<img src="${torneo.imagen_torneo}" alt="${torneo.nombre}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                         <div class="icono-deporte" style="display: none;"><i class="${iconoDeporte}"></i></div>` :
                        `<div class="icono-deporte"><i class="${iconoDeporte}"></i></div>`
                    }
                    <div class="torneo-estado ${estadoClass}">${estadoTexto}</div>
                </div>
                
                <div class="torneo-content">
                    <div class="torneo-header">
                        <h3 class="torneo-titulo">${torneo.nombre}</h3>
                        <div class="torneo-deporte">
                            <i class="${iconoDeporte}"></i>
                            <span>${obtenerNombreDeporte(torneo.deporte_id)}</span>
                        </div>
                    </div>
                    
                    <div class="torneo-info">
                        <div class="info-item">
                            <span class="info-label">Inicio</span>
                            <span class="info-value">${formatearFecha(torneo.fecha_inicio)}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Modalidad</span>
                            <span class="info-value">${formatearModalidad(torneo.modalidad)}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Inscripción</span>
                            <span class="info-value">S/. ${parseFloat(torneo.costo_inscripcion || 0).toFixed(2)}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Organizador</span>
                            <span class="info-value">${torneo.organizador_tipo === 'ipd' ? 'IPD' : 'Privado'}</span>
                        </div>
                    </div>
                    
                    <div class="torneo-sede">
                        <div class="sede-nombre">
                            <i class="fas fa-map-marker-alt"></i>
                            ${torneo.sede_nombre}
                        </div>
                        <div class="sede-info">Sede del torneo</div>
                    </div>
                    
                    <div class="torneo-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${progreso.porcentaje}%"></div>
                        </div>
                        <div class="progress-text">
                            <span>Equipos: ${torneo.equipos_inscritos}/${torneo.max_equipos}</span>
                            <span>${progreso.porcentaje}%</span>
                        </div>
                    </div>
                    
                    <div class="torneo-actions">
                        <button class="btn-action btn-ver" onclick="verDetalles(${torneo.id})">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                        <button class="btn-action btn-editar" onclick="editarTorneo(${torneo.id})">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn-action btn-gestionar" onclick="gestionarTorneo(${torneo.id})">
                            <i class="fas fa-cogs"></i> Gestionar
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// ✅ FUNCIÓN: Mostrar torneos en vista lista
function mostrarTorneosLista(torneos) {
    const container = document.getElementById('torneosLista');
    const gridContainer = document.getElementById('torneosGrid');
    
    if (!container) return;
    
    container.style.display = 'block';
    if (gridContainer) gridContainer.style.display = 'none';
    
    if (torneos.length === 0) {
        container.innerHTML = `
            <div class="empty-torneos">
                <i class="fas fa-trophy"></i>
                <h3>No hay torneos disponibles</h3>
                <p>Aún no se han creado torneos. ¡Crea el primer torneo!</p>
                <button class="btn-primary-torneos" onclick="abrirModalCrearTorneo()">
                    <i class="fas fa-plus"></i> Crear Primer Torneo
                </button>
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="lista-header">
            <div>Torneo</div>
            <div>Deporte</div>
            <div>Fechas</div>
            <div>Equipos</div>
            <div>Estado</div>
            <div>Acciones</div>
        </div>
    `;
    
    torneos.forEach(torneo => {
        const iconoDeporte = obtenerIconoDeporte(torneo.deporte_id);
        const estadoClass = `estado-${torneo.estado.replace(/_/g, '-')}`;
        const estadoTexto = formatearEstado(torneo.estado);
        
        html += `
            <div class="torneo-item">
                <div class="item-torneo">
                    <div class="item-titulo">${torneo.nombre}</div>
                    <div class="item-sede">
                        <i class="fas fa-map-marker-alt"></i>
                        ${torneo.sede_nombre}
                    </div>
                </div>
                
                <div class="item-deporte">
                    <i class="${iconoDeporte}"></i>
                    <span>${obtenerNombreDeporte(torneo.deporte_id)}</span>
                </div>
                
                <div class="item-fechas">
                    <div><strong>Inicio:</strong> ${formatearFecha(torneo.fecha_inicio)}</div>
                    <div><strong>Inscr:</strong> ${formatearFecha(torneo.fecha_inscripcion_fin)}</div>
                </div>
                
                <div class="item-equipos">
                    <div class="equipos-numero">${torneo.equipos_inscritos}</div>
                    <div class="equipos-max">de ${torneo.max_equipos}</div>
                </div>
                
                <div class="item-estado">
                    <span class="badge-estado ${estadoClass}">${estadoTexto}</span>
                </div>
                
                <div class="item-acciones">
                    <button class="btn-mini btn-ver" onclick="verDetalles(${torneo.id})" title="Ver detalles">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-mini btn-editar" onclick="editarTorneo(${torneo.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-mini btn-gestionar" onclick="gestionarTorneo(${torneo.id})" title="Gestionar">
                        <i class="fas fa-cogs"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// ✅ FUNCIÓN: Actualizar estadísticas
function actualizarEstadisticas() {
    const stats = {
        total: torneosData.length,
        activos: torneosData.filter(t => t.estado === 'activo').length,
        proximos: torneosData.filter(t => t.estado === 'inscripciones_abiertas' || t.estado === 'proximo').length,
        finalizados: torneosData.filter(t => t.estado === 'finalizado').length
    };
    
    // Actualizar elementos del DOM
    const elements = {
        total: document.getElementById('totalTorneos'),
        activos: document.getElementById('torneosActivos'),
        proximos: document.getElementById('torneosProximos'),
        finalizados: document.getElementById('torneosFinalizados')
    };
    
    if (elements.total) elements.total.textContent = stats.total;
    if (elements.activos) elements.activos.textContent = stats.activos;
    if (elements.proximos) elements.proximos.textContent = stats.proximos;
    if (elements.finalizados) elements.finalizados.textContent = stats.finalizados;
}

// ✅ FUNCIONES DE UTILIDAD
function obtenerIconoDeporte(deporteId) {
    const iconos = {
        1: 'fas fa-futbol',
        2: 'fas fa-volleyball-ball', 
        3: 'fas fa-basketball-ball'
    };
    return iconos[deporteId] || 'fas fa-trophy';
}

function obtenerNombreDeporte(deporteId) {
    const nombres = {
        1: 'Fútbol',
        2: 'Vóley',
        3: 'Básquet'
    };
    return nombres[deporteId] || 'Deporte';
}

function formatearEstado(estado) {
    const estados = {
        'proximo': 'Próximo',
        'inscripciones_abiertas': 'Inscripciones Abiertas',
        'inscripciones_cerradas': 'Inscripciones Cerradas',
        'activo': 'En Curso',
        'finalizado': 'Finalizado',
        'cancelado': 'Cancelado'
    };
    return estados[estado] || estado;
}

function formatearModalidad(modalidad) {
    const modalidades = {
        'eliminacion_simple': 'Eliminación Simple',
        'eliminacion_doble': 'Eliminación Doble',
        'todos_contra_todos': 'Todos vs Todos',
        'grupos_eliminatoria': 'Grupos + Eliminatoria'
    };
    return modalidades[modalidad] || modalidad;
}

function formatearFecha(fecha) {
    if (!fecha) return 'No definida';
    return new Date(fecha).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

function calcularProgreso(torneo) {
    const porcentaje = Math.round((torneo.equipos_inscritos / torneo.max_equipos) * 100);
    return { porcentaje };
}

// ✅ FUNCIONES DE INTERACCIÓN
function cambiarVista(vista) {
    vistaActual = vista;
    
    // Actualizar botones
    document.querySelectorAll('.btn-vista').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-vista="${vista}"]`).classList.add('active');
    
    // Mostrar vista correspondiente
    mostrarTorneos(torneosData);
}

function aplicarFiltros() {
    let torneosFiltrados = torneosData;
    
    if (filtroEstado) {
        torneosFiltrados = torneosFiltrados.filter(t => t.estado === filtroEstado);
    }
    
    if (filtroDeporte) {
        torneosFiltrados = torneosFiltrados.filter(t => t.deporte_id == filtroDeporte);
    }
    
    mostrarTorneos(torneosFiltrados);
}

function verDetalles(torneoId) {
    const torneo = torneosData.find(t => t.id === torneoId);
    if (!torneo) return;
    
    console.log('Ver detalles del torneo:', torneo);
    showNotification(`Viendo detalles de: ${torneo.nombre}`, 'info');
}

function editarTorneo(torneoId) {
    const torneo = torneosData.find(t => t.id === torneoId);
    if (!torneo) return;
    
    console.log('Editar torneo:', torneo);
    showNotification(`Editando: ${torneo.nombre}`, 'info');
}

function gestionarTorneo(torneoId) {
    const torneo = torneosData.find(t => t.id === torneoId);
    if (!torneo) return;
    
    console.log('Gestionar torneo:', torneo);
    showNotification(`Gestionando: ${torneo.nombre}`, 'info');
}

function abrirModalCrearTorneo() {
    const modal = document.getElementById('modalTorneo');
    if (modal) {
        modal.style.display = 'flex';
    }
    showNotification('Modal de creación abierto', 'info');
}

function cerrarModal() {
    const modales = document.querySelectorAll('.modal-overlay');
    modales.forEach(modal => {
        modal.style.display = 'none';
    });
    
    // Resetear campo de premios al cerrar
    const premiosField = document.getElementById('premioDescripcion');
    if (premiosField) {
        premiosField.value = '🥇 1er puesto: \n🥈 2do puesto: \n🥉 3er puesto: ';
        premiosField.readOnly = true;
        premiosField.style.background = '#f8f9fa';
        premiosField.style.border = '2px solid #e9ecef';
    }
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification-toast ${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${type === 'success' ? 'linear-gradient(135deg, #28a745, #20c997)' : 
                     type === 'error' ? 'linear-gradient(135deg, #dc3545, #e74c3c)' :
                     type === 'info' ? 'linear-gradient(135deg, #17a2b8, #6f42c1)' :
                     'linear-gradient(135deg, #17a2b8, #20c997)'};
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
        <i class="fas fa-${type === 'success' ? 'check-circle' : 
                          type === 'error' ? 'exclamation-circle' : 
                          type === 'info' ? 'info-circle' : 'bell'}"></i>
        <span style="margin-left: 10px;">${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 4000);
}

// Agregar estilos de animación
const styles = document.createElement('style');
styles.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
`;
document.head.appendChild(styles);

// ✅ CONFIGURACIÓN DE IMGBB API
const IMGBB_API_KEY = 'f94d58c09424ff225d85feee613de3a6'; // Reemplaza con tu API key
const IMGBB_API_URL = 'https://api.imgbb.com/1/upload';

// ✅ NUEVA FUNCIÓN: Subir imagen a ImgBB
async function subirImagen(input) {
    const file = input.files[0];
    if (!file) return;
    
    // Validar archivo
    if (!file.type.startsWith('image/')) {
        showNotification('Por favor selecciona un archivo de imagen válido', 'error');
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) { // 5MB máximo
        showNotification('La imagen es muy grande. Máximo 5MB', 'error');
        return;
    }
    
    const uploadStatus = document.getElementById('uploadStatus');
    const progressBar = document.getElementById('progressBar');
    const uploadText = document.getElementById('uploadText');
    const preview = document.getElementById('previewImagen');
    
    // Mostrar progreso
    uploadStatus.style.display = 'block';
    uploadText.textContent = 'Subiendo imagen...';
    progressBar.style.width = '0%';
    
    try {
        // Crear FormData
        const formData = new FormData();
        formData.append('image', file);
        formData.append('key', IMGBB_API_KEY);
        
        // Simular progreso
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 30;
            if (progress > 90) progress = 90;
            progressBar.style.width = progress + '%';
        }, 200);
        
        // Subir imagen
        const response = await fetch(IMGBB_API_URL, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        clearInterval(progressInterval);
        progressBar.style.width = '100%';
        
        if (result.success) {
            // Imagen subida exitosamente
            const imageUrl = result.data.url;
            document.getElementById('imagenTorneoURL').value = imageUrl;
            
            // Mostrar preview
            preview.innerHTML = `<img src="${imageUrl}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">`;
            
            uploadText.textContent = '¡Imagen subida exitosamente!';
            uploadText.style.color = '#28a745';
            
            showNotification('Imagen subida correctamente', 'success');
            
            // Ocultar progreso después de 2 segundos
            setTimeout(() => {
                uploadStatus.style.display = 'none';
            }, 2000);
            
        } else {
            throw new Error(result.error?.message || 'Error al subir imagen');
        }
        
    } catch (error) {
        console.error('Error subiendo imagen:', error);
        
        // Resetear estado
        progressBar.style.width = '0%';
        uploadText.textContent = 'Error al subir imagen';
        uploadText.style.color = '#dc3545';
        document.getElementById('imagenTorneoURL').value = '';
        preview.innerHTML = '<i class="fas fa-image" style="color: #ccc; font-size: 24px;"></i>';
        
        showNotification('Error al subir imagen: ' + error.message, 'error');
        
        setTimeout(() => {
            uploadStatus.style.display = 'none';
        }, 3000);
    }
}

// ✅ FUNCIÓN CORREGIDA: Verificar disponibilidad de áreas deportivas con mejor manejo de errores
async function verificarAreasDisponibles(fase, fecha, equipos) {
    try {
        const sedeId = document.getElementById('sedeTorneo').value;
        const deporteId = document.getElementById('deporteTorneo').value;
        
        if (!sedeId || !deporteId) {
            showNotification('Selecciona sede y deporte primero', 'warning');
            return;
        }
        
        // Actualizar botón a estado de carga
        const btnVerificar = document.querySelector(`[onclick*="verificarAreasDisponibles('${fase}'"]`);
        if (btnVerificar) {
            btnVerificar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
            btnVerificar.style.background = '#6c757d';
            btnVerificar.disabled = true;
        }
        
        const partidosNecesarios = Math.floor(equipos / 2);
        
        console.log('Verificando disponibilidad:', {
            fase, fecha, equipos, partidosNecesarios, sedeId, deporteId
        });
        
        // ✅ CORRECCIÓN: Enviar datos como JSON
        const requestData = {
            action: 'verificar_y_reservar_automatico',
            sede_id: parseInt(sedeId),
            deporte_id: parseInt(deporteId),
            fecha: fecha,
            partidos_necesarios: partidosNecesarios,
            fase: fase,
            horario_torneo: document.getElementById('horarioTorneo').value || 'mananas'
        };
        
        console.log('Enviando datos:', requestData);
        
        const response = await fetch(`../../Models/AreasDeportivasModel.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        // ✅ MEJORADO: Verificar respuesta antes de parsear JSON
        const responseText = await response.text();
        console.log('Respuesta cruda del servidor:', responseText);
        
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Error parseando JSON:', parseError);
            console.error('Respuesta no es JSON:', responseText);
            
            // Restaurar botón
            if (btnVerificar) {
                btnVerificar.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error servidor';
                btnVerificar.style.background = '#dc3545';
                btnVerificar.disabled = false;
            }
            
            showNotification('Error en el servidor. Revisa la consola para más detalles.', 'error');
            return;
        }
        
        console.log('Respuesta parseada:', result);
        
        if (result.success) {
            if (result.reservas_realizadas > 0) {
                // ✅ ÉXITO: Áreas reservadas automáticamente
                btnVerificar.innerHTML = `<i class="fas fa-check-circle"></i> ${result.reservas_realizadas} área(s) reservada(s)`;
                btnVerificar.style.background = '#28a745';
                btnVerificar.style.color = 'white';
                btnVerificar.disabled = false;
                
                // Guardar información de reservas
                if (!window.areasReservadasTorneo) {
                    window.areasReservadasTorneo = {};
                }
                window.areasReservadasTorneo[fase] = result.reservas_detalle;
                
                showNotification(`✅ ${result.reservas_realizadas} área(s) reservada(s) automáticamente para ${fase}`, 'success');
                
                // Mostrar detalles de las reservas
                setTimeout(() => {
                    mostrarDetallesReservas(fase, result.reservas_detalle);
                }, 1000);
                
            } else {
                // ❌ NO HAY DISPONIBILIDAD
                btnVerificar.innerHTML = '<i class="fas fa-times-circle"></i> Sin disponibilidad';
                btnVerificar.style.background = '#dc3545';
                btnVerificar.style.color = 'white';
                btnVerificar.disabled = false;
                
                const mensaje = result.message || 'No hay horarios disponibles';
                showNotification(`❌ ${mensaje}`, 'error');
                
                // Mostrar información de debug
                console.log('Debug info:', {
                    areas_encontradas: result.areas_encontradas,
                    areas_necesarias: result.areas_necesarias,
                    areas_faltantes: result.areas_faltantes,
                    fecha_consulta: result.fecha_consulta,
                    dia_semana: result.dia_semana
                });
                
                // Mostrar sugerencias
                mostrarSugerenciasDisponibilidad(fase, fecha, partidosNecesarios);
            }
        } else {
            // Error en la verificación
            btnVerificar.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error';
            btnVerificar.style.background = '#ffc107';
            btnVerificar.style.color = '#333';
            btnVerificar.disabled = false;
            
            showNotification('Error al verificar áreas: ' + (result.message || 'Error desconocido'), 'error');
        }
        
    } catch (error) {
        console.error('Error completo:', error);
        
        const btnVerificar = document.querySelector(`[onclick*="verificarAreasDisponibles('${fase}'"]`);
        if (btnVerificar) {
            btnVerificar.innerHTML = '<i class="fas fa-wifi"></i> Error conexión';
            btnVerificar.style.background = '#6c757d';
            btnVerificar.disabled = false;
        }
        
        showNotification('Error de conexión: ' + error.message, 'error');
    }
}

// ✅ NUEVA FUNCIÓN: Mostrar detalles de reservas realizadas
function mostrarDetallesReservas(fase, reservasDetalle) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1001;
        backdrop-filter: blur(5px);
    `;
    
    modal.innerHTML = `
        <div style="background: white; border-radius: 12px; width: 90%; max-width: 700px; max-height: 80vh; overflow-y: auto;">
            <div style="padding: 25px; border-bottom: 2px solid #f1f3f5; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #28a745, #20c997); color: white; border-radius: 12px 12px 0 0;">
                <h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle"></i>
                    Reservas Confirmadas - ${fase}
                </h3>
                <button onclick="this.closest('.modal-overlay').remove()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: white;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div style="padding: 25px;">
                <div style="margin-bottom: 20px; padding: 15px; background: #d1edff; border-radius: 8px; border-left: 4px solid #0066cc;">
                    <h4 style="margin: 0 0 10px 0; color: #0066cc;">
                        <i class="fas fa-info-circle"></i> Resumen de Reservas
                    </h4>
                    <p style="margin: 0; color: #0066cc;">
                        Se han reservado <strong>${reservasDetalle.length} área(s)</strong> para los partidos de <strong>${fase}</strong>
                    </p>
                </div>
                
                <div style="display: grid; gap: 15px;">
                    ${reservasDetalle.map((reserva, index) => `
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 3px solid #28a745;">
                            <div>
                                <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                                    <i class="fas fa-map-marked-alt"></i> ${reserva.nombre_area}
                                </div>
                                <div style="font-size: 0.9em; color: #666; display: flex; align-items: center; gap: 15px;">
                                    <span><i class="fas fa-clock"></i> ${reserva.hora_inicio} - ${reserva.hora_fin}</span>
                                    <span><i class="fas fa-users"></i> Partido ${index + 1}</span>
                                    <span><i class="fas fa-dollar-sign"></i> S/. ${reserva.tarifa_por_hora}/hora</span>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="background: #28a745; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.8em; font-weight: 600;">
                                    RESERVADO
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
                
                <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                    <div style="display: flex; align-items: center; gap: 10px; color: #856404;">
                        <i class="fas fa-lightbulb"></i>
                        <strong>Nota:</strong> Las reservas se han confirmado automáticamente para el torneo. Podrás gestionarlas desde el panel de reservas.
                    </div>
                </div>
            </div>
            
            <div style="padding: 20px 25px; border-top: 1px solid #f1f3f5; display: flex; justify-content: center; background: #f8f9fa; border-radius: 0 0 12px 12px;">
                <button onclick="this.closest('.modal-overlay').remove()" 
                        style="background: #28a745; color: white; padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-check"></i> Entendido
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// ✅ NUEVA FUNCIÓN: Mostrar sugerencias cuando no hay disponibilidad
function mostrarSugerenciasDisponibilidad(fase, fecha, partidosNecesarios) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1001;
        backdrop-filter: blur(5px);
    `;
    
    const fechaFormateada = new Date(fecha).toLocaleDateString('es-ES', {
        weekday: 'long',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
    
    modal.innerHTML = `
        <div style="background: white; border-radius: 12px; width: 90%; max-width: 600px;">
            <div style="padding: 25px; border-bottom: 2px solid #f1f3f5; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #dc3545, #e74c3c); color: white; border-radius: 12px 12px 0 0;">
                <h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    Sin Disponibilidad - ${fase}
                </h3>
                <button onclick="this.closest('.modal-overlay').remove()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: white;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div style="padding: 25px;">
                <div style="margin-bottom: 20px; padding: 15px; background: #ffe6e6; border-radius: 8px; border-left: 4px solid #dc3545;">
                    <h4 style="margin: 0 0 10px 0; color: #dc3545;">
                        <i class="fas fa-calendar-times"></i> No hay áreas disponibles
                    </h4>
                    <p style="margin: 0; color: #dc3545;">
                        Para <strong>${fechaFormateada}</strong> no se encontraron <strong>${partidosNecesarios} área(s)</strong> libres para los partidos de <strong>${fase}</strong>
                    </p>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <h4 style="color: #2c3e50; margin-bottom: 15px;">
                        <i class="fas fa-lightbulb"></i> Sugerencias:
                    </h4>
                    <div style="display: grid; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                            <i class="fas fa-calendar-alt" style="color: #17a2b8; font-size: 18px;"></i>
                            <div>
                                <strong>Cambiar fecha del torneo</strong>
                                <div style="font-size: 0.9em; color: #666;">Elige una fecha con menos reservas</div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                            <i class="fas fa-clock" style="color: #28a745; font-size: 18px;"></i>
                            <div>
                                <strong>Revisar horarios</strong>
                                <div style="font-size: 0.9em; color: #666;">Cambia a horario de tardes o fines de semana</div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                            <i class="fas fa-list-alt" style="color: #ffc107; font-size: 18px;"></i>
                            <div>
                                <strong>Gestionar reservas</strong>
                                <div style="font-size: 0.9em; color: #666;">Revisa y reorganiza tus reservas existentes</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 20px; padding: 15px; background: #e3f2fd; border-radius: 8px; border-left: 4px solid #2196f3;">
                    <div style="display: flex; align-items: center; gap: 10px; color: #1976d2;">
                        <i class="fas fa-info-circle"></i>
                        <strong>Tip:</strong> Los horarios de mañana y fines de semana suelen tener más disponibilidad.
                    </div>
                </div>
            </div>
            
            <div style="padding: 20px 25px; border-top: 1px solid #f1f3f5; display: flex; justify-content: space-between; gap: 15px; background: #f8f9fa; border-radius: 0 0 12px 12px;">
                <button onclick="window.location.href='reservas.php'" 
                        style="background: #17a2b8; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-calendar-check"></i> Ver Reservas
                </button>
                <button onclick="this.closest('.modal-overlay').remove()" 
                        style="background: #6c757d; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// ✅ FUNCIÓN SÚPER SIMPLE: Solo verificar disponibilidad
async function verificarAreasDisponibles(fase, fecha, equipos) {
    try {
        const sedeId = document.getElementById('sedeTorneo').value;
        const deporteId = document.getElementById('deporteTorneo').value;
        
        if (!sedeId || !deporteId) {
            showNotification('Selecciona sede y deporte primero', 'warning');
            return;
        }
        
        // Actualizar botón a estado de carga
        const btnVerificar = document.querySelector(`[onclick*="verificarAreasDisponibles('${fase}'"]`);
        if (btnVerificar) {
            btnVerificar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
            btnVerificar.style.background = '#6c757d';
            btnVerificar.disabled = true;
        }
        
        const partidosNecesarios = Math.floor(equipos / 2);
        
        const requestData = {
            action: 'verificar_y_reservar_automatico',
            sede_id: parseInt(sedeId),
            deporte_id: parseInt(deporteId),
            fecha: fecha,
            partidos_necesarios: partidosNecesarios,
            fase: fase,
            horario_torneo: document.getElementById('horarioTorneo').value || 'mananas'
        };
        
        const response = await fetch(`../../Models/AreasDeportivasModel.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        const responseText = await response.text();
        console.log('Respuesta del servidor:', responseText);
        
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Error parseando JSON:', parseError);
            
            if (btnVerificar) {
                btnVerificar.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error servidor';
                btnVerificar.style.background = '#dc3545';
                btnVerificar.disabled = false;
            }
            
            showNotification('Error en el servidor. Revisa la consola.', 'error');
            return;
        }
        
        if (result.success) {
            if (result.reservas_realizadas > 0) {
                // ✅ ÉXITO: Áreas encontradas
                btnVerificar.innerHTML = `<i class="fas fa-check-circle"></i> ${result.reservas_realizadas} área(s) disponible(s)`;
                btnVerificar.style.background = '#28a745';
                btnVerificar.style.color = 'white';
                btnVerificar.disabled = false;
                
                const mensaje = result.simulado ? 
                    `✅ ${result.reservas_realizadas} área(s) disponible(s) para ${fase}` :
                    `✅ ${result.reservas_realizadas} área(s) reservada(s) para ${fase}`;
                
                showNotification(mensaje, 'success');
                
                // Mostrar detalles
                setTimeout(() => {
                    mostrarAreasDisponibles(fase, result.reservas_detalle, result.simulado || false);
                }, 1000);
                
            } else {
                // ❌ NO HAY DISPONIBILIDAD
                btnVerificar.innerHTML = '<i class="fas fa-times-circle"></i> Sin disponibilidad';
                btnVerificar.style.background = '#dc3545';
                btnVerificar.style.color = 'white';
                btnVerificar.disabled = false;
                
                showNotification(`❌ ${result.message}`, 'error');
            }
        } else {
            // Error
            btnVerificar.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error';
            btnVerificar.style.background = '#ffc107';
            btnVerificar.style.color = '#333';
            btnVerificar.disabled = false;
            
            showNotification('Error: ' + (result.message || 'Error desconocido'), 'error');
        }
        
    } catch (error) {
        console.error('Error:', error);
        
        const btnVerificar = document.querySelector(`[onclick*="verificarAreasDisponibles('${fase}'"]`);
        if (btnVerificar) {
            btnVerificar.innerHTML = '<i class="fas fa-wifi"></i> Error conexión';
            btnVerificar.style.background = '#6c757d';
            btnVerificar.disabled = false;
        }
        
        showNotification('Error de conexión: ' + error.message, 'error');
    }
}

// ✅ NUEVA FUNCIÓN SIMPLE: Mostrar áreas disponibles (sin reservar)
function mostrarAreasDisponibles(fase, areasDetalle, esSimulacion = true) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1001;
        backdrop-filter: blur(5px);
    `;
    
    const tituloModal = esSimulacion ? 'Áreas Disponibles' : 'Reservas Confirmadas';
    const colorTema = esSimulacion ? '#17a2b8' : '#28a745';
    const iconoTema = esSimulacion ? 'fa-search' : 'fa-check-circle';
    
    const duracionTexto = '1h';
    
    modal.innerHTML = `
        <div style="background: white; border-radius: 12px; width: 90%; max-width: 700px; max-height: 80vh; overflow-y: auto;">
            <div style="padding: 25px; border-bottom: 2px solid #f1f3f5; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, ${colorTema}, #20c997); color: white; border-radius: 12px 12px 0 0;">
                <h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fas ${iconoTema}"></i>
                    ${tituloModal} - ${fase}
                </h3>
                <button onclick="this.closest('.modal-overlay').remove()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: white;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div style="padding: 25px;">
                <div style="margin-bottom: 20px; padding: 15px; background: #e3f2fd; border-radius: 8px; border-left: 4px solid ${colorTema};">
                    <h4 style="margin: 0 0 10px 0; color: ${colorTema};">
                        <i class="fas fa-info-circle"></i> ${esSimulacion ? 'Horarios Disponibles' : 'Horarios Reservados'}
                    </h4>
                    <p style="margin: 0; color: ${colorTema};">
                        Se ${esSimulacion ? 'encontraron' : 'reservaron'} <strong>${areasDetalle.length} área(s)</strong> para los partidos de <strong>${fase}</strong>
                        <br><small>Duración por partido: <strong>${duracionTexto}</strong></small>
                    </p>
                </div>
                
                <div style="display: grid; gap: 15px;">
                    ${areasDetalle.map((area, index) => {
                        return `
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 3px solid ${colorTema};">
                            <div>
                                <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                                    <i class="fas fa-map-marked-alt"></i> ${area.nombre_area}
                                </div>
                                <div style="font-size: 0.9em; color: #666; display: flex; align-items: center; gap: 15px;">
                                    <span><i class="fas fa-clock"></i> ${area.hora_inicio} - ${area.hora_fin}</span>
                                    <span><i class="fas fa-stopwatch"></i> 1h</span>
                                    <span><i class="fas fa-users"></i> Partido ${index + 1}</span>
                                    <span><i class="fas fa-dollar-sign"></i> S/. ${area.tarifa_por_hora}/hora</span>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="background: ${colorTema}; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.8em; font-weight: 600;">
                                    ${esSimulacion ? 'DISPONIBLE' : 'RESERVADO'}
                                </div>
                            </div>
                        </div>
                        `;
                    }).join('')}
                </div>
                
                <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                    <div style="display: flex; align-items: center; gap: 10px; color: #856404;">
                        <i class="fas fa-lightbulb"></i>
                        <strong>Nota:</strong> ${esSimulacion ? 
                            `Estos horarios están disponibles. Duración: 1 hora por partido (40 min de juego + tiempo adicional).` : 
                            'Las reservas se han confirmado automáticamente para el torneo.'}
                    </div>
                </div>
            </div>
            
            <div style="padding: 20px 25px; border-top: 1px solid #f1f3f5; display: flex; justify-content: center; background: #f8f9fa; border-radius: 0 0 12px 12px;">
                <button onclick="this.closest('.modal-overlay').remove()" 
                        style="background: ${colorTema}; color: white; padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-check"></i> Entendido
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

// ✅ FUNCIÓN ACTUALIZADA: Generar cronograma con 1 hora por partido
function generarCronogramaConSorteos(fechaInicio, equipos, modalidad, horario) {
    let html = '';
    let fechaActual = new Date(fechaInicio);
    let equiposRestantes = equipos;
    let fase = 1;
    
    // ✅ CORREGIDO: 1 hora para todos los deportes
    const duracionPartido = 1.0; // 1 hora por partido
    const duracionTexto = '1h';
    
    const nombresFases = {
        16: 'Octavos de Final',
        8: 'Cuartos de Final', 
        4: 'Semifinal',
        2: 'Final'
    };
    
    // ✅ FUNCIÓN AUXILIAR: Buscar próximo día disponible según horario
    function buscarProximaFechaDisponible(fecha, horario, esPrimeraFecha = false) {
        let nuevaFecha = new Date(fecha);
        
        if (horario === 'fines_semana') {
            if (!esPrimeraFecha) {
                do {
                    nuevaFecha.setDate(nuevaFecha.getDate() + 1);
                } while (nuevaFecha.getDay() !== 6 && nuevaFecha.getDay() !== 0);
            } else {
                while (nuevaFecha.getDay() !== 6 && nuevaFecha.getDay() !== 0) {
                    nuevaFecha.setDate(nuevaFecha.getDate() + 1);
                }
            }
        } else {
            if (!esPrimeraFecha) {
                nuevaFecha.setDate(nuevaFecha.getDate() + 1);
            }
        }
        
        return nuevaFecha;
    }
    
    // Ajustar fecha inicial si es necesario
    fechaActual = buscarProximaFechaDisponible(fechaActual, horario, true);
    
    while (equiposRestantes > 1) {
        const partidos = Math.floor(equiposRestantes / 2);
        const hayImpar = equiposRestantes % 2 !== 0;
        const nombreFase = fase === 1 ? 'Primera Ronda' : (nombresFases[equiposRestantes] || `Ronda ${fase}`);
        
        // Para fases después de la primera, buscar próxima fecha disponible
        if (fase > 1) {
            fechaActual = buscarProximaFechaDisponible(fechaActual, horario, false);
        }
        
        const fechaFormateada = fechaActual.toLocaleDateString('es-ES', {
            weekday: 'long',
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
        
        const fechaISO = fechaActual.toISOString().split('T')[0];
        
        // ✅ CORREGIDO: 1 hora por partido
        const tiempoTotal = partidos * duracionPartido;
        
        html += `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; margin-bottom: 10px; background: white; border-radius: 8px; border-left: 3px solid var(--torneos-primary); box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 5px;">
                        <strong style="color: var(--torneos-text-primary); font-size: 1.1em;">${nombreFase}</strong>
                        <button onclick="verificarAreasDisponibles('${nombreFase}', '${fechaISO}', ${partidos * 2})" 
                                style="background: #17a2b8; color: white; border: none; padding: 6px 12px; border-radius: 20px; font-size: 0.8em; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 5px;"
                                onmouseover="this.style.background='#138496'; this.style.transform='scale(1.05)'"
                                onmouseout="this.style.background='#17a2b8'; this.style.transform='scale(1)'">
                            <i class="fas fa-map-marked-alt"></i> Verificar Áreas
                        </button>
                    </div>
                    <div style="font-size: 0.85em; color: #666;">
                        ${partidos} partido${partidos !== 1 ? 's' : ''} × 1h
                        ${hayImpar ? ' + 🎲 Sorteo pase directo' : ''}
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-weight: 600; color: #495057; margin-bottom: 3px;">${fechaFormateada}</div>
                    <div style="font-size: 0.8em; color: var(--torneos-primary);">⏰ ${tiempoTotal}h total${hayImpar ? ' + sorteo' : ''}</div>
                </div>
            </div>
        `;
        
        equiposRestantes = Math.ceil(equiposRestantes / 2);
        fase++;
    }
    
    return html;
}

// ✅ FUNCIONES FALTANTES QUE SE LLAMAN DESDE EL HTML

// ✅ FUNCIÓN: Calcular horarios del torneo
function calcularHorarios() {
    const fechaInicio = document.getElementById('fechaInicio').value;
    const maxEquipos = parseInt(document.getElementById('maxEquipos').value) || 0;
    const modalidad = document.getElementById('modalidadTorneo').value;
    const horario = document.getElementById('horarioTorneo').value;
    const sedeId = document.getElementById('sedeTorneo').value;
    const deporteId = document.getElementById('deporteTorneo').value;
    
    if (!fechaInicio || !maxEquipos || !modalidad || !horario) {
        document.getElementById('previsualizacionHorarios').style.display = 'none';
        return;
    }
    
    // Calcular partidos totales
    const partidosTotales = calcularPartidos(maxEquipos, modalidad);
    
    // Calcular días necesarios (máximo 4 partidos por día)
    const diasNecesarios = Math.ceil(partidosTotales / 4);
    
    // Calcular fecha de fin
    let fechaFin = new Date(fechaInicio);
    if (horario === 'fines_semana') {
        // Para fines de semana, agregar suficientes fines de semana
        let finesDeSemanaNecesarios = Math.ceil(diasNecesarios / 2);
        let finesDeSemanasContados = 0;
        
        while (finesDeSemanasContados < finesDeSemanaNecesarios) {
            fechaFin.setDate(fechaFin.getDate() + 1);
            if (fechaFin.getDay() === 0) { // Domingo
                finesDeSemanasContados++;
            }
        }
    } else {
        fechaFin.setDate(fechaFin.getDate() + diasNecesarios - 1);
    }
    
    // Actualizar campo de fecha fin
    document.getElementById('fechaFin').value = fechaFin.toISOString().split('T')[0];
    
    // Generar previsualización
    generarPrevisualizacion(maxEquipos, modalidad, fechaInicio, fechaFin.toISOString().split('T')[0], horario, partidosTotales, diasNecesarios);
}

// ✅ FUNCIÓN: Validar número de equipos
function validarEquipos() {
    const maxEquipos = parseInt(document.getElementById('maxEquipos').value) || 0;
    const btnSolicitud = document.getElementById('btnSolicitudIPD');
    const input = document.getElementById('maxEquipos');
    
    if (maxEquipos > 15) {
        input.value = 15;
        btnSolicitud.style.display = 'block';
        showNotification('Máximo 15 equipos para torneos privados. Para más equipos, solicita apoyo del IPD.', 'warning');
    } else if (maxEquipos < 4 && maxEquipos > 0) {
        input.value = 4;
        showNotification('Mínimo 4 equipos para un torneo válido', 'warning');
    } else {
        btnSolicitud.style.display = 'none';
    }
    
    // Recalcular horarios si es necesario
    calcularHorarios();
}

// ✅ FUNCIÓN: Calcular partidos según modalidad
function calcularPartidos(equipos, modalidad) {
    switch(modalidad) {
        case 'eliminacion_simple':
            return equipos - 1; // En eliminación simple siempre son equipos - 1 partidos
        case 'eliminacion_doble':
            return (equipos - 1) * 2; // En eliminación doble puede ser hasta el doble
        case 'todos_contra_todos':
            return (equipos * (equipos - 1)) / 2; // Cada equipo juega contra todos
        case 'grupos_eliminatoria':
            // Aproximación: 4 grupos de equipos/4, luego eliminatoria
            const gruposPartidos = Math.floor(equipos / 4) * 6; // 6 partidos por grupo de 4
            const eliminatoriaPartidos = 8 - 1; // Eliminatoria de 8 equipos
            return gruposPartidos + eliminatoriaPartidos;
        default:
            return equipos - 1;
    }
}

// ✅ FUNCIÓN: Generar previsualización del torneo
function generarPrevisualizacion(equipos, modalidad, fechaInicio, fechaFin, horario, partidos, dias) {
    const container = document.getElementById('previsualizacionHorarios');
    const infoContainer = document.getElementById('infoTorneo');
    const bracketsContainer = document.getElementById('bracketsPreview');
    
    if (!container) return;
    
    // Mostrar información básica
    infoContainer.innerHTML = `
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 15px;">
            <div style="text-align: center; padding: 10px; background: white; border-radius: 8px; border: 2px solid #28a745;">
                <div style="font-size: 24px; font-weight: bold; color: #28a745;">${equipos}</div>
                <div style="font-size: 12px; color: #666;">Equipos</div>
            </div>
            <div style="text-align: center; padding: 10px; background: white; border-radius: 8px; border: 2px solid #17a2b8;">
                <div style="font-size: 24px; font-weight: bold; color: #17a2b8;">${partidos}</div>
                <div style="font-size: 12px; color: #666;">Partidos</div>
            </div>
            <div style="text-align: center; padding: 10px; background: white; border-radius: 8px; border: 2px solid #ffc107;">
                <div style="font-size: 24px; font-weight: bold; color: #856404;">${dias}</div>
                <div style="font-size: 12px; color: #666;">Días</div>
            </div>
            <div style="text-align: center; padding: 10px; background: white; border-radius: 8px; border: 2px solid #dc3545;">
                <div style="font-size: 14px; font-weight: bold, color: #dc3545;">${fechaFin}</div>
                <div style="font-size: 12px; color: #666;">Fecha Fin</div>
            </div>
        </div>
    `;
    
    // Generar brackets según modalidad
    if (modalidad === 'eliminacion_simple') {
        bracketsContainer.innerHTML = generarBracketsEliminacion(equipos);
    } else {
        bracketsContainer.innerHTML = `<div style="text-align: center; padding: 20px; color: #666;">Modalidad en desarrollo</div>`;
    }
    
    // Generar cronograma
    const cronograma = generarCronogramaConSorteos(fechaInicio, equipos, modalidad, horario);
    mostrarHorariosRecomendados(fechaInicio, fechaFin, horario, partidos, equipos, modalidad);
    
    container.style.display = 'block';
}

// ✅ FUNCIÓN: Generar brackets de eliminación simple
function generarBracketsEliminacion(equipos) {
    let html = '';
    let equiposRestantes = equipos;
    let ronda = 1;
    
    const nombresFases = {
        16: 'Octavos de Final',
        8: 'Cuartos de Final',
        4: 'Semifinal',
        2: 'Final'
    };
    
    while (equiposRestantes > 1) {
        const partidos = Math.floor(equiposRestantes / 2);
        const hayImpar = equiposRestantes % 2 !== 0;
        const nombreFase = ronda === 1 ? 'Primera Ronda' : (nombresFases[equiposRestantes] || `Ronda ${ronda}`);
        
        html += `<div style="margin-bottom: 20px;">`;
        html += `<div style="margin-bottom: 10px;"><strong>${nombreFase}:</strong></div>`;
        html += `<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">`;
        
        // Generar partidos
        for (let i = 0; i < partidos; i++) {
            const equipo1 = ronda === 1 ? `Equipo ${(i * 2 + 1).toString().padStart(2, '0')}` : `Ganador P${i * 2 + 1}`;
            const equipo2 = ronda === 1 ? `Equipo ${(i * 2 + 2).toString().padStart(2, '0')}` : `Ganador P${i * 2 + 2}`;
            
            html += `
                <div style="background: #e3f2fd; padding: 12px; border-radius: 8px; text-align: center; border: 2px solid #2196f3;">
                    <div style="font-weight: 600; color: #1976d2; margin-bottom: 5px;">Partido ${i + 1}</div>
                    <div style="font-weight: 500; color: #495057; font-size: 0.9em;">${equipo1}</div>
                    <div style="margin: 5px 0; color: #2196f3; font-weight: bold;">VS</div>
                    <div style="font-weight: 500; color: #495057; font-size: 0.9em;">${equipo2}</div>
                </div>
            `;
        }
        
        // Equipo con pase directo si hay número impar
        if (hayImpar) {
            const equipoDirecto = ronda === 1 ? 
                `Equipo ${equipos.toString().padStart(2, '0')} (Pase Directo)` : 
                'Ganador sorteo anterior';
            
            html += `
                <div style="background: #fff3cd; padding: 12px; border-radius: 8px; text-align: center; border: 2px solid #ffc107;">
                    <div style="font-weight: 600; color: #856404; margin-bottom: 5px;">🎲 Sorteo - Pase Directo</div>
                    <div style="font-weight: 500; color: #495057; font-size: 0.85em;">${equipoDirecto}</div>
                    <div style="margin: 5px 0; color: #ffc107; font-weight: bold;">🎯</div>
                    <div style="font-size: 0.8em; color: #666;">Se sorteará entre ganadores</div>
                </div>
            `;
        }
        
        html += '</div>';
        
        // Explicación del sorteo si hay equipos impares
        if (hayImpar && equiposRestantes > 2) {
            html += `
                <div style="margin-top: 10px; padding: 10px; background: #e8f4fd; border-radius: 6px; border-left: 4px solid #2196f3;">
                    <small style="color: #1976d2; font-weight: 500;">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Sorteo de Pase Directo:</strong> 
                        Entre los ${partidos} ganadores de esta ronda, se sorteará quién avanza automáticamente a la siguiente fase.
                    </small>
                </div>
            `;
        }
        
        html += '</div>';
        
        equiposRestantes = Math.ceil(equiposRestantes / 2);
        ronda++;
    }
    
    return html;
}

// ✅ FUNCIÓN: Mostrar horarios recomendados
function mostrarHorariosRecomendados(fechaInicio, fechaFin, horario, partidos, equipos, modalidad) {
    const container = document.getElementById('horariosRecomendados');
    
    if (!container) return;
    
    let horariosTexto = '';
    let horasRango = '';
    
    switch(horario) {
        case 'mananas':
            horasRango = '8:00 AM - 12:00 PM';
            horariosTexto = 'Lunes a Domingo en horario matutino';
            break;
        case 'tardes':
            horasRango = '2:00 PM - 8:00 PM';
            horariosTexto = 'Lunes a Domingo en horario vespertino';
            break;
        case 'fines_semana':
            horasRango = '9:00 AM - 6:00 PM';
            horariosTexto = 'Solo Sábados y Domingos';
            break;
    }
    
    // Generar cronograma por fases con fechas correctas
    let cronograma = generarCronogramaConSorteos(fechaInicio, equipos, modalidad, horario);
    
    container.innerHTML = `
        <div style="background: white; padding: 15px; border-radius: 8px; border-left: 4px solid var(--torneos-primary);">
            <h5 style="margin: 0 0 15px 0; color: var(--torneos-primary);"><i class="fas fa-clock"></i> Cronograma del Torneo</h5>
            <div style="margin-bottom: 15px;">
                <p style="margin: 0 0 5px 0;"><strong>Período:</strong> ${fechaInicio} al ${fechaFin}</p>
                <p style="margin: 0 0 5px 0;"><strong>Horario:</strong> ${horasRango}</p>
                <p style="margin: 0 0 15px 0;"><strong>Días:</strong> ${horariosTexto}</p>
            </div>
            
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                <h6 style="margin: 0 0 10px 0; color: #495057;">📅 Programación por Fases:</h6>
                ${cronograma}
            </div>
            
            <div style="margin-top: 10px; padding: 10px; background: #e8f5e8; border-radius: 6px; font-size: 0.9em;">
                <strong>💡 Nota:</strong> Cada fase se realizará en un día diferente${horario === 'fines_semana' ? ' (solo fines de semana)' : ''}. Los sorteos para pases directos se realizarán después de cada ronda.
            </div>
        </div>
    `;
}

// ✅ FUNCIÓN: Habilitar edición de premios
function habilitarEdicionPremios() {
    const premiosField = document.getElementById('premioDescripcion');
    if (premiosField.readOnly) {
        premiosField.readOnly = false;
        premiosField.style.background = 'white';
        premiosField.style.border = '2px solid #28a745';
        premiosField.focus();
        
        // Colocar cursor al final del primer premio
        const texto = premiosField.value;
        const posicionPrimerPremio = texto.indexOf('🥇 1er puesto: ') + 15;
        premiosField.setSelectionRange(posicionPrimerPremio, posicionPrimerPremio);
        
        showNotification('Ahora puedes editar la descripción de los premios', 'info');
    }
}

// ✅ FUNCIÓN: Validar premios completos
function validarPremios(premiosTexto) {
    if (!premiosTexto) return false;
    
    // Verificar que contenga los tres premios con algún texto después de los dos puntos
    const patrones = [
        /🥇 1er puesto:\s*(.+)/,
        /🥈 2do puesto:\s*(.+)/,
        /🥉 3er puesto:\s*(.+)/
    ];
    
    return patrones.every(patron => {
        const match = premiosTexto.match(patron);
        return match && match[1].trim().length > 0;
    });
}

// ✅ FUNCIÓN: Solicitar apoyo IPD
function solicitarIPD() {
    showNotification('Redirigiendo al formulario de solicitud IPD...', 'info');
    
    // Aquí puedes agregar la lógica para abrir un formulario o redirigir
    setTimeout(() => {
        alert('Función en desarrollo: Formulario de solicitud al IPD para torneos de más de 15 equipos');
    }, 1000);
}

// ✅ FUNCIÓN: Resetear formulario
function resetearFormulario() {
    const form = document.getElementById('formTorneo');
    if (form) {
        form.reset();
        
        // Resetear campos específicos
        document.getElementById('fechaFin').value = '';
        document.getElementById('imagenTorneoURL').value = '';
        document.getElementById('previewImagen').innerHTML = '<i class="fas fa-image" style="color: #ccc; font-size: 24px;"></i>';
        document.getElementById('premiodeScripcion').value = '🥇 1er puesto: \n🥈 2do puesto: \n🥉 3er puesto: ';
        document.getElementById('premioDescripcion').readOnly = true;
        document.getElementById('premioDescripcion').style.background = '#f8f9fa';
        document.getElementById('premioDescripcion').style.border = '2px solid #e9ecef';
        document.getElementById('previsualizacionHorarios').style.display = 'none';
        document.getElementById('uploadStatus').style.display = 'none';
    }
}

// ✅ FUNCIÓN: Cargar torneos ficticios (fallback)
function cargarTorneosFicticios() {
    torneosData = [
        {
            id: 1,
            nombre: "Copa Top Gol 2025",
            descripcion: "Torneo de fútbol amateur",
            deporte_id: 1,
            estado: "inscripciones_abiertas",
            modalidad: "eliminacion_simple",
            max_equipos: 16,
            equipos_inscritos: 8,
            fecha_inicio: "2025-07-15",
            fecha_fin: "2025-07-28",
            fecha_inscripcion_fin: "2025-07-10",
            costo_inscripcion: 150.00,
            organizador_tipo: "institucion",
            sede_nombre: "Top Gol Tacna",
            imagen_torneo: null
        }
    ];
    
    actualizarEstadisticas();
    mostrarTorneos(torneosData);
    showNotification('Datos de demostración cargados', 'info');
}