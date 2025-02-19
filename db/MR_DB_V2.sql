-- Host: localhost    Database: mr_db
-- Versión 2
-- Modificado por Brenda SV

-- ------------------------------------------------------

-- Se agregó la creación y selección de la base de datos
CREATE DATABASE IF NOT EXISTS mr_db;
USE mr_db;

-- Crear las tablas sin restricciones de claves foráneas
CREATE TABLE MR_EstatusMiembros (
    id INT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    descripcion TEXT
);

CREATE TABLE MR_TiposUsuario (
    id INT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    descripcion TEXT
);

CREATE TABLE MR_Universidades (
    id INT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL
);

CREATE TABLE MR_Paises (
    id INT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL
);

CREATE TABLE MR_Estados (
    id INT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    MR_Paises_id INT NOT NULL
);

CREATE TABLE MR_Membresias (
    id INT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    fecha_inicio DATE,
    fecha_fin DATE,
    tipo VARCHAR(50) NOT NULL
);

CREATE TABLE MR_LineaInvestigaciones (
    id INT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL
);

-- Crear MR_login
CREATE TABLE MR_login (
    id INT PRIMARY KEY,
    password_hash VARCHAR(255) NOT NULL,
    ultimo_acceso DATE NOT NULL,
    MR_Miembros_id INT NOT NULL,
    token VARCHAR(255)
);

-- Ahora crear MR_Miembros sin claves foráneas
CREATE TABLE MR_Miembros (
    id INT PRIMARY KEY,
    nombre VARCHAR(40) NOT NULL,
    apellidos VARCHAR(40) NOT NULL,
    genero VARCHAR(20) NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    MR_login_id INT NOT NULL,
    MR_Universidades_id INT,
    MR_Estados_id INT,
    MR_Paises_id INT,
    MR_EstatusMiembros_id INT,
    MR_TiposUsuario_id INT
);

CREATE TABLE MR_MiembrosInvestigaciones (
    MR_Miembros_id INT NOT NULL,
    MR_LineaInvestigaciones_id INT NOT NULL
);

CREATE TABLE MR_MiembrosMembresias (
    MR_Miembros_id INT NOT NULL,
    MR_Membresias_id INT NOT NULL
);

CREATE TABLE MR_ArchivosMiembros (
    id INT PRIMARY KEY,
    images VARCHAR(100),
    cv VARCHAR(100) NOT NULL,
    credencial VARCHAR(100) NOT NULL,
    MR_Miembros_id INT NOT NULL
);

-- Ahora agregar las restricciones de claves foráneas
ALTER TABLE MR_Estados ADD CONSTRAINT MR_Estados_MR_Paises_FK FOREIGN KEY (MR_Paises_id) REFERENCES MR_Paises(id);

ALTER TABLE MR_login ADD CONSTRAINT MR_login_MR_Miembros_FK FOREIGN KEY (MR_Miembros_id) REFERENCES MR_Miembros(id);

ALTER TABLE MR_Miembros 
    ADD CONSTRAINT MR_Miembros_MR_Paises_FK FOREIGN KEY (MR_Paises_id) REFERENCES MR_Paises(id),
    ADD CONSTRAINT MR_Miembros_MR_Estados_FK FOREIGN KEY (MR_Estados_id) REFERENCES MR_Estados(id),
    ADD CONSTRAINT MR_Miembros_MR_TiposUsuario_FK FOREIGN KEY (MR_TiposUsuario_id) REFERENCES MR_TiposUsuario(id),
    ADD CONSTRAINT MR_Miembros_MR_EstatusMiembros_FK FOREIGN KEY (MR_EstatusMiembros_id) REFERENCES MR_EstatusMiembros(id),
    ADD CONSTRAINT MR_Miembros_MR_login_FK FOREIGN KEY (MR_login_id) REFERENCES MR_login(id),
    ADD CONSTRAINT MR_Miembros_MR_Universidades_FK FOREIGN KEY (MR_Universidades_id) REFERENCES MR_Universidades(id);

ALTER TABLE MR_MiembrosInvestigaciones 
    ADD CONSTRAINT MR_MiembrosInvestigaciones_MR_Miembros_FK FOREIGN KEY (MR_Miembros_id) REFERENCES MR_Miembros(id),
    ADD CONSTRAINT MR_MiembrosInvestigaciones_MR_LineaInvestigaciones_FK FOREIGN KEY (MR_LineaInvestigaciones_id) REFERENCES MR_LineaInvestigaciones(id);

ALTER TABLE MR_MiembrosMembresias 
    ADD CONSTRAINT MR_MiembrosMembresias_MR_Miembros_FK FOREIGN KEY (MR_Miembros_id) REFERENCES MR_Miembros(id),
    ADD CONSTRAINT MR_MiembrosMembresias_MR_Membresias_FK FOREIGN KEY (MR_Membresias_id) REFERENCES MR_Membresias(id);

ALTER TABLE MR_ArchivosMiembros 
    ADD CONSTRAINT MR_ArchivosMiembros_MR_Miembros_FK FOREIGN KEY (MR_Miembros_id) REFERENCES MR_Miembros(id);
