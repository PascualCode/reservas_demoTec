-- 
-- reservas.sql - Base de datos para el sistema de reservas
-- Para importar la BD ejecuta el siguiente comando en PSHELL:
--   mysql -u root -p --default-character-set=utf8mb4 -e "source database/reservas.sql"
-- Crea la base de datos y las siguientes tablas:
--   1. reserva: almacena la informacion de las reservas realizadas por los huespedes.
--   2. reserva_evento: almacena los eventos relacionados con las reservas, como cambios de estado.
-- Uso de SET SESSION sql_mode = 'TRADITIONAL' para asegurar que se cumplan las restricciones de integridad y evitar errores silenciosos.
--
SET NAMES utf8mb4;
SET SESSION sql_mode = 'TRADITIONAL';
CREATE DATABASE IF NOT EXISTS reservas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE reservas;
DROP TABLE IF EXISTS reserva_evento;
DROP TABLE IF EXISTS reserva;

-- CREACION DE TABLAS
CREATE TABLE reserva (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre_huesped VARCHAR(100) NOT NULL,
  email_huesped VARCHAR(254) NOT NULL,
  nombre_alojamiento VARCHAR(254) NOT NULL,
  fecha_entrada DATE NOT NULL,
  fecha_salida DATE NOT NULL,
  estado ENUM('pendiente', 'confirmada', 'cancelada') NOT NULL DEFAULT 'pendiente',
  importe DECIMAL(10, 2) NOT NULL,
  notas TEXT,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT chk_reserva_fecha CHECK (fecha_entrada < fecha_salida),
  CONSTRAINT chk_reserva_importe CHECK (importe >= 0),
  INDEX idx_reserva_estado (estado),
  INDEX idx_reserva_fecha (fecha_entrada, fecha_salida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reserva_evento (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  reserva_id INT UNSIGNED NOT NULL,
  tipo ENUM('creada', 'confirmada', 'cancelada') NOT NULL,
  descripcion TEXT NOT NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_reserva_evento FOREIGN KEY (reserva_id) REFERENCES reserva(id),
  INDEX idx_reserva_evento (reserva_id, fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERCCION DE DATOS DE MUESTRA (FORZANDO DATOS PARA TESTING)
INSERT INTO reserva (id, nombre_huesped, email_huesped, nombre_alojamiento, fecha_entrada, fecha_salida, estado, importe, notas, fecha_creacion) VALUES
(1,  'Juan Pérez',       'juan.perez@example.com',       'Hotel Extremadura', '2026-10-23', '2026-10-25', 'pendiente',   170.00, NULL,                                                                                        '2026-10-09 18:42:00'),
(2,  'María López',      'maria.lopez@example.com',      'Hotel Neptuno',     '2026-11-12', '2026-11-16', 'pendiente',   480.00, 'Llegaremos tarde 🙂',                                                                       '2026-10-02 21:15:00'),
(3,  'Carlos García',    'carlos.garcia@example.com',    'Hotel Extremadura', '2026-12-04', '2026-12-08', 'pendiente',   340.00, '¡No puedo esperar a nuestra estancia!',                                                     '2026-10-11 10:05:00'),
(4,  'Seán O''Brien',    'sean.obrien@example.com',      'Hotel Neptuno',     '2026-12-18', '2026-12-21', 'pendiente',   360.00, 'Hi, I would like to request a room with a view of the ocean.',                              '2026-10-13 16:30:00'),
(5,  'Luis Martínez',    'luis.martinez@example.com',    'Hotel Neptuno',     '2026-12-25', '2027-01-02', 'pendiente',   960.00, 'Soy alérgico a los gatos, por favor asegúrense de que la habitación esté libre de ellos.',  '2026-10-14 12:20:00'),
(6,  'Sofía Castillo',   'sofia.castillo@example.com',   'Hotel Marte',       '2026-09-18', '2026-09-21', 'confirmada',  285.00, NULL,                                                                                        '2026-08-27 19:48:00'),
(7,  'Łukasz Pérez',     'lukasz.perez@example.com',     'Hotel Marte',       '2026-10-02', '2026-10-05', 'confirmada',  285.00, 'Estamos interesados en una habitación con balcón. ¿Es posible?',                            '2026-09-10 09:12:00'),
(8,  'Íñigo Ramírez',    'inigo.ramirez@example.com',    'Hotel Extremadura', '2026-10-13', '2026-10-18', 'confirmada',  425.00, 'Reserva realizada desde el sitio web.',                                                     '2026-09-22 22:03:00'),
(9,  'Mateo Fernández',  'mateo.fernandez@example.com',  'Hotel Neptuno',     '2026-10-30', '2026-11-13', 'confirmada', 1680.00, 'Cliente interesado en una habitación con vistas al mar.',                                   '2026-09-28 11:37:00'),
(10, 'Isabella Díaz',    'isabella.diaz@example.com',    'Hotel Marte',       '2026-11-20', '2026-11-23', 'confirmada',  285.00, 'Deseamos una habitación tranquila, lejos del ruido.',                                       '2026-10-05 17:54:00'),
(11, 'Santiago Pérez',   'santiago.perez@example.com',   'Hotel Marte',       '2025-12-05', '2025-12-08', 'cancelada',   285.00, NULL,                                                                                        '2025-11-03 20:10:00'),
(12, 'Camila Vargas',    'camila.vargas@example.com',    'Hotel Neptuno',     '2025-11-06', '2025-11-16', 'cancelada',  1200.00, NULL,                                                                                        '2025-10-01 13:25:00'),
(13, 'Begoña Fernández', 'begona.fernandez@example.com', 'Hotel Extremadura', '2026-11-27', '2026-11-29', 'cancelada',   170.00, NULL,                                                                                        '2026-10-06 08:50:00'),
(14, 'Valeria Castillo', 'valeria.castillo@example.com', 'Hotel Alejandría',  '2026-10-14', '2026-10-18', 'cancelada',   560.00, '¿Disponen de plaza de aparcamiento?',                                                       '2026-09-15 14:02:00'),
(15, 'Emiliano Morales', 'emiliano.morales@example.com', 'Hotel Neptuno',     '2026-12-23', '2026-12-26', 'cancelada',   360.00, NULL,                                                                                        '2026-09-30 19:33:00');

INSERT INTO reserva_evento (reserva_id, tipo, descripcion, fecha_creacion) VALUES
(1,  'creada',     'Reserva creada',                            '2026-10-09 18:42:00'),
(2,  'creada',     'Reserva creada',                            '2026-10-02 21:15:00'),
(3,  'creada',     'Reserva creada',                            '2026-10-11 10:05:00'),
(4,  'creada',     'Reserva creada',                            '2026-10-13 16:30:00'),
(5,  'creada',     'Reserva creada',                            '2026-10-14 12:20:00'),
(6,  'creada',     'Reserva creada',                            '2026-08-27 19:48:00'),
(6,  'confirmada', 'Estado cambiado de pendiente a confirmada', '2026-08-28 09:30:00'),
(7,  'creada',     'Reserva creada',                            '2026-09-10 09:12:00'),
(7,  'confirmada', 'Estado cambiado de pendiente a confirmada', '2026-09-10 17:45:00'),
(8,  'creada',     'Reserva creada',                            '2026-09-22 22:03:00'),
(8,  'confirmada', 'Estado cambiado de pendiente a confirmada', '2026-09-23 10:15:00'),
(9,  'creada',     'Reserva creada',                            '2026-09-28 11:37:00'),
(9,  'confirmada', 'Estado cambiado de pendiente a confirmada', '2026-09-29 12:02:00'),
(10, 'creada',     'Reserva creada',                            '2026-10-05 17:54:00'),
(10, 'confirmada', 'Estado cambiado de pendiente a confirmada', '2026-10-07 09:40:00'),
(11, 'creada',     'Reserva creada',                            '2025-11-03 20:10:00'),
(11, 'cancelada',  'Estado cambiado de pendiente a cancelada',  '2025-11-20 11:05:00'),
(12, 'creada',     'Reserva creada',                            '2025-10-01 13:25:00'),
(12, 'cancelada',  'Estado cambiado de pendiente a cancelada',  '2025-10-28 16:50:00'),
(13, 'creada',     'Reserva creada',                            '2026-10-06 08:50:00'),
(13, 'cancelada',  'Estado cambiado de pendiente a cancelada',  '2026-10-12 19:22:00'),
(14, 'creada',     'Reserva creada',                            '2026-09-15 14:02:00'),
(14, 'confirmada', 'Estado cambiado de pendiente a confirmada', '2026-09-16 10:30:00'),
(14, 'cancelada',  'Estado cambiado de confirmada a cancelada', '2026-10-08 13:15:00'),
(15, 'creada',     'Reserva creada',                            '2026-09-30 19:33:00'),
(15, 'cancelada',  'Estado cambiado de pendiente a cancelada',  '2026-10-10 11:48:00');
