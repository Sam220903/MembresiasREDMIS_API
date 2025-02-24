create table MR_EstatusMiembros
(
    id          int          not null
        primary key,
    nombre      varchar(120) not null,
    descripcion text         null
);

create table MR_LineaInvestigaciones
(
    id     int          not null
        primary key,
    nombre varchar(120) not null
);

create table MR_Membresias
(
    id           int          not null
        primary key,
    nombre       varchar(120) not null,
    fecha_inicio date         null,
    fecha_fin    date         null,
    tipo         varchar(50)  not null
);

create table MR_Paises
(
    id     int          not null
        primary key,
    nombre varchar(120) not null
);

create table MR_Estados
(
    id           int          not null
        primary key,
    nombre       varchar(120) not null,
    MR_Paises_id int          not null,
    constraint MR_Estados_MR_Paises_FK
        foreign key (MR_Paises_id) references MR_Paises (id)
);

create table MR_TiposUsuario
(
    id          int          not null
        primary key,
    nombre      varchar(120) not null,
    descripcion text         null
);

create table MR_Universidades
(
    id     int          not null
        primary key,
    nombre varchar(120) not null
);

create table MR_Miembros
(
    id                    int         not null
        primary key,
    nombre                varchar(40) not null,
    apellidos             varchar(40) not null,
    genero                varchar(20) not null,
    email                 varchar(50) not null,
    MR_login_id           int         not null,
    MR_Universidades_id   int         null,
    MR_Estados_id         int         null,
    MR_Paises_id          int         null,
    MR_EstatusMiembros_id int         null,
    MR_TiposUsuario_id    int         null,
    constraint email
        unique (email),
    constraint MR_Miembros_MR_Estados_FK
        foreign key (MR_Estados_id) references MR_Estados (id),
    constraint MR_Miembros_MR_EstatusMiembros_FK
        foreign key (MR_EstatusMiembros_id) references MR_EstatusMiembros (id),
    constraint MR_Miembros_MR_Paises_FK
        foreign key (MR_Paises_id) references MR_Paises (id),
    constraint MR_Miembros_MR_TiposUsuario_FK
        foreign key (MR_TiposUsuario_id) references MR_TiposUsuario (id),
    constraint MR_Miembros_MR_Universidades_FK
        foreign key (MR_Universidades_id) references MR_Universidades (id)
);

create table MR_ArchivosMiembros
(
    id             int          not null
        primary key,
    images         varchar(100) null,
    cv             varchar(100) not null,
    credencial     varchar(100) not null,
    MR_Miembros_id int          not null,
    constraint MR_ArchivosMiembros_MR_Miembros_FK
        foreign key (MR_Miembros_id) references MR_Miembros (id)
);

create table MR_MiembrosInvestigaciones
(
    MR_Miembros_id             int not null,
    MR_LineaInvestigaciones_id int not null,
    constraint MR_MiembrosInvestigaciones_MR_LineaInvestigaciones_FK
        foreign key (MR_LineaInvestigaciones_id) references MR_LineaInvestigaciones (id),
    constraint MR_MiembrosInvestigaciones_MR_Miembros_FK
        foreign key (MR_Miembros_id) references MR_Miembros (id)
);

create table MR_MiembrosMembresias
(
    MR_Miembros_id   int not null,
    MR_Membresias_id int not null,
    constraint MR_MiembrosMembresias_MR_Membresias_FK
        foreign key (MR_Membresias_id) references MR_Membresias (id),
    constraint MR_MiembrosMembresias_MR_Miembros_FK
        foreign key (MR_Miembros_id) references MR_Miembros (id)
);

create table MR_Tokens
(
    id             int auto_increment
        primary key,
    token          varchar(255)         not null,
    token_type     varchar(50)          not null,
    expired        tinyint(1) default 0 not null,
    revoked        tinyint(1) default 0 not null,
    MR_Miembros_id int                  not null,
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

create table MR_login
(
    id             int          not null
        primary key,
    password_hash  varchar(255) not null,
    ultimo_acceso  date         not null,
    MR_Miembros_id int          not null,
    constraint MR_login_MR_Miembros_FK
        foreign key (MR_Miembros_id) references MR_Miembros (id)
);

alter table MR_Miembros
    add constraint MR_Miembros_MR_login_FK
        foreign key (MR_login_id) references MR_login (id);

