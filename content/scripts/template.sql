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
    disc_capacity INT,
    disc_type VARCHAR(10),
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