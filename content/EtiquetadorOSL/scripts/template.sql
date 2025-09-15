-- Tabla SN
CREATE TABLE sn (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prefix VARCHAR(3) NOT NULL,
    num INT(4) NOT NULL
);

-- Tabla CPU
CREATE TABLE cpu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(35) NOT NULL
);

-- Tabla RAM
CREATE TABLE ram (
    id INT AUTO_INCREMENT PRIMARY KEY,
    capacity INT(5) NOT NULL      
);

-- Tabla Disc
CREATE TABLE disc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    capacity INT(5) NOT NULL
);

-- Tabla GPU
CREATE TABLE gpu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(40) NOT NULL
);

-- Tabla pc
CREATE TABLE pc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    board_type VARCHAR(4),
    cpu_name INT,
    ram_capacity INT,
    ram_type VARCHAR(10),
    disc_type VARCHAR(10),
    disc_capacity INT,
    gpu_name INT,
    gpu_type VARCHAR(10),
    wifi VARCHAR(7),
    bluetooth VARCHAR(8),
    obser TEXT,
    FOREIGN KEY (cpu_name) REFERENCES cpu(id),
    FOREIGN KEY (ram_capacity) REFERENCES ram(id),
    FOREIGN KEY (disc_capacity) REFERENCES disc(id),
    FOREIGN KEY (gpu_name) REFERENCES gpu(id)
);

-- Tabla models
CREATE TABLE models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL,
    model INT NOT NULL,
    FOREIGN KEY (model) REFERENCES pc(id)
);


-- Tabla asociacion de SN
CREATE TABLE sn_pc (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sn_id INT NOT NULL,
    pc_id INT NOT NULL,
    FOREIGN KEY (sn_id) REFERENCES sn(id),
    FOREIGN KEY (pc_id) REFERENCES pc(id)
);

-- Valores por defecto para RAM
INSERT INTO ram (capacity) VALUES("2");
INSERT INTO ram (capacity) VALUES("4");
INSERT INTO ram (capacity) VALUES("8");
INSERT INTO ram (capacity) VALUES("16");

-- Valores por defecto para Disc
INSERT INTO disc (capacity) VALUES("120");
INSERT INTO disc (capacity) VALUES("160");
INSERT INTO disc (capacity) VALUES("200");
INSERT INTO disc (capacity) VALUES("250");
INSERT INTO disc (capacity) VALUES("320");
INSERT INTO disc (capacity) VALUES("480");
INSERT INTO disc (capacity) VALUES("500");
INSERT INTO disc (capacity) VALUES("750");
INSERT INTO disc (capacity) VALUES("1000");

-- Valores por defecto para SN
INSERT INTO sn (prefix,num) VALUES("OSL", 0);


CREATE TABLE roles(
    id_rol INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL
);
INSERT INTO roles (nombre_rol, descripcion) VALUES ('Admin', 'Acceso completo al sistema con todos los privilegios');
INSERT INTO roles (nombre_rol, descripcion) VALUES ('User', 'Usuario con acceso limitado, puede ver y generar PDFs pero no modificar configuraciones');

-- Tabla para gestión de usuarios
CREATE TABLE users (
  id INT NOT NULL AUTO_INCREMENT,
  role_id INT NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE, 
  password VARCHAR(255) NOT NULL,
  PRIMARY KEY (id,role_id),
  FOREIGN KEY (role_id) REFERENCES roles(id_rol),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
  


-- Se debe añadir un correo electrónico en el INSERT
INSERT INTO users (role_id, username, email, password) VALUES (1, 'admin', 'admin@example.com', '$2y$10$qdlwG5sR/A7OZ3tt5yYFgOvhKr09.eqoLRpAl2BQb17ymbhyeX84.');

-- Nueva tabla para la recuperación de contraseña
CREATE TABLE password_resets (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    -- The FOREIGN KEY now correctly references the UNSIGNED 'id' column in the 'users' table
    FOREIGN KEY (user_id) REFERENCES users(id)
);