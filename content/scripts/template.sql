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
INSERT INTO sn (prefix,num) VALUES("OSL", 0)