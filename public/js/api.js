//Error de la API con lo necesario para mostrárselo al usuario.
export class ErrorApi extends Error {
    constructor(mensaje, status = 0, errores = {}) {
        super(mensaje);
        this.status = status;     // 0 = no se llegó al servidor (fallo de red)
        this.errores = errores;   // errores por campo de una respuesta 400
    }
}

//Llama a la API y devuelve el JSON de la respuesta.
//Si falla la red o la API responde con un error (4xx/5xx), lanza un ErrorApi.
export async function pedirApi(url, { method = 'GET', datos = null } = {}) {
    const opciones = { method, headers: { Accept: 'application/json' } };
    if (datos !== null) {
        opciones.headers['Content-Type'] = 'application/json';
        opciones.body = JSON.stringify(datos);
    }

    let respuesta;
    try {
        respuesta = await fetch(url, opciones);
    } catch {
        throw new ErrorApi('No se ha podido conectar con el servidor. Comprueba tu conexión.');
    }

    let cuerpo = null;
    try {
        cuerpo = await respuesta.json();
    } catch {
        // La respuesta no era JSON (por ejemplo, un error del propio servidor web)
    }

    if (!respuesta.ok) {
        // Valores por defecto
        let mensaje = 'Error inesperado del servidor';
        let errores = {};

        if (cuerpo !== null && cuerpo.error) {
            mensaje = cuerpo.error;
        }
        if (cuerpo !== null && cuerpo.errores) {
            errores = cuerpo.errores;
        }

        throw new ErrorApi(mensaje, respuesta.status, errores);
    }

    return cuerpo;
}
