SET NAMES 'utf8mb4';
SET CHARACTER SET utf8mb4;

create table MR_EstatusMiembros
(
    id          int auto_increment
        primary key,
    nombre      varchar(120) not null,
    descripcion text         null
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create table MR_LineaInvestigaciones
(
    id     int auto_increment
        primary key,
    nombre varchar(120) not null,
    activo tinyint(1) not null default 1
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create table MR_Membresias
(
    id           int auto_increment
        primary key,
    nombre       varchar(120)         not null,
    tipo         varchar(50)          not null,
    activo        tinyint(1) default 1 not null
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create table MR_Paises
(
    id         int auto_increment
        primary key,
    nombre     varchar(120) not null,
    codigo_iso varchar(3)   null
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create table MR_Estados
(
    id           int auto_increment
        primary key,
    nombre       varchar(120) not null,
    MR_Paises_id int          null,
    constraint MR_Estados_MR_Paises_FK
        foreign key (MR_Paises_id) references MR_Paises (id)
            on delete set null
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create table MR_TiposUsuario
(
    id          int auto_increment
        primary key,
    nombre      varchar(120) not null,
    descripcion text         null
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create table MR_Universidades
(
    id           int auto_increment
        primary key,
    nombre       varchar(120) not null,
    MR_Paises_id int          null,
    constraint MR_Universidades_MR_Paises_FK
        foreign key (MR_Paises_id) references MR_Paises (id)
            on delete set null
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create table MR_Miembros
(
    id                    int auto_increment
        primary key,
    nombre                varchar(40)                        not null,
    apellidos             varchar(40)                        not null,
    genero                varchar(20)                        not null,
    codigo                varchar(6)                         null,
    verificado            tinyint(1) default 0               not null,
    activo               tinyint(1) default 1                 not null,
    MR_Universidades_id   int                                null,
    MR_Estados_id         int                                null,
    MR_Paises_id          int                                null,
    MR_EstatusMiembros_id int                                null,
    MR_TiposUsuario_id    int                                null,
    fecha_registro        datetime default CURRENT_TIMESTAMP not null,
    ultima_actualizacion  datetime default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint MR_Miembros_MR_Estados_FK
        foreign key (MR_Estados_id) references MR_Estados (id)
            on delete set null,
    constraint MR_Miembros_MR_EstatusMiembros_FK
        foreign key (MR_EstatusMiembros_id) references MR_EstatusMiembros (id)
            on delete set null,
    constraint MR_Miembros_MR_Paises_FK
        foreign key (MR_Paises_id) references MR_Paises (id)
            on delete set null,
    constraint MR_Miembros_MR_TiposUsuario_FK
        foreign key (MR_TiposUsuario_id) references MR_TiposUsuario (id)
            on delete set null,
    constraint MR_Miembros_MR_Universidades_FK
        foreign key (MR_Universidades_id) references MR_Universidades (id)
            on delete set null
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create table MR_ArchivosMiembros
(
    id             int auto_increment
        primary key,
    images         varchar(100)                       null,
    cv             varchar(100)                       not null,
    credencial     varchar(100)                       null,
    MR_Miembros_id int                                not null,
    fecha_subida   datetime default CURRENT_TIMESTAMP not null,
    constraint MR_ArchivosMiembros_MR_Miembros_FK
        foreign key (MR_Miembros_id) references MR_Miembros (id)
            on delete cascade
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create table MR_Login
(
    id                  int auto_increment
        primary key,
    MR_Miembros_id      int                                  not null,
    email               varchar(50)                          not null,
    password_hash       varchar(255)                         not null,
    ultimo_acceso       datetime   default CURRENT_TIMESTAMP not null,
    activo              tinyint(1) default 1                 not null,
    fecha_creacion      datetime   default CURRENT_TIMESTAMP not null,
    ultima_modificacion datetime   default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    constraint email
        unique (email),
    constraint MR_Login_MR_Miembros_FK
        foreign key (MR_Miembros_id) references MR_Miembros (id)
            on delete cascade
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create index idx_miembros_email
    on MR_Login (email);

create index idx_miembros_nombre
    on MR_Miembros (nombre, apellidos);

create table MR_MiembrosInvestigaciones
(
    id                         int auto_increment
        primary key,
    MR_Miembros_id             int  not null,
    MR_LineaInvestigaciones_id int  not null,
    constraint MR_MiembrosInvestigaciones_MR_LineaInvestigaciones_FK
        foreign key (MR_LineaInvestigaciones_id) references MR_LineaInvestigaciones (id),
    constraint MR_MiembrosInvestigaciones_MR_Miembros_FK
        foreign key (MR_Miembros_id) references MR_Miembros (id)
            on delete cascade
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create table MR_MiembrosMembresias
(
    id               int auto_increment
        primary key,
    MR_Miembros_id   int                          not null,
    MR_Membresias_id int                          not null,
    fecha_inicio     date                         not null,
    fecha_fin        date                         null,
    estado           varchar(20) default 'ACTIVA' not null,
    constraint MR_MiembrosMembresias_MR_Membresias_FK
        foreign key (MR_Membresias_id) references MR_Membresias (id),
    constraint MR_MiembrosMembresias_MR_Miembros_FK
        foreign key (MR_Miembros_id) references MR_Miembros (id)
            on delete cascade
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create table MR_SolicitudesMembresia
(
    id               int auto_increment
        primary key,
    MR_Miembros_id   int                                                                   not null,
    MR_Membresias_id int                                                                   not null,
    estado           enum ('PENDIENTE', 'APROBADA', 'RECHAZADA') default 'PENDIENTE'       not null,
    fecha_solicitud  datetime                                    default CURRENT_TIMESTAMP not null,
    fecha_respuesta  datetime                                                              null,
    comentarios      text                                                                  null,
    revisado_por     int                                                                   null,
    constraint MR_SolicitudesMembresia_MR_Membresias_FK
        foreign key (MR_Membresias_id) references MR_Membresias (id),
    constraint MR_SolicitudesMembresia_MR_Miembros_FK
        foreign key (MR_Miembros_id) references MR_Miembros (id)
            on delete cascade,
    constraint MR_SolicitudesMembresia_Revisor_FK
        foreign key (revisado_por) references MR_Miembros (id)
            on delete set null
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create index idx_solicitud_estado
    on MR_SolicitudesMembresia (estado);

create table MR_Tokens
(
    id               int auto_increment
        primary key,
    token            varchar(255)                         not null,
    token_type       varchar(50)                          not null,
    expired          tinyint(1) default 0                 not null,
    revoked          tinyint(1) default 0                 not null,
    MR_Miembros_id   int                                  not null,
    fecha_creacion   datetime   default CURRENT_TIMESTAMP not null,
    fecha_expiracion datetime                             not null,
    constraint unique_token
        unique (token),
    constraint MR_Tokens_MR_Miembros_FK
        foreign key (MR_Miembros_id) references MR_Miembros (id)
            on delete cascade
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

create index idx_miembro_token
    on MR_Tokens (MR_Miembros_id);

create index idx_token
    on MR_Tokens (token);

-- Datos de prueba para la base de datos
-- Datos para MR_EstatusMiembros
INSERT INTO MR_EstatusMiembros (nombre, descripcion) VALUES
    ('Activo', 'Miembro con todos los derechos vigentes'),
    ('Inactivo', 'Miembro temporalmente inactivo'),
    ('Honorario', 'Miembro distinguido con status honorario'),
    ('Suspendido', 'Miembro temporalmente suspendido');

-- Datos para MR_LineaInvestigaciones
INSERT INTO MR_LineaInvestigaciones (nombre) VALUES
    ('Inteligencia Artificial'),
    ('Desarrollo Sostenible'),
    ('Energías Renovables'),
    ('Biotecnología'),
    ('Nanotecnología');

-- Datos para MR_Membresias
INSERT INTO MR_Membresias (nombre, tipo, activo) VALUES
    ('Membresía Básica', 'BASICA', 1),
    ('Membresía Premium', 'PREMIUM', 1),
    ('Membresía Estudiante', 'ESTUDIANTE', 1),
    ('Membresía Corporativa', 'CORPORATIVA', 1);

-- Datos para MR_Paises
INSERT INTO MR_Paises (nombre, codigo_iso) VALUES
    ('México', 'MEX'),
    ('Estados Unidos', 'USA'),
    ('España', 'ESP'),
    ('Argentina', 'ARG'),
    ('Colombia', 'COL');

-- Datos para MR_Estados (32 estados de México)
INSERT INTO MR_Estados (nombre, MR_Paises_id) VALUES
    ('Aguascalientes', 1),
    ('Baja California', 1),
    ('Baja California Sur', 1),
    ('Campeche', 1),
    ('Chiapas', 1),
    ('Chihuahua', 1),
    ('Ciudad de México', 1),
    ('Coahuila', 1),
    ('Colima', 1),
    ('Durango', 1),
    ('Estado de México', 1),
    ('Guanajuato', 1),
    ('Guerrero', 1),
    ('Hidalgo', 1),
    ('Jalisco', 1),
    ('Michoacán', 1),
    ('Morelos', 1),
    ('Nayarit', 1),
    ('Nuevo León', 1),
    ('Oaxaca', 1),
    ('Puebla', 1),
    ('Querétaro', 1),
    ('Quintana Roo', 1),
    ('San Luis Potosí', 1),
    ('Sinaloa', 1),
    ('Sonora', 1),
    ('Tabasco', 1),
    ('Tamaulipas', 1),
    ('Tlaxcala', 1),
    ('Veracruz', 1),
    ('Yucatán', 1),
    ('Zacatecas', 1),
    ('California', 2),
    ('Madrid', 3),
    ('Buenos Aires', 4),
    ('Bogotá', 5);
    

-- Datos para MR_TiposUsuario
INSERT INTO MR_TiposUsuario (nombre, descripcion) VALUES
    ('Administrador', 'Control total del sistema'),
    ('Usuario', 'Acceso estándar al sistema');

-- Datos para MR_Universidades
-- Datos para MR_Universidades
INSERT INTO MR_Universidades (nombre, MR_Paises_id) VALUES
    ('Universidad Autónoma de Ciudad Juárez', 1),
    ('Instituto Tecnológico de Hermosillo', 1),
    ('Centro Nacional de Investigación y Desarrollo Tecnológico (CENIDET)', 1),
    ('Instituto Tecnológico y de Estudios Superiores de Monterrey', 1),
    ('CINVESTAV, Tamaulipas', 1),   
    ('Universidad Politécnica de Tapachula', 1),
    ('Instituto Tecnológico de Sonora', 1),
    ('Instituto Tecnológico de Tijuana', 1),
    ('Instituto Tecnológico de León', 1),
    ('Universidad Autónoma de Baja California', 1),
    ('Universidad Nacional Autónoma de México', 1),
    ('Universidad Autónoma de San Luis Potosí', 1),
    ('Universidad Autónoma de Yucatán', 1),
    ('Universidad Autónoma de Zacatecas', 1),
    ('Universidad Tecnológica de la Mixteca', 1),
    ('Universidad Popular Autónoma del Estado de Puebla', 1),
    ('Universidad Veracruzana', 1),
    ('Universidad Autónoma de Sinaloa', 1),
    ('Universidad Autónoma Metropolitana', 1);

-- Datos para MR_Miembros
INSERT INTO MR_Miembros (nombre, apellidos, genero, codigo, verificado, activo, MR_Universidades_id, MR_Estados_id, MR_Paises_id, MR_EstatusMiembros_id, MR_TiposUsuario_id) VALUES
    ('Juan', 'Pérez García', 'Masculino', 'ADM001', 1, 1, 1, 1, 1, 1, 1),        -- Administrador
    ('María', 'López Martínez', 'Femenino', 'USR001', 1, 1, 2, 2, 1, 1, 2),      -- Usuario Regular
    ('Robert', 'Smith Johnson', 'Masculino', 'USR002', 1, 1, 3, 4, 2, 1, 2),     -- Usuario Regular
    ('Ana', 'García Rodríguez', 'Femenino', 'USR003', 1, 1, 4, 5, 3, 1, 2),      -- Usuario Regular
    ('Carlos', 'Martínez López', 'Masculino', 'USR004', 1, 1, 5, 6, 4, 1, 2);    -- Usuario Regular

-- Datos para MR_Login
INSERT INTO MR_Login (MR_Miembros_id, email, password_hash, activo) VALUES
    (1, 'admin@example.com', '$2y$10$nhScNtJzPPE7wn3sacUjcOwPUvSITpQuHwoWrC.GNldBXrygofm6W', true),    -- password: "admin"
    (2, 'usuario1@example.com', '$2y$10$PGS3W57IQZKcVj3DnnDsGe8ncfSJW88Y9064OeU7lFMp/PwL5zg3i', true), -- password: "user"
    (3, 'usuario2@example.com', '$2y$10$59ws4xggU2TtOuPEA92tZ.w.Lau1OWUt1sD98wv/I0hiA2CLLhCjK', true), -- password: "password"
    (4, 'usuario3@example.com', '$2y$10$BaANspvui5jBCsC7QqX4hOTOPA9sN0g2TzpMCeVWBJa9LXrE3uuVG', true), -- password: "12345"
    (5, 'usuario4@example.com', '$2y$10$8tkrMOem8qNymIjuQajAVeFftpdrG21fJW.9MdU1VSP.BL2C3irMi', true); -- password: "123456"

-- Datos para MR_ArchivosMiembros
INSERT INTO MR_ArchivosMiembros (images, cv, credencial, MR_Miembros_id) VALUES
    ('perfil1.jpg', 'cv1.pdf', 'cred1.pdf', 1),
    ('perfil2.jpg', 'cv2.pdf', 'cred2.pdf', 2),
    ('perfil3.jpg', 'cv3.pdf', 'cred3.pdf', 3),
    ('perfil4.jpg', 'cv4.pdf', 'cred4.pdf', 4),
    ('perfil5.jpg', 'cv5.pdf', 'cred5.pdf', 5);

-- Datos para MR_MiembrosInvestigaciones
INSERT INTO MR_MiembrosInvestigaciones (MR_Miembros_id, MR_LineaInvestigaciones_id) VALUES
    (1, 1),
    (2, 2),
    (3, 3),
    (4, 4),
    (5, 5);

-- Datos para MR_MiembrosMembresias
INSERT INTO MR_MiembrosMembresias (MR_Miembros_id, MR_Membresias_id, fecha_inicio, fecha_fin, estado) VALUES
    (1, 1, '2024-01-01', '2024-12-31', 'ACTIVA'),
    (2, 2, '2024-01-01', '2024-12-31', 'ACTIVA'),
    (3, 3, '2024-01-01', '2024-12-31', 'ACTIVA'),
    (4, 4, '2024-01-01', '2024-12-31', 'ACTIVA'),
    (5, 1, '2024-01-01', '2024-12-31', 'ACTIVA');

-- Datos para MR_SolicitudesMembresia
INSERT INTO MR_SolicitudesMembresia (MR_Miembros_id, MR_Membresias_id, estado, fecha_solicitud, fecha_respuesta, comentarios, revisado_por) VALUES
    (3, 2, 'PENDIENTE', '2024-02-20', NULL, 'Solicitud de actualización a membresía premium', NULL),
    (4, 2, 'APROBADA', '2024-02-15', '2024-02-18', 'Aprobado por excelente historial', 1),
    (5, 4, 'RECHAZADA', '2024-02-10', '2024-02-13', 'No cumple con los requisitos mínimos', 1),
    (2, 3, 'PENDIENTE', '2024-02-22', NULL, 'Solicitud de cambio a membresía estudiante', NULL),
    (1, 2, 'APROBADA', '2024-02-01', '2024-02-03', 'Aprobado por antigüedad', 2);

-- Datos para MR_Tokens (ejemplos de tokens JWT)
INSERT INTO MR_Tokens (token, token_type, expired, revoked, MR_Miembros_id, fecha_expiracion) VALUES
    ('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...', 'ACCESS', false, false, 1, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 1 DAY)),
    ('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ7...', 'REFRESH', false, false, 1, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY)),
    ('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ6...', 'ACCESS', false, false, 2, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 1 DAY)),
    ('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ5...', 'REFRESH', false, false, 2, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY)),
    ('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ4...', 'ACCESS', true, true, 3, '2024-02-01 00:00:00');