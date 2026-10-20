import { pedirApi, ErrorApi } from './api.js';
import { mostrarAviso, formatearFechaHora, capitalizar } from './ui.js';

// Qué botones tienen sentido en cada estado (la regla de verdad está en EstadoReserva, en PHP)
const ACCIONES_POR_ESTADO = {
    pendiente: ['confirmada', 'cancelada'],
    confirmada: ['cancelada'],
    cancelada: [],
};

const PREGUNTAS = {
    confirmada: '¿Confirmar esta reserva?',
    cancelada: '¿Seguro que quieres cancelar esta reserva? No se puede deshacer.',
};

const acciones = document.querySelector('#acciones');
const botones = acciones.querySelectorAll('button[data-estado]');
const aviso = document.querySelector('#aviso-detalle');
const etiquetaEstado = document.querySelector('#estado-reserva');
const historial = document.querySelector('#historial');
const id = acciones.dataset.id;

for (const boton of botones) {
    boton.addEventListener('click', () => cambiarEstado(boton.dataset.estado));
}

async function cambiarEstado(nuevoEstado) {
    if (!confirm(PREGUNTAS[nuevoEstado])) {
        return;
    }

    mostrarAviso(aviso, 'Guardando…');
    activarBotones(false);

    try {
        const reserva = await pedirApi(`/api/reservas/${id}/estado`, {
            method: 'PATCH',
            datos: { estado: nuevoEstado },
        });
        pintarEstado(reserva.estado);
        pintarHistorial(reserva.eventos);
        mostrarAviso(aviso, `Reserva ${reserva.estado}`, 'exito');
    } catch (error) {
        if (error instanceof ErrorApi) {
            mostrarAviso(aviso, error.message, 'error');
        } else {
            console.error(error);
            mostrarAviso(aviso, 'Error inesperado en la página', 'error');
        }
    } finally {
        activarBotones(true);
    }
}

function pintarEstado(estado) {
    etiquetaEstado.className = `estado estado--${estado}`;
    etiquetaEstado.textContent = capitalizar(estado);

    for (const boton of botones) {
        boton.hidden = !ACCIONES_POR_ESTADO[estado].includes(boton.dataset.estado);
    }
}

function pintarHistorial(eventos) {
    historial.replaceChildren();
    for (const evento of eventos) {
        const li = document.createElement('li');
        li.textContent = `${formatearFechaHora(evento.fecha_creacion)} — ${evento.descripcion}`;
        historial.append(li);
    }
}

function activarBotones(activos) {
    for (const boton of botones) {
        boton.disabled = !activos;
    }
}
