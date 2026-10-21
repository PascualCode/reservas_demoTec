# DEMO GESTIÓN DE RESERVAS DE ALOJAMIENTO

Prueba técnica con API en PHP nativo, vistas con Twig e interfaz JS

**Despliegue de proyecto:** https://reservaspascual.alwaysdata.net

 
## Cómo desplegarlo en local
 
### Requisitos
 
- PHP 8.2 o superior con las extensiones pdo_mysql y mbstring.
- Composer.
- MySQL 8.0.16+ o MariaDB 10.2+ (necesarios para `CHECK`).

### 1. Clonar e instalar dependencias
 
```bash
git clone https://github.com/PascualCode/reservas_demoTec.git
cd reservas_demoTec
composer install
```
 
### 2. Crear la base de datos
 
El fichero `database/reservas.sql` crea la base de datos `reservas`, las dos tablas y los datos de ejemplo. Desde la raíz del proyecto:
 
```bash
mysql -u root -p --default-character-set=utf8mb4 -e "source database/reservas.sql"
```
 
(En WampServer, si `root` no tiene contraseña, quita el `-p`.)
 
El mismo comando sirve para **reiniciar la BD**: el script borra y vuelve a crear las tablas.
 
### 3. Crear el usuario de la aplicación (mínimo privilegio)
 
La aplicación no se conecta con `root`, sino con un usuario que solo puede leer, insertar y actualizar en esa base de datos. **No tiene permiso de `DELETE`**, porque la aplicación nunca borra nada:
 
```sql
CREATE USER 'reservas_user'@'localhost' IDENTIFIED BY 'una-contraseña-segura';
GRANT SELECT, INSERT, UPDATE ON reservas.* TO 'reservas_user'@'localhost';
```
 
### 4. Configurar la conexión
 
El archivo `.env.example` se usa como plantilla para la creación del propio `.env`

```bash
cp .env.example .env
```
 
Y rellenar los datos en `.env`:
 
```ini
APP_ENV = dev
DB_HOST = 127.0.0.1
DB_PORT = 3306
DB_NAME = reservas
DB_USER = reservas_user
DB_PASS = "una-contraseña-segura"
```
 
- `APP_ENV = dev` muestra el detalle técnico de los errores 500; con `prod` se oculta y se activa la caché de Twig.
- **La contraseña va entre comillas**: el `.env` se lee con `parse_ini_file`, y sin comillas un `;` se interpretaría como inicio de comentario y cortaría el valor.
- `.env` está en `.gitignore`: las credenciales nunca se suben al repositorio.

### 5. Arrancar
 
```bash
php -S localhost:8000 -t public
```
 
Y abrir http://localhost:8000 (redirige a `/reservas`).
 
El parámetro `-t public` es importante: la raíz web es la carpeta `public/`, de modo que `.env`, `src/` o `vendor/` no son accesibles desde el navegador.
 
### 6. Ejecutar los tests

Con el siguiente comando se ejecutan los test creados en phpUnit:
 
```bash
vendor/bin/phpunit
```
 
(En Windows: `vendor\bin\phpunit`.)
 

## Como se Desplegó en red
 
Para el despliegue en red se ha eleigido la plataforma de **alwaysdata** por contar con un hosting gratuito con versiones de PHP, MariaDB y Composer compatibles con el proyecto. Ademas cuenta con acceso SSH para poder configurarlo desde terminal.

https://reservaspascual.alwaysdata.net
 
Pasos que se siguieron:
 
1. Se crea el Usuario y BD desde panel.
2. Por SSH se clona el repo fuera de `www/` y se instala sin dependencias de desarrollo:
```bash
   git clone https://github.com/PascualCode/reservas_demoTec.git
   cd reservas_demoTec
   composer install --no-dev --optimize-autoloader
```
3. Se crean el `.env` con `APP_ENV = prod` y los datos de la base de datos, y se protegen con `chmod 600 .env`.
4. Importar los datos. Al ser un hosting compartido la base de datos se crea desde el panel y tiene otro nombre, por eso hay que eliminar las líneas `CREATE DATABASE` y `USE` del script, sin modificar el fichero para que no de un error y se puedan crear las tablas:
```bash
   sed -e '/^CREATE DATABASE/d' -e '/^USE /d' database/reservas.sql | mysql -h <host> -u <usuario> -p --default-character-set=utf8mb4 <base_de_datos>
```
5. En el panel, se configura el sitio como PHP con la **raíz en `public/`** y redirección forzada a HTTPS.
`public/.htaccess` envía a `index.php` todas las peticiones que no correspondan a un fichero real.
 
**Para actualizar:**
 
```bash
git pull
composer install --no-dev --optimize-autoloader
rm -rf var/cache
```

(Es necesario borrar la caché de Twig porque en produccion no comprueba si las plantillas han cambiado.) 
 
**Limitación del hosting:** el panel solo permite asignar "todos los privilegios", "solo lectura" o "ninguno" sobre una base de datos, así que en producción no se puede replicar el usuario sin `DELETE` que se usa en local.

## Estructura del proyecto
 
```
├── database/reservas.sql        DDL + datos de ejemplo
├── public/                      Raíz web (lo único accesible desde internet)
│   ├── index.php                Front controller: configuración, dependencias y rutas
│   ├── .htaccess                Reescritura de URLs para Apache
│   ├── css/app.css
│   └── js/
│       ├── api.js               Llamadas a la API y gestión de errores (común)
│       ├── ui.js                Avisos y formato de fechas/importes (común)
│       ├── listado.js           Filtros del listado
│       ├── crear.js             Formulario de creación
│       └── detalle.js           Confirmar / cancelar
├── src/
│   ├── Config/Config.php                      Lee el .env y da acceso a sus valores
│   ├── Controller/
│   │   ├── ReservaApiController.php           Endpoints JSON: lee la petición, llama al servicio y responde
│   │   └── ReservaWebController.php           Páginas HTML: pide los datos al servicio y renderiza Twig
│   ├── Database/ConnectionFactory.php         Crea la conexión PDO con opciones seguras
│   ├── Domain/
│   │   ├── EstadoReserva.php                  Estados posibles y regla de transiciones
│   │   ├── ReservaFiltros.php                 Filtros del listado ya validados
│   │   ├── ReservaNotFoundException.php       La reserva no existe
│   │   └── TransicionNoPermitidaException.php El cambio de estado no está permitido
│   ├── Http/
│   │   ├── Router.php                         Asocia método + ruta con su controlador (404 / 405)
│   │   ├── JsonResponse.php                   Envía JSON con cabeceras de seguridad
│   │   ├── HtmlResponse.php                   Envía HTML con CSP y cabeceras de seguridad
│   │   ├── HttpException.php                  Error con código HTTP y mensaje para el usuario
│   │   ├── ValidationException.php            Error 400 con los errores de cada campo
│   │   └── ErrorHandler.php                   Convierte cualquier excepción en respuesta JSON o HTML
│   ├── Repository/ReservaRepository.php       Todas las consultas SQL
│   ├── Service/ReservaService.php             Reglas de negocio y transacciones
│   └── Validation/
│       ├── ReservaValidator.php               Valida y limpia los datos de una reserva nueva
│       └── Fecha.php                          Comprueba que una fecha AAAA-MM-DD existe
├── templates/                   Plantillas Twig (base + herencia)
└── tests/                       Tests de PHPUnit
```

## API
 
Todas las respuestas de la API son en formato JSON. El formato de error siempre tienen la misma forma:
 
```json
{ "error": "Datos no válidos", "errores": { "email_huesped": "El email no es válido" } }
```
 
### Endpoints
 
| Método | Ruta | Descripción | Respuestas |
|---|---|---|---|
| `GET` | `/api/reservas` | Listado con filtros | 200, 400 |
| `GET` | `/api/reservas/{id}` | Detalle + historial de eventos | 200, 404 |
| `POST` | `/api/reservas` | Crear una reserva | 201 (+ cabecera `Location`), 400 |
| `PATCH` | `/api/reservas/{id}/estado` | Confirmar o cancelar | 200, 400, 404, 409 |
 
También se usa `404` para rutas que no existen y `405` si la ruta existe pero el método no.
 
### Filtros del listado
 
Los filtros tienen la capacidad de ser combinables: `GET /api/reservas?estado=pendiente&desde=2026-11-01&hasta=2026-11-30&q=perez`
 
| Parámetro | Valor | Significado |
|---|---|---|
| `estado` | `pendiente`, `confirmada` o `cancelada` | Reservas en ese estado |
| `desde` / `hasta` | `AAAA-MM-DD` | Reservas con **al menos una noche** dentro del rango |
| `q` | texto (máx. 100) | Búsqueda por nombre del huésped, sin distinguir mayúsculas ni tildes |
 
La respuesta viene en el formato JSON ya indicado de esta forma: `{ "reservas": [ ... ], "total": 5 }`, ordenadas por fecha de entrada.
 
### Crear una reserva
 
```bash
curl -i -X POST http://localhost:8000/api/reservas \
  -H "Content-Type: application/json" \
  -d '{"nombre_huesped":"Ana Ruiz","email_huesped":"ana@example.com","nombre_alojamiento":"Hotel Marte","fecha_entrada":"2026-12-10","fecha_salida":"2026-12-12","importe":"190.50","notas":"Llegaremos tarde"}'
```
 
Validación en el servidor:
 
| Campo | Reglas |
|---|---|
| `nombre_huesped` | Obligatorio, max. 100 caracteres |
| `email_huesped` | Obligatorio, email válido, max. 254 |
| `nombre_alojamiento` | Obligatorio, max. 254 |
| `fecha_entrada` | Obligatoria, `AAAA-MM-DD`, fecha real, no anterior a hoy |
| `fecha_salida` | Obligatoria, `AAAA-MM-DD`, fecha real, posterior a la entrada |
| `importe` | Obligatorio, ≥ 0, hasta 8 cifras enteras y 2 decimales, con punto decimal |
| `notas` | Opcional, max. 1000 |
 
Otros campos se ignoran. Además una reserva nueva **siempre** empieza en `pendiente`, aunque el JSON diga otra cosa.
 
### Cambiar el estado
 
```bash
curl -i -X PATCH http://localhost:8000/api/reservas/1/estado \
  -H "Content-Type: application/json" \
  -d '{"estado":"cancelada"}'
```
 
Solo se permiten los siguentes cambios de estado:
 
```
pendiente ──► confirmada ──► cancelada
    └──────────────────────────►┘
```
 
`cancelada` es un estado final. Una transición no permitida (por ejemplo, cancelar una reserva ya cancelada) responde **409 Conflict**; un estado que no existe o `pendiente` como destino responde **400**.
 
Cada creación y cada cambio de estado añade una fila a `reserva_evento` en la **misma transacción**: o se guardan las dos cosas o ninguna.
 
## Decisiones

### Organización de las clases
 
Cada capa tiene una sola responsabilidad y solo conoce a la de debajo:
 
```
Router → Controller → Service → Repository → PDO
                         └→ Validator
```
- **Router** : Mira el método y la URL, invocandolo a través de `apiController->metodo(...)`. Solo reparte peticiones
- **Controller** : Lee la petición HTTP sacando el JSON y comprobando que sea valido (400 si no lo es). Luego llama al servicio y decide el formato y el código de la respuesta. Convierte las excepciones de dominio en códigos HTTP (404, 409).
- **Service** : Las reglas de negocio. Valida los datos, decide el estado inicial, aplica las transiciones y abre las transacciones.
- **Repository** : Único sitio con SQL. Si se cambiara la base de datos, solo cambiaría esta clase.
- **Validator** : comprueba los datos de entrada y devuelve solo los campos permitidos, ya limpios.
- **Domain** : la regla de las transiciones vive en el propio enum (`puedeCambiarA()`), en un solo sitio.
Los dos controladores (API y web) **reutilizan el mismo servicio**: la lógica no se duplica, solo cambia la presentación.
- **PDO** : envía el SQL a MySQL.

**Motivos de esta separación**:

a. El servicio no sabe nada de HTTP, y por eso lo usan dos controladores. Por ejemplo, si se hiciera un script de consola que listara las reservas, también podría usar el servicio sin cambiar nada.
b. Cada cambio afecta a un solo sitio.
c. Se puede probar por piezas.
 
### Por qué MySQL/MariaDB
 
- Es lo que se pide preferentemente.
- Los datos son relacionales.
- Hacen falta transacciones y bloqueo de filas (`SELECT ... FOR UPDATE`).
- Las restricciones `CHECK` permiten que la propia base de datos rechace datos imposibles.

### Base de datos
 
- **`utf8mb4` / `utf8mb4_unicode_ci`**: Admite cualquier carácter y es compatible con MySQL y MariaDB.
- **`sql_mode = 'TRADITIONAL'`** en el script y en cada conexión: WampServer viene sin modo estricto, y sin él, MySQL guardaría datos inválidos (fechas imposibles, textos recortados) con solo un aviso. Así no depende de la configuración del servidor.
- **`importe` es `DECIMAL(10,2)`** y la API lo devuelve como texto (`"190.50"`): Esto es para no perder precisión.
- **`fecha_creacion` es `DATETIME`**, no `TIMESTAMP`: No tiene el límite de año ni conversiones de zona horaria.
- **Sin `ON DELETE CASCADE`**: La aplicación nunca borra reservas (se cancelan).
- **Índices**: por `estado`, por `(fecha_entrada, fecha_salida)` para los filtros, y `(reserva_id, fecha_creacion)` en los eventos para cargar el historial ordenado.

### El filtro de fechas
 
`desde` y `hasta` se tratan como días incluidos, y una reserva aparece si **tiene al menos una noche dentro del rango**:
 
```sql
fecha_salida > :desde AND fecha_entrada <= :hasta
```
 
- El día de salida no cuenta como noche ya que ese día el huésped se va por la mañana.
- Se descarta tratar el rango como una estancia porque impedia consultar un solo día (`desde = hasta`), que es un caso de uso natural.

### Búsqueda por nombre
 
- Usa `LIKE '%texto%'` sobre `nombre_huesped`, con los comodines `%` y `_` escapados. Si alguien busca `%`, se busca el carácter `%`, no "cualquier cosa". Así la búsqueda es correcta y no se pueden construir patrones costosos (como `%_%_%_%`) para cargar la base de datos.
- Con el uso de `utf8mb4_unicode_ci`, no se distinguen mayúsculas ni tildes: "perez" encuentra "Pérez". Efecto secundario conocido: también trata la `ñ` como `n`.

### Concurrencia
 
El cambio de estado lee el estado actual con `SELECT ... FOR UPDATE` dentro de una transacción. Si dos personas cancelan la misma reserva a la vez, la segunda espera a la primera y después recibe un 409, en lugar de registrar dos cancelaciones en el historial.
 
### Manejo de errores centralizado
 
Todas las excepciones llegan a un único `ErrorHandler` (`src/Http/ErrorHandler.php`):
 
- **Errores esperados** (`HttpException`: 400, 404, 405, 409): se responden con su mensaje, que está escrito para el usuario.
- **Errores inesperados** : el detalle va al log del servidor y el cliente recibe un 500 genérico. Solo en `APP_ENV = dev` se añade el detalle.
- Responde en **JSON** a las rutas `/api/...` y con una **página HTML** al resto.
- Si falla el propio Twig, devuelve un HTML mínimo.
- Los avisos `warnings` de PHP se convierten en excepciones, para que no pasen desapercibidos. Los avisos de deprecación solo se registran en el log: no deben tumbar una petición si el servidor tiene un PHP más nuevo.

### Vistas con Twig
 
- **Plantilla base con herencia** (`base.html.twig`) con bloques `title`, `content` y `scripts`.
- **Autoescape activado** . **No se usa `|raw` en ningún sitio.** Las notas usan `|nl2br`, que primero escapa el texto y después añade los `<br>`.
- **Formato en la plantilla**. Fechas con `|date('d/m/Y')` e importes con `|number_format(2, ',', '.')`.
- `strict_variables` activado de tal forma que una variable mal escrita da un error en vez de una cadena vacía silenciosa.
- **Caché de plantillas compiladas** en producción (`var/cache/twig`), pero desactivada en desarrollo para ver los cambios al momento.

### Frontend
 
- **Módulos ES** (`<script type="module">`) permite el uso de `import/export`, que cada fichero use sus propias variables y se ejecute cuando el HTML ya esta cargado. Sin JavaScript en el HTML.
- **`api.js` centraliza las llamadas** `fetch`. Así que `pedirApi()` comprueba `response.ok` y convierte cualquier fallo en un `ErrorApi` con un mensaje para el usuario.
- **Todo se pinta con `textContent`, nunca con `innerHTML`**. Este es el autoescape de JS. Un nombre como `<img src=x onerror=alert(1)>` se muestra como texto.
- **El listado se pinta dos veces**. La tabla se pinta con Twig al cargar la página y con JS al filtrar. Así la página llega ya con los datos en una sola petición, y el filtrado no recarga..
- **Peticiones fuera de orden**. Se encontró que, si se lanzan dos filtrados seguidos y el primero tarda más, su respuesta podía pisar a la del segundo. Ahora cada petición lleva un número y solo se pinta la última.
- **Validación en cliente**. Atributos HTML con los mismos límites que el servidor, más una comprobación en JS de que la salida es posterior a la entrada. Es solo para comodidad del usuario, aunque la validación que cuenta es la del servidor. Los errores que devuelve la API se muestran junto a su campo.
- **Feedback**. Se añaden avisos de "Cargando…"/"Guardando…", éxito y error en zonas `role="status"`, y los botones se desactivan mientras hay una petición en curso.
- **Confirmación** antes de confirmar o cancelar (`confirm()`).
- Los botones del detalle se muestran según el estado, pero si la página está desactualizada (por ejemplo, la reserva se canceló en otra pestaña), la API responde 409 y se muestra el mensaje.
- **CSS responsive** básico pensado para adaptarse a cualquier pantalla. En pantallas estrechas la tabla se desplaza en horizontal dentro de su caja.

## Seguridad
 
| Riesgo | Medida |
|---|---|
| Inyección SQL | Todas las consultas son preparadas, con `PDO::ATTR_EMULATE_PREPARES = false`. Las únicas partes dinámicas del SQL son fragmentos fijos del propio código. |
| Asignación masiva | El validador devuelve solo los 7 campos permitidos; `id`, `estado` o `fecha_creacion` del cliente se ignoran. |
| Datos inválidos | Validación completa en el servidor, más `CHECK` y `sql_mode` estricto en la base de datos. Los parámetros de la URL deben ser texto UTF-8 válido. |
| XSS | Autoescape de Twig + cabecera Content-Security-Policy `default-src 'self'` que impide ejecutar scripts incrustados aunque se colara uno. |
| Clickjacking | `frame-ancestors 'none'` y `X-Frame-Options: DENY`. |
| Fuga de información | Errores internos solo en el log, `display_errors` desactivado, mensaje genérico en producción. Se ha comprobado que PHP oculta la contraseña de PDO en las trazas (`SensitiveParameterValue`). |
| Datos personales en caché | `Cache-Control: no-store` en todas las respuestas. |
| Ficheros sensibles | La raíz web es `public/`: `.env`, `src/` y `vendor/` no son accesibles. `.env` fuera del repositorio y con permisos `600` en el servidor. |
| Privilegios | El usuario de la base de datos no tiene `DELETE` (pero por limitaciones de creacion si lo tiene en alwaysdata). |
| Otras cabeceras | `X-Content-Type-Options: nosniff`, `Referrer-Policy: same-origin`. |
 
**Limitación**. La API no tiene autenticación y expone datos personales (nombres y emails).
 
## Tests
 
```bash
vendor/bin/phpunit
```
 
36 tests en dos grupos:
 
- **EstadoReservaTest**: las 9 combinaciones de estado actual → estado nuevo, para comprobar que solo se permiten las transiciones válidas.
- **ReservaValidatorTest**: datos válidos (incluido que los campos no permitidos desaparecen), obligatorios, fechas (coherencia, pasado, fechas imposibles, formatos incorrectos), email, importes válidos y no válidos (negativos, coma decimal, 3 decimales, arrays, booleanos…) y longitudes con caracteres multibyte (`ñ`).
Se probó primero la lógica que no depende de la base de datos, que es donde están las reglas. El validador recibe la fecha de "hoy" por el constructor, para que los tests den el mismo resultado cualquier día que se ejecuten.
 
Los tests **encontraron un bug real**. La comprobación de la fecha de salida usaba por error la fecha de entrada, así que cuando la entrada era anterior a hoy o tenía un formato incorrecto se añadía también un error falso en `fecha_salida`. El arreglo se apunto con un commit.

## Qué haría diferente con más tiempo
 
- Autenticación solo para el personal que gestiona reservas.
- Rate limiting para impedir la enumeración masiva de reservas.
- Si la autenticación fuera con cookies, añadir protección CSRF.
- Paginación y límite de resultados en el listado.
- En el listado no se cargan los eventos de cada reserva (evita N consultas extra); si hicieran falta, se cargarían todos en una sola consulta.
- Tests de integración del repositorio contra una base de datos de pruebas, y tests de la API.
- Tests automáticos del frontend en navegador.
- Integración continua con GitHub Actions por ejemplo para que ejecute los tests en cada push.
- Migraciones versionadas en lugar de un único `.sql`.
- Versionar los ficheros CSS/JS (`app.css?v=2`) para que los navegadores no usen una versión antigua tras un despliegue.
- Un `<dialog>` propio en lugar de `confirm()`.
- Tras un 409 en el detalle, volver a pedir la reserva y repintarla con su estado real.
- Cancelar también desde el listado.
- Un modelo de alojamientos (y habitaciones) con control de disponibilidad, para impedir reservas solapadas del mismo alojamiento.


 
## Escalabilidad
 
Creo que para 1000 reservas/hora la carga en sí la aguanta sin problemas. El problema real es el acumulado superando los millones de reservas al año. Por tanto los cambios que propongo son:
 
1. **Paginación obligatoria del listado.** Ahora devuelve todas las reservas; con millones de filas, cada consulta y cada respuesta crecerían sin límite.
2. **Búsqueda por nombre.** `LIKE '%texto%'` no puede usar índices y recorre toda la tabla. Pasaría a un motor de búsqueda.
3. **Índices para las consultas mas usadas**.
4. **Archivar o particionar** las reservas antiguas para que la tabla activa no crezca indefinidamente.
5. **Escalar horizontalmente PHP**: la aplicación no guarda estado entre peticiones (no hay sesiones), así que se pueden poner varias instancias detrás de un balanceador. Ya se usan OPcache, la caché de Twig y el autoload optimizado de Composer.
6. **Tareas lentas fuera de la petición**: si se añadieran emails de confirmación, irían a una cola de trabajos para no hacer esperar al usuario.
7. **Concurrencia**: Control de disponibilidad, las reservas solapadas se evitarían también con bloqueo o con una restricción en la base de datos.
8. **Monitorización y logs centralizados, rate limiting y copias de seguridad automáticas de la base de datos**.
 
## Autor : José Manuel Pascual Mora
