create table MR_EstatusMiembros
(
    id          int auto_increment
        primary key,
    nombre      varchar(120) not null,
    descripcion text         null
);

create table MR_LineaInvestigaciones
(
    id     int auto_increment
        primary key,
    nombre varchar(120) not null
);

create table MR_Membresias
(
    id           int auto_increment
        primary key,
    nombre       varchar(120)         not null,
    tipo         varchar(50)          not null
);

create table MR_Paises
(
    id         int auto_increment
        primary key,
    nombre     varchar(120) not null,
    codigo_iso varchar(3)   null
);

create table MR_Estados
(
    id           int auto_increment
        primary key,
    nombre       varchar(120) not null,
    MR_Paises_id int          not null,
    constraint MR_Estados_MR_Paises_FK
        foreign key (MR_Paises_id) references MR_Paises (id)
);

create table MR_TiposUsuario
(
    id          int auto_increment
        primary key,
    nombre      varchar(120) not null,
    descripcion text         null
);

create table MR_Universidades
(
    id           int auto_increment
        primary key,
    nombre       varchar(120) not null,
    MR_Paises_id int          null,
    constraint MR_Universidades_MR_Paises_FK
        foreign key (MR_Paises_id) references MR_Paises (id)
            on delete set null
);

create table MR_Miembros
(
    id                    int auto_increment
        primary key,
    nombre                varchar(40)                        not null,
    apellidos             varchar(40)                        not null,
    genero                varchar(20)                        not null,
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
);

create table MR_ArchivosMiembros
(
    id             int auto_increment
        primary key,
    images         varchar(100)                       null,
    cv             varchar(100)                       not null,
    credencial     varchar(100)                       not null,
    MR_Miembros_id int                                not null,
    fecha_subida   datetime default CURRENT_TIMESTAMP not null,
    constraint MR_ArchivosMiembros_MR_Miembros_FK
        foreign key (MR_Miembros_id) references MR_Miembros (id)
            on delete cascade
);

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
);

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
    fecha_inicio               date not null,
    fecha_fin                  date null,
    constraint MR_MiembrosInvestigaciones_MR_LineaInvestigaciones_FK
        foreign key (MR_LineaInvestigaciones_id) references MR_LineaInvestigaciones (id),
    constraint MR_MiembrosInvestigaciones_MR_Miembros_FK
        foreign key (MR_Miembros_id) references MR_Miembros (id)
            on delete cascade
);

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
);

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
        foreign key (MR_Miembros_id) references MR_Miembros (id),
    constraint MR_SolicitudesMembresia_Revisor_FK
        foreign key (revisado_por) references MR_Miembros (id)
            on delete set null
);

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
);

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
INSERT INTO MR_Membresias (nombre, tipo) VALUES
    ('Membresía Básica', 'BASICA'),
    ('Membresía Premium', 'PREMIUM'),
    ('Membresía Estudiante', 'ESTUDIANTE'),
    ('Membresía Corporativa', 'CORPORATIVA');

-- Datos para MR_Paises
INSERT INTO MR_Paises (nombre, codigo_iso) VALUES
    ('México', 'MEX'),
    ('Estados Unidos', 'USA'),
    ('España', 'ESP'),
    ('Argentina', 'ARG'),
    ('Colombia', 'COL');

-- Datos para MR_Estados
INSERT INTO MR_Estados (nombre, MR_Paises_id) VALUES
    ('Ciudad de México', 1),
    ('Nuevo León', 1),
    ('Jalisco', 1),
    ('California', 2),
    ('Madrid', 3),
    ('Buenos Aires', 4),
    ('Bogotá', 5);

-- Datos para MR_TiposUsuario
INSERT INTO MR_TiposUsuario (nombre, descripcion) VALUES
    ('Administrador', 'Control total del sistema'),
    ('Usuario', 'Acceso estándar al sistema');

-- Datos para MR_Universidades
INSERT INTO MR_Universidades (nombre, MR_Paises_id) VALUES
    ('UNAM', 1),
    ('Tec de Monterrey', 1),
    ('Stanford University', 2),
    ('Universidad Complutense de Madrid', 3),
    ('Universidad de Buenos Aires', 4),
    ('Universidad Nacional de Colombia', 5);

-- Datos para MR_Miembros
INSERT INTO MR_Miembros (nombre, apellidos, genero, MR_Universidades_id, MR_Estados_id, MR_Paises_id, MR_EstatusMiembros_id, MR_TiposUsuario_id) VALUES
    ('Juan', 'Pérez García', 'Masculino', 1, 1, 1, 1, 1),        -- Administrador
    ('María', 'López Martínez', 'Femenino', 2, 2, 1, 1, 2),      -- Usuario Regular
    ('Robert', 'Smith Johnson', 'Masculino', 3, 4, 2, 1, 2),     -- Usuario Regular
    ('Ana', 'García Rodríguez', 'Femenino', 4, 5, 3, 1, 2),      -- Usuario Regular
    ('Carlos', 'Martínez López', 'Masculino', 5, 6, 4, 1, 2);    -- Usuario Regular

-- Datos para MR_Login
INSERT INTO MR_Login (MR_Miembros_id, email, password_hash, activo) VALUES
    (1, 'admin@example.com', '21232f297a57a5a743894a0e4a801fc3', true),    -- password: "admin"
    (2, 'usuario1@example.com', 'ee11cbb19052e40b07aac0ca060c23ee', true), -- password: "user"
    (3, 'usuario2@example.com', '5f4dcc3b5aa765d61d8327deb882cf99', true), -- password: "password"
    (4, 'usuario3@example.com', '827ccb0eea8a706c4c34a16891f84e7b', true), -- password: "12345"
    (5, 'usuario4@example.com', 'e10adc3949ba59abbe56e057f20f883e', true); -- password: "123456"

-- Datos para MR_ArchivosMiembros
INSERT INTO MR_ArchivosMiembros (images, cv, credencial, MR_Miembros_id) VALUES
    ('perfil1.jpg', 'cv1.pdf', 'cred1.pdf', 1),
    ('perfil2.jpg', 'cv2.pdf', 'cred2.pdf', 2),
    ('perfil3.jpg', 'cv3.pdf', 'cred3.pdf', 3),
    ('perfil4.jpg', 'cv4.pdf', 'cred4.pdf', 4),
    ('perfil5.jpg', 'cv5.pdf', 'cred5.pdf', 5);

-- Datos para MR_MiembrosInvestigaciones
INSERT INTO MR_MiembrosInvestigaciones (MR_Miembros_id, MR_LineaInvestigaciones_id, fecha_inicio, fecha_fin) VALUES
    (1, 1, '2024-01-01', NULL),
    (2, 2, '2024-01-01', NULL),
    (3, 3, '2024-01-01', NULL),
    (4, 4, '2024-01-01', NULL),
    (5, 5, '2024-01-01', NULL);

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