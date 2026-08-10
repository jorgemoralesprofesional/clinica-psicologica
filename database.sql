-- Creación de la base de datos
CREATE DATABASE IF NOT EXISTS clinica_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clinica_db;

-- 1. Tabla de Usuarios (Recepcionistas / Administradores)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabla de Psicólogos
CREATE TABLE IF NOT EXISTS psicologos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    especialidad VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    telefono VARCHAR(20) NOT NULL,
    duracion_consulta_minutos INT NOT NULL DEFAULT 45,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Tabla de Horarios de Atención del Psicólogo
CREATE TABLE IF NOT EXISTS horarios_atencion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    psicologo_id INT NOT NULL,
    dia_semana TINYINT NOT NULL COMMENT '1=Lunes, 7=Domingo',
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    FOREIGN KEY (psicologo_id) REFERENCES psicologos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Tabla de Pacientes
CREATE TABLE IF NOT EXISTS pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    documento_identidad VARCHAR(30) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    telefono VARCHAR(20) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. Tabla de Citas Médicas
CREATE TABLE IF NOT EXISTS citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    psicologo_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    estado ENUM('pendiente', 'realizada', 'cancelada') DEFAULT 'pendiente',
    motivo_cancelacion TEXT NULL,
    notas TEXT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
    FOREIGN KEY (psicologo_id) REFERENCES psicologos(id) ON DELETE CASCADE
) ENGINE=InnoDB;