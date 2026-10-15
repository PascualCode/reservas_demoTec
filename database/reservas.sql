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
