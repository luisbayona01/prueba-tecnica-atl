-- ============================================================
--  Prueba Tecnica - Base de datos
--  Gestion de Contactos y Telefonos
--  MySQL 8.0+
-- ============================================================

CREATE DATABASE IF NOT EXISTS prueba_contactos
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE prueba_contactos;

-- ------------------------------------------------------------
--  Tabla: contactos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contactos (
    id         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    nombre     VARCHAR(100)     NOT NULL,
    apellido   VARCHAR(100)     NOT NULL,
    email      VARCHAR(190)     NOT NULL,
    created_at TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP
                               ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_contactos_email (email)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Tabla: telefonos
--  Relacion: contactos (1) ---------- (N) telefonos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS telefonos (
    id         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    contacto_id INT UNSIGNED    NOT NULL,
    numero     VARCHAR(30)      NOT NULL,
    created_at TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP
                               ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_telefonos_contacto_numero (contacto_id, numero),
    CONSTRAINT fk_telefonos_contacto
        FOREIGN KEY (contacto_id)
        REFERENCES contactos (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Datos semilla (opcionales, solo para pruebas locales)
-- ------------------------------------------------------------
INSERT INTO contactos (nombre, apellido, email)
VALUES
    ('Luis',   'Bayona',    'luis@example.com'),
    ('Maria',  'Fernandez', 'maria@example.com'),
    ('Carlos', 'Gomez',     'carlos@example.com'),
    ('Ana',    'Lopez',     'ana@example.com'),
    ('Pedro',  'Ramirez',   'pedro@example.com');

INSERT INTO telefonos (contacto_id, numero)
VALUES
    (1, '3001234567'),
    (1, '3109876543'),
    (2, '3201112233'),
    (3, '3004445566'),
    (3, '3157778899'),
    (4, '3112223344'),
    (5, '3185556677');