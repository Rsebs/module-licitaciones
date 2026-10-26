-- Módulo de licitaciones — estructura de la base de datos
-- Uso:  mysql -u root -p < db/init.sql

CREATE DATABASE IF NOT EXISTS `modulo_licitaciones`;
USE modulo_licitaciones;

-- modulo_licitaciones.actividades definition
CREATE TABLE IF NOT EXISTS `actividades` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `codigo_segmento` INT NOT NULL,
  `segmento` VARCHAR(200) NOT NULL,
  `codigo_familia` INT NOT NULL,
  `familia` VARCHAR(200) NOT NULL,
  `codigo_clase` INT NOT NULL,
  `clase` VARCHAR(200) NOT NULL,
  `codigo_producto` INT NOT NULL,
  `producto` VARCHAR(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_actividades_producto` (`codigo_producto`),
  KEY `idx_actividades_nombre` (`producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- modulo_licitaciones.ofertas definition
CREATE TABLE IF NOT EXISTS `ofertas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `consecutivo` VARCHAR(200) NOT NULL,
  `objeto` VARCHAR(150) NOT NULL,
  `descripcion` VARCHAR(400) NOT NULL,
  `moneda` VARCHAR(3) NOT NULL,
  `presupuesto` DECIMAL(15,2) NOT NULL,
  `actividad_id` INT NOT NULL,
  `fecha_inicio` DATE NOT NULL,
  `hora_inicio` TIME NOT NULL,
  `fecha_cierre` DATE NOT NULL,
  `hora_cierre` TIME NOT NULL,
  `estado` VARCHAR(20) NOT NULL DEFAULT 'Activo',
  `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `actualizado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ofertas_consecutivo` (`consecutivo`),
  KEY `idx_ofertas_actividad` (`actividad_id`),
  CONSTRAINT `fk_ofertas_actividad` FOREIGN KEY (`actividad_id`) REFERENCES `actividades` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- modulo_licitaciones.ofertas_documentos definition
CREATE TABLE IF NOT EXISTS `ofertas_documentos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `licitacion_id` INT NOT NULL,
  `titulo` VARCHAR(100) NOT NULL,
  `descripcion` VARCHAR(200) NOT NULL,
  `archivo` VARCHAR(200) NOT NULL,
  `nombre_original` VARCHAR(200) DEFAULT NULL, -- Me tomé la libertad de agregar este campo para mostrar el nombre original del archivo
  `creado_en` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  KEY `idx_documentos_oferta` (`licitacion_id`),
  CONSTRAINT `fk_documentos_oferta` FOREIGN KEY (`licitacion_id`) REFERENCES `ofertas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
