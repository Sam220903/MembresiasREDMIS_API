-- Tabla de Estatus de Miembros
CREATE TABLE MR_EstatusMiembros (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    nombre VARCHAR(120) NOT NULL,
                                    descripcion TEXT NULL
);

-- Tabla de Líneas de Investigación
CREATE TABLE MR_LineaInvestigaciones (
                                         id INT AUTO_INCREMENT PRIMARY KEY,
                                         nombre VARCHAR(120) NOT NULL
);

-- Tabla de Membresias
CREATE TABLE MR_Membresias (
                               id INT AUTO_INCREMENT PRIMARY KEY,
                               nombre VARCHAR(120) NOT NULL,
                               fecha_inicio DATE NULL,
                               fecha_fin DATE NULL,
                               tipo VARCHAR(50) NOT NULL,
                               activa BOOLEAN DEFAULT TRUE NOT NULL -- Añadido para mejor control
);

-- Tabla de Países
CREATE TABLE MR_Paises (
                           id INT AUTO_INCREMENT PRIMARY KEY,
                           nombre VARCHAR(120) NOT NULL,
                           codigo_iso VARCHAR(3) NULL -- Añadido para estandarización
);

-- Tabla de Estados
CREATE TABLE MR_Estados (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            nombre VARCHAR(120) NOT NULL,
                            MR_Paises_id INT NOT NULL,
                            CONSTRAINT MR_Estados_MR_Paises_FK
                                FOREIGN KEY (MR_Paises_id)
                                    REFERENCES MR_Paises (id)
                                    ON DELETE RESTRICT
);

-- Tabla de Tipos de Usuario
CREATE TABLE MR_TiposUsuario (
                                 id INT AUTO_INCREMENT PRIMARY KEY,
                                 nombre VARCHAR(120) NOT NULL,
                                 descripcion TEXT NULL
);

-- Tabla de Universidades
CREATE TABLE MR_Universidades (
                                  id INT AUTO_INCREMENT PRIMARY KEY,
                                  nombre VARCHAR(120) NOT NULL,
                                  MR_Paises_id INT NULL, -- Añadido para relacionar universidad con país
                                  CONSTRAINT MR_Universidades_MR_Paises_FK
                                      FOREIGN KEY (MR_Paises_id)
                                          REFERENCES MR_Paises (id)
                                          ON DELETE SET NULL
);

-- Tabla de Login (Movida antes de Miembros para evitar referencias circulares)
CREATE TABLE MR_Login (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          email VARCHAR(50) NOT NULL UNIQUE,
                          password_hash VARCHAR(255) NOT NULL,
                          ultimo_acceso DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          activo BOOLEAN DEFAULT TRUE NOT NULL,
                          fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          ultima_modificacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de Miembros
CREATE TABLE MR_Miembros (
                             id INT AUTO_INCREMENT PRIMARY KEY,
                             nombre VARCHAR(40) NOT NULL,
                             apellidos VARCHAR(40) NOT NULL,
                             genero VARCHAR(20) NOT NULL,
                             MR_Login_id INT NOT NULL,
                             MR_Universidades_id INT NULL,
                             MR_Estados_id INT NULL,
                             MR_Paises_id INT NULL,
                             MR_EstatusMiembros_id INT NULL,
                             MR_TiposUsuario_id INT NULL,
                             fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                             ultima_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                             CONSTRAINT MR_Miembros_MR_Login_FK
                                 FOREIGN KEY (MR_Login_id)
                                     REFERENCES MR_Login (id)
                                     ON DELETE RESTRICT,
                             CONSTRAINT MR_Miembros_MR_Estados_FK
                                 FOREIGN KEY (MR_Estados_id)
                                     REFERENCES MR_Estados (id)
                                     ON DELETE SET NULL,
                             CONSTRAINT MR_Miembros_MR_EstatusMiembros_FK
                                 FOREIGN KEY (MR_EstatusMiembros_id)
                                     REFERENCES MR_EstatusMiembros (id)
                                     ON DELETE SET NULL,
                             CONSTRAINT MR_Miembros_MR_Paises_FK
                                 FOREIGN KEY (MR_Paises_id)
                                     REFERENCES MR_Paises (id)
                                     ON DELETE SET NULL,
                             CONSTRAINT MR_Miembros_MR_TiposUsuario_FK
                                 FOREIGN KEY (MR_TiposUsuario_id)
                                     REFERENCES MR_TiposUsuario (id)
                                     ON DELETE SET NULL,
                             CONSTRAINT MR_Miembros_MR_Universidades_FK
                                 FOREIGN KEY (MR_Universidades_id)
                                     REFERENCES MR_Universidades (id)
                                     ON DELETE SET NULL
);

-- Tabla de Archivos de Miembros
CREATE TABLE MR_ArchivosMiembros (
                                     id INT AUTO_INCREMENT PRIMARY KEY,
                                     images VARCHAR(100) NULL,
                                     cv VARCHAR(100) NOT NULL,
                                     credencial VARCHAR(100) NOT NULL,
                                     MR_Miembros_id INT NOT NULL,
                                     fecha_subida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                     CONSTRAINT MR_ArchivosMiembros_MR_Miembros_FK
                                         FOREIGN KEY (MR_Miembros_id)
                                             REFERENCES MR_Miembros (id)
                                             ON DELETE CASCADE
);

-- Tabla de relación Miembros-Investigaciones
CREATE TABLE MR_MiembrosInvestigaciones (
                                            id INT AUTO_INCREMENT PRIMARY KEY, -- Añadido ID para mejor control
                                            MR_Miembros_id INT NOT NULL,
                                            MR_LineaInvestigaciones_id INT NOT NULL,
                                            fecha_inicio DATE NOT NULL,
                                            fecha_fin DATE NULL,
                                            CONSTRAINT MR_MiembrosInvestigaciones_MR_LineaInvestigaciones_FK
                                                FOREIGN KEY (MR_LineaInvestigaciones_id)
                                                    REFERENCES MR_LineaInvestigaciones (id)
                                                    ON DELETE RESTRICT,
                                            CONSTRAINT MR_MiembrosInvestigaciones_MR_Miembros_FK
                                                FOREIGN KEY (MR_Miembros_id)
                                                    REFERENCES MR_Miembros (id)
                                                    ON DELETE CASCADE
);

-- Tabla de relación Miembros-Membresias
CREATE TABLE MR_MiembrosMembresias (
                                       id INT AUTO_INCREMENT PRIMARY KEY, -- Añadido ID para mejor control
                                       MR_Miembros_id INT NOT NULL,
                                       MR_Membresias_id INT NOT NULL,
                                       fecha_inicio DATE NOT NULL,
                                       fecha_fin DATE NULL,
                                       estado VARCHAR(20) NOT NULL DEFAULT 'ACTIVA',
                                       CONSTRAINT MR_MiembrosMembresias_MR_Membresias_FK
                                           FOREIGN KEY (MR_Membresias_id)
                                               REFERENCES MR_Membresias (id)
                                               ON DELETE RESTRICT,
                                       CONSTRAINT MR_MiembrosMembresias_MR_Miembros_FK
                                           FOREIGN KEY (MR_Miembros_id)
                                               REFERENCES MR_Miembros (id)
                                               ON DELETE CASCADE
);

-- Tabla de Tokens
CREATE TABLE MR_Tokens (
                           id INT AUTO_INCREMENT PRIMARY KEY,
                           token VARCHAR(255) NOT NULL,
                           token_type VARCHAR(50) NOT NULL,
                           expired BOOLEAN DEFAULT FALSE NOT NULL,
                           revoked BOOLEAN DEFAULT FALSE NOT NULL,
                           MR_Miembros_id INT NOT NULL,
                           fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                           fecha_expiracion DATETIME NOT NULL,
                           CONSTRAINT unique_token
                               UNIQUE (token),
                           CONSTRAINT MR_Tokens_MR_Miembros_FK
                               FOREIGN KEY (MR_Miembros_id)
                                   REFERENCES MR_Miembros (id)
                                   ON DELETE CASCADE
);

-- Índices para optimización
CREATE INDEX idx_miembro_token ON MR_Tokens (MR_Miembros_id);
CREATE INDEX idx_token ON MR_Tokens (token);
CREATE INDEX idx_miembros_email ON MR_Login (email);
CREATE INDEX idx_miembros_nombre ON MR_Miembros (nombre, apellidos);