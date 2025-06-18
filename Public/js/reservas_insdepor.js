// Variables globales
let fechaSeleccionada = new Date().toISOString().split('T')[0];

// Inicializar al cargar DOM
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Sistema de reservas inicializado');
    inicializarEventos();
});

// Inicializar eventos
function inicializarEventos() {
    // Event listener para cambio de fecha
    const fechaInput = document.getElementById('fechaReservas');
    if (fechaInput) {
        fechaInput.addEventListener('change', function() {
            fechaSeleccionada = this.value;
            cargarTodasLasAreas();
        });
    }
    
    // Event listeners para bloques de horarios
    document.addEventListener('click', function(e) {
        if (e.target.closest('.horario-bloque')) {
            const bloque = e.target.closest('.horario-bloque');
            if (bloque.classList.contains('ocupado')) {
                mostrarDetalleReserva(bloque);
            } else if (bloque.classList.contains('disponible')) {
                console.log('Bloque disponible clickeado:', bloque.dataset.hora);
            }
        }
    });
    
    // Cargar áreas iniciales
    setTimeout(cargarTodasLasAreas, 1000);
}

// ✅ NUEVA FUNCIÓN: Cargar todas las áreas
async function cargarTodasLasAreas() {
    const areasContainers = document.querySelectorAll('.horarios-grid[data-area-id]');
    
    for (const container of areasContainers) {
        const areaId = container.dataset.areaId;
        await cargarHorariosPorArea(areaId);
    }
    
    // Después cargar las reservas
    await cargarReservasReales();
    actualizarEstadisticas();
}

// ✅ NUEVA FUNCIÓN: Cargar horarios por área
async function cargarHorariosPorArea(areaId) {
    try {
        const response = await fetch(`../../Controllers/ReservaController.php?action=getCronograma&area_id=${areaId}&fecha=${fechaSeleccionada}`);
        const result = await response.json();
        
        if (result.success) {
            generarBloquesPorArea(areaId, result.data);
        } else {
            console.error('Error cargando horarios del área:', result.message);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// ✅ NUEVA FUNCIÓN: Generar bloques de horarios
function generarBloquesPorArea(areaId, datos) {
    const container = document.querySelector(`[data-area-id="${areaId}"] .horarios-grid`);
    if (!container || datos.cerrado) return;
    
    const { cronograma } = datos;
    let html = '';
    
    cronograma.forEach(bloque => {
        const claseEstado = bloque.disponible ? 'disponible' : 'ocupado';
        const horaTexto = `${bloque.hora_inicio}-${bloque.hora_fin}`;
        
        html += `
            <div class="horario-bloque ${claseEstado}" data-hora="${horaTexto}" data-area-id="${areaId}">
                <span class="hora">${bloque.hora_inicio}</span>
                <span class="duracion">30min</span>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// ✅ NUEVA FUNCIÓN: Cargar reservas reales desde la base de datos
async function cargarReservasReales() {
    try {
        const response = await fetch(`../../Controllers/ReservaController.php?action=reservas_institucion&fecha=${fechaSeleccionada}`);
        const result = await response.json();
        
        if (result.success) {
            actualizarVistaReservas(result.data);
        } else {
            console.error('Error cargando reservas:', result.message);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// ✅ NUEVA FUNCIÓN: Actualizar vista con reservas reales
function actualizarVistaReservas(reservas) {
    // Limpiar reservas anteriores
    const todosLosBloques = document.querySelectorAll('.horario-bloque');
    todosLosBloques.forEach(bloque => {
        bloque.classList.remove('ocupado');
        bloque.classList.add('disponible');
        const reservaInfo = bloque.querySelector('.reserva-info');
        if (reservaInfo) {
            reservaInfo.remove();
        }
    });
    
    // Marcar bloques ocupados según reservas reales
    reservas.forEach(reserva => {
        const areaId = reserva.area_deportiva_id;
        const horaInicio = reserva.hora_inicio.substring(0, 5); // HH:MM
        const horaFin = reserva.hora_fin.substring(0, 5);
        
        // Buscar bloques que coincidan con la reserva
        const bloquesArea = document.querySelectorAll(`[data-area-id="${areaId}"] .horario-bloque`);
        
        bloquesArea.forEach(bloque => {
            const horaBloque = bloque.dataset.hora;
            const [inicioBloque] = horaBloque.split('-');
            
            // Si la hora del bloque está dentro del rango de la reserva
            if (inicioBloque >= horaInicio && inicioBloque < horaFin) {
                bloque.classList.remove('disponible');
                bloque.classList.add('ocupado');
                
                // Agregar información de la reserva
                const reservaInfo = document.createElement('div');
                reservaInfo.className = 'reserva-info';
                reservaInfo.innerHTML = `<small>${reserva.cliente_nombre}</small>`;
                bloque.appendChild(reservaInfo);
                
                // Actualizar datos para el modal
                bloque.dataset.reservaId = reserva.id;
                bloque.dataset.clienteNombre = reserva.cliente_nombre;
                bloque.dataset.clienteTelefono = reserva.cliente_telefono || 'No disponible';
                bloque.dataset.areaNombre = reserva.nombre_area;
                bloque.dataset.tarifaHora = reserva.tarifa_por_hora;
                bloque.dataset.estado = reserva.estado;
            }
        });
    });
    
    showNotification('Reservas actualizadas', 'success');
}

// ✅ NUEVA FUNCIÓN: Actualizar estadísticas
function actualizarEstadisticas() {
    const reservasHoyElement = document.getElementById('reservasHoy');
    const horasOcupadasElement = document.getElementById('horasOcupadas');
    const ingresosDiaElement = document.getElementById('ingresosDia');
    
    const bloquesOcupados = document.querySelectorAll('.horario-bloque.ocupado');
    const reservasHoy = new Set();
    
    bloquesOcupados.forEach(bloque => {
        if (bloque.dataset.reservaId) {
            reservasHoy.add(bloque.dataset.reservaId);
        }
    });
    
    reservasHoyElement.textContent = reservasHoy.size;
    horasOcupadasElement.textContent = Math.round(bloquesOcupados.length * 0.5); // 30min = 0.5 horas
    
    // Calcular ingresos aproximados
    let ingresos = 0;
    bloquesOcupados.forEach(bloque => {
        const tarifa = parseFloat(bloque.dataset.tarifaHora || 0);
        ingresos += tarifa * 0.5; // 30 minutos
    });
    
    ingresosDiaElement.textContent = `S/. ${ingresos.toFixed(2)}`;
}

// Actualizar reservas
function actualizarReservas() {
    console.log('Actualizando reservas para fecha:', fechaSeleccionada);
    cargarTodasLasAreas();
}

// Filtrar por instalación
function filtrarPorInstalacion() {
    const filtro = document.getElementById('filtroInstalacion').value;
    const instalaciones = document.querySelectorAll('.instalacion-reservas');
    
    instalaciones.forEach(instalacion => {
        if (filtro === '' || instalacion.dataset.instalacionId === filtro) {
            instalacion.style.display = 'block';
        } else {
            instalacion.style.display = 'none';
        }
    });
}

// Filtrar por deporte
function filtrarPorDeporte() {
    const filtro = document.getElementById('filtroDeporte').value;
    const areas = document.querySelectorAll('.area-deportiva');
    
    areas.forEach(area => {
        if (filtro === '' || area.dataset.deporte === getDeporteNombre(filtro)) {
            area.style.display = 'block';
        } else {
            area.style.display = 'none';
        }
    });
}

// Filtrar por estado
function filtrarPorEstado() {
    const filtro = document.getElementById('filtroEstado').value;
    const bloques = document.querySelectorAll('.horario-bloque');
    
    bloques.forEach(bloque => {
        const esVisible = filtro === '' || bloque.classList.contains(filtro);
        bloque.style.display = esVisible ? 'flex' : 'none';
    });
}

// Obtener nombre del deporte
function getDeporteNombre(id) {
    const deportes = {
        '1': 'futbol',
        '2': 'voley',
        '3': 'basquet'
    };
    return deportes[id] || '';
}

// Mostrar detalle de reserva
function mostrarDetalleReserva(bloque) {
    const modalBody = document.getElementById('modalBody');
    const hora = bloque.dataset.hora;
    const clienteNombre = bloque.dataset.clienteNombre || 'Cliente';
    const clienteTelefono = bloque.dataset.clienteTelefono || 'No disponible';
    const areaNombre = bloque.dataset.areaNombre || 'Área deportiva';
    const tarifaHora = bloque.dataset.tarifaHora || '0.00';
    const estado = bloque.dataset.estado || 'confirmada';
    
    modalBody.innerHTML = `
        <div class="detalle-reserva">
            <h4><i class="fas fa-clock"></i> ${hora}</h4>
            <div class="info-item">
                <strong>Cliente:</strong> ${clienteNombre}
            </div>
            <div class="info-item">
                <strong>Área:</strong> ${areaNombre}
            </div>
            <div class="info-item">
                <strong>Fecha:</strong> ${fechaSeleccionada}
            </div>
            <div class="info-item">
                <strong>Teléfono:</strong> ${clienteTelefono}
            </div>
            <div class="info-item">
                <strong>Tarifa:</strong> S/. ${parseFloat(tarifaHora).toFixed(2)} por hora
            </div>
            <div class="info-item">
                <strong>Estado:</strong> <span class="badge ${estado}">${estado.charAt(0).toUpperCase() + estado.slice(1)}</span>
            </div>
        </div>
    `;
    
    document.getElementById('modalReserva').style.display = 'flex';
}

// Cerrar modal
function cerrarModal() {
    document.getElementById('modalReserva').style.display = 'none';
}

// Contactar cliente
function contactarCliente() {
    const telefono = '+51946143071'; // Ejemplo
    window.open(`tel:${telefono}`, '_self');
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
                          type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span style="margin-left: 10px;">${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 4000);
}

// Estilos adicionales
const additionalStyles = document.createElement('style');
additionalStyles.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    .detalle-reserva {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .detalle-reserva h4 {
        color: var(--primary-color);
        margin: 0;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--border-light);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid var(--border-light);
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge.confirmada {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
        border: 1px solid rgba(40, 167, 69, 0.3);
    }
    
    .badge.pendiente {
        background: rgba(255, 193, 7, 0.1);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }
    
    .loading-horarios {
        text-align: center;
        padding: 40px;
        color: var(--text-secondary);
    }
    
    .no-areas {
        text-align: center;
        padding: 40px;
        color: var(--text-secondary);
    }
`;
document.head.appendChild(additionalStyles);