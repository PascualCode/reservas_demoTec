// Muestra un mensaje en el elemento indicado. tipo: 'info' | 'exito' | 'error' */
export function mostrarAviso(elemento, texto, tipo = 'info') {
    elemento.textContent = texto;
    elemento.className = `aviso aviso--${tipo}`;
}

// '2026-10-23' → '23/10/2026' (sin pasar por Date, que cambiaria el día segun la zona horaria)
export function formatearFecha(fecha) {
    const [anio, mes, dia] = fecha.split('-');
    return `${dia}/${mes}/${anio}`;
}

// '1200.00' → '1.200,00 €' (igual que el filtro number_format de Twig)
export function formatearImporte(importe) {
    const formato = new Intl.NumberFormat('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
        useGrouping: 'always',
    });
    return formato.format(Number(importe)) + ' €';
}

//'pendiente' → 'Pendiente'
export function capitalizar(texto) {
    return texto.charAt(0).toUpperCase() + texto.slice(1);
}

// '2026-10-20 18:13:00' → '20/10/2026 18:13'
export function formatearFechaHora(fechaHora) {
    const [fecha, hora] = fechaHora.split(' ');
    return `${formatearFecha(fecha)} ${hora.slice(0, 5)}`;
}
