import { pedirApi, ErrorApi } from './api.js';
import { mostrarAviso, formatearFecha, formatearImporte, capitalizar } from './ui.js';

const formulario = document.querySelector('#filtros');
const botonFiltrar = formulario.querySelector('button[type="submit"]');
const botonLimpiar = document.querySelector('#limpiar-filtros');
const cuerpoTabla = document.querySelector('#reservas-cuerpo');
const aviso = document.querySelector('#aviso-listado');

formulario.addEventListener('submit', (evento) => {
    evento.preventDefault();   // evita que el navegador envie el formulario y recargue la pagina
    cargarReservas();
});

botonLimpiar.addEventListener('click', () => {
    formulario.reset();        // vacia los campos
    cargarReservas();
});

async function cargarReservas() {

    const parametros = new URLSearchParams();
    const form = new FormData(formulario);
    for (const [calve, valor] of form) {
        if (valor.trim !== '') {
            parametros.append(calve, valor.trim());
        }
    }

    mostrarAviso(aviso, 'Cargando....');
    botonFiltrar.disabled = true;

    try {
        const datos = await pedirApi(`/api/reservas?${parametros}`);
        pintarFilas(datos.reservas);
        mostrarAviso(aviso, `Reservas encontradas: ${datos.total}`, 'exito');
    } catch (error) {
        let texto;
        if (error instanceof ErrorApi) {
            // Error de la API o de la red: su mensaje está pensado para el usuario
            texto = error.message;
            const detalles = Object.values(error.errores).join('. ');
            if (detalles !== '') {
                texto = `${error.message}: ${detalles}`;
            }
        } else {
            // Fallo del propio JS: el usuario ve un mensaje generico y el detalle va a la consola
            console.error(error);
            texto = 'Error inesperado en la página';
        }
        mostrarAviso(aviso, texto, 'error');
    } finally {
        botonFiltrar.disabled = false;
    }
}

function pintarFilas(reservas) {

    cuerpoTabla.replaceChildren();

    if (reservas.legth === 0) {
        const fila = document.createElement('tr');
        const vacia = celda('No hay reservas');
        vacia.colSpan = 7;
        fila.append(vacia);
        cuerpoTabla.append(fila);
    } else {
        for (const reserva of reservas) {
            const fila = document.createElement('tr');
            fila.append(
                celda(reserva.nombre_huesped),
                celda(reserva.nombre_alojamiento),
                celda(formatearFecha(reserva.fecha_entrada)),
                celda(formatearFecha(reserva.fecha_salida)),
                celdaEstado(reserva.estado),
                celda(formatearImporte(reserva.importe)),
                celdaEnlace(reserva.id),
            );
            cuerpoTabla.append(fila);
        }
    }

}

// Crea una celda <td> con el texto indicado.
function celda(texto) {
    const td = document.createElement('td');
    td.textContent = texto;
    return td;
}

// Crea una celda <td> con el texto indicado.
function celdaEstado(estado) {
    const span = document.createElement('span');
    span.className = `estado estado--${estado}`;
    span.textContent = capitalizar(estado);

    const td = document.createElement('td');
    td.append(span);
    return td;
}

// Crea una celda <td> con el texto indicado.
function celdaEnlace(id) {
    const enlace = document.createElement('a');
    enlace.href = `/reservas/${id}`;
    enlace.textContent = 'Ver detalle';

    const td = document.createElement('td');
    td.append(enlace);
    return td;
}
