-- Crear base de datos
CREATE DATABASE kelpie_helpdesk;
USE kelpie_helpdesk;

-- Tabla de roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    contraseña VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);

-- Tabla de tickets
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    prioridad ENUM('baja','media','alta') DEFAULT 'media',
    estado ENUM('pendiente','en_proceso','resuelto') DEFAULT 'pendiente',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT NOT NULL,
    tecnico_id INT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (tecnico_id) REFERENCES usuarios(id)
);

-- Tabla de comentarios
CREATE TABLE comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    autor_id INT NOT NULL,
    texto TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id),
    FOREIGN KEY (autor_id) REFERENCES usuarios(id)
);

-- Datos iniciales
INSERT INTO roles (nombre) VALUES ('admin'), ('tecnico'), ('user');

--INSERT INTO usuarios (nombre, correo, contraseña, rol_id)
--VALUES ('Admin', 'admin@kelpie.com', '$2y$10$hashAdmin', 1),
--       ('Tecnico', 'tecnico@kelpie.com', '$2y$10$hashTecnico', 2),
--       ('Usuario', 'user@kelpie.com', '$2y$10$hashUser', 3);
