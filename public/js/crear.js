import { pedirApi, ErrorApi } from './api.js';
import { mostrarAviso } from './ui.js';

const formulario = document.querySelector('#form-crear');
const botonCrear = formulario.querySelector('button[type="submit"]');
const aviso = document.querySelector('#aviso-crear');

//Activa el formulario de creación.
//alCrear: función que se llama cuando la reserva se ha creado (por ejemplo, para recargar el listado).
export function iniciarCreacion(alCrear) {
    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        limpiarErrores();

        const datos = Object.fromEntries(new FormData(formulario));

        // Validación en cliente que el HTML no puede expresar
        if (datos.fecha_entrada && datos.fecha_salida && datos.fecha_salida <= datos.fecha_entrada) {
            mostrarErrores({ fecha_salida: 'Debe ser posterior a la fecha de entrada' });
            return;
        }

        mostrarAviso(aviso, 'Guardando…');
        botonCrear.disabled = true;

        try {
            const reserva = await pedirApi('/api/reservas', { method: 'POST', datos });
            formulario.reset();
            mostrarAviso(aviso, `Reserva ${reserva.id} creada correctamente. `, 'exito');
            const enlace = document.createElement('a');
            enlace.href = `/reservas/${reserva.id}`;
            enlace.textContent = 'Ver detalle';
            aviso.append(enlace);
            alCrear();
        } catch (error) {
            if (error instanceof ErrorApi) {
                mostrarErrores(error.errores);
                mostrarAviso(aviso, error.message, 'error');
            } else {
                console.error(error);
                mostrarAviso(aviso, 'Error inesperado en la página', 'error');
            }
        } finally {
            botonCrear.disabled = false;
        }
    });
}

//Pinta cada mensaje de error junto a su campo.
function mostrarErrores(errores) {
    for (const [campo, mensaje] of Object.entries(errores)) {
        const hueco = document.querySelector(`#error-${campo}`);
        const input = formulario.elements[campo];
        if (hueco !== null) {
            hueco.textContent = mensaje;
        }
        if (input !== undefined) {
            input.setAttribute('aria-invalid', 'true');
        }
    }
}

// Borra los errores de un envío anterior.
function limpiarErrores() {
    for (const hueco of formulario.querySelectorAll('.error-campo')) {
        hueco.textContent = '';
    }
    for (const input of formulario.querySelectorAll('[aria-invalid]')) {
        input.removeAttribute('aria-invalid');
    }
}
