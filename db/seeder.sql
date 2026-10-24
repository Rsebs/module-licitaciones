-- Seeder de las tablas
-- Uso:  mysql --default-character-set=utf8mb4 modulo_licitaciones < db/seeder.sql

SET NAMES utf8mb4;

INSERT INTO actividades
    (codigo_segmento, segmento, codigo_familia, familia, codigo_clase, clase, codigo_producto, producto)
VALUES
    (43, 'Tecnología de la información y telecomunicaciones', 4321, 'Equipos informáticos y accesorios', 432115, 'Computadores', 43211503, 'Computadores portátiles'),
    (43, 'Tecnología de la información y telecomunicaciones', 4321, 'Equipos informáticos y accesorios', 432115, 'Computadores', 43211507, 'Computadores de escritorio'),
    (43, 'Tecnología de la información y telecomunicaciones', 4321, 'Equipos informáticos y accesorios', 432121, 'Impresoras de computador', 43212105, 'Impresoras láser');
