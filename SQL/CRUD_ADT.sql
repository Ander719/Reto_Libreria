drop database CRUD_ADT;
CREATE DATABASE IF NOT EXISTS CRUD_ADT;
USE CRUD_ADT;

CREATE TABLE PROFILE_(
PROFILE_CODE INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
EMAIL VARCHAR (40) UNIQUE,
USER_NAME VARCHAR (30) UNIQUE,
PSWD VARCHAR (30),
TELEPHONE BIGINT,
NAME_ VARCHAR (30),
SURNAME VARCHAR (30)
);

CREATE TABLE USER_(
PROFILE_CODE INT NOT NULL PRIMARY KEY,
GENDER VARCHAR (10),
CARD_NO VARCHAR (50),
FOREIGN KEY (PROFILE_CODE) REFERENCES PROFILE_(PROFILE_CODE) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE ADMIN_(
PROFILE_CODE INT NOT NULL PRIMARY KEY,
CURRENT_ACCOUNT VARCHAR (50),
FOREIGN KEY (PROFILE_CODE) REFERENCES PROFILE_(PROFILE_CODE) ON UPDATE CASCADE ON DELETE CASCADE
);
CREATE TABLE Order_(
Id_order int not null primary key,
PROFILE_CODE int not null,
date_buy date,
buyed boolean,
foreign key(PROFILE_CODE) references PROFILE_ (PROFILE_CODE) on update cascade on delete cascade
);
CREATE TABLE AUTHOR_(
ID_AUTHOR INT NOT NULL PRIMARY KEY,
NameAuthor VARCHAR(100),
LastName VARCHAR(100)
);
CREATE TABLE Book_(
Isbn char(13) not null primary key,
title varchar(100),
id_author int,
pages int,
stock int,
sipnosis varchar (300),
price float,
editorial varchar (100),
cover varchar (100),
foreign key(id_author) references AUTHOR_ (ID_AUTHOR) on update cascade on delete cascade
);
CREATE TABLE CONTENT_(
id_order int not null,
isbn char(13) not null,
quantity int,
primary key(id_order, isbn),
foreign key (id_order) references ORDER_ (Id_order) on update cascade on delete cascade,
foreign key (isbn) references BOOK_ (Isbn) on update cascade on delete cascade
);

CREATE TABLE COMENT_(
PROFILE_CODE int not null,
Isbn char (13) not null,
coment varchar (200),
valoration int,
dateComent date,
primary key(PROFILE_CODE, isbn),
foreign key (isbn) references BOOK_ (Isbn) on update cascade on delete cascade,
foreign key (PROFILE_CODE) references PROFILE_ (PROFILE_CODE) on update cascade on delete cascade

);

INSERT INTO PROFILE_ (PROFILE_CODE, EMAIL, USER_NAME, PSWD, TELEPHONE, NAME_, SURNAME) VALUES
(1, 'juan.perez@email.com', 'juanP', '1234', 611223344, 'Juan', 'Pérez'),
(2, 'maria.garcia@email.com', 'mariag', '1234', 622334455, 'María', 'García'),
(3, 'carlos.lopez@email.com', 'carlosl', '1234', 633445566, 'Carlos', 'López'),
(4, 'ana.martinez@email.com', 'anam', '1234', 644556677, 'Ana', 'Martínez'),
(5, 'pedro.rodriguez@email.com', 'pedror', '1234', 655667788, 'Pedro', 'Rodríguez');


INSERT INTO USER_ (PROFILE_CODE, GENDER, CARD_NO) VALUES
(1, 'Man', '1234-5678-9012-3456'),
(2, 'Female', '2345-6789-0123-4567'),
(3, 'Man', '3456-7890-1234-5678');


INSERT INTO ADMIN_ (PROFILE_CODE, CURRENT_ACCOUNT) VALUES
(4, 'ES12-3456-7890-1234-5678'),
(5, 'ES98-7654-3210-9876-5432');

-- =============================================
-- DATOS DE PRUEBA ADICIONALES (PRECARGADOS)
-- =============================================

-- 1. INSERTAR AUTORES (Es vital tenerlos antes que los libros)
-- El ID 1 es el que te daba error antes. ¡Ahora ya existe!
INSERT INTO AUTHOR_ (ID_AUTHOR, NameAuthor, LastName) VALUES
(1, 'J.K.', 'Rowling'),
(2, 'George R.R.', 'Martin'),
(3, 'J.R.R.', 'Tolkien'),
(4, 'Brandon', 'Sanderson'),
(5, 'Isaac', 'Asimov');

-- 2. INSERTAR LIBROS (Relacionados con los autores de arriba)
INSERT INTO Book_ (Isbn, title, id_author, pages, stock, sipnosis, price, editorial, cover) VALUES
('9788478884452', 'Harry Potter y la piedra filosofal', 1, 254, 50, 'El niño que vivió comienza su aventura en Hogwarts.', 20.00, 'Salamandra', 'harry_potter_1.jpg'),
('9788496208964', 'Juego de Tronos', 2, 800, 30, 'El invierno se acerca. Las casas nobles luchan por el trono.', 25.50, 'Gigamesh', 'juego_tronos.jpg'),
('9788445073722', 'El Señor de los Anillos: La Comunidad del Anillo', 3, 560, 100, 'Un anillo para gobernarlos a todos.', 22.90, 'Minotauro', 'senor_anillos.jpg'),
('9788466657662', 'El Imperio Final (Mistborn)', 4, 672, 45, 'En un mundo donde cae ceniza del cielo, un ladrón planea el golpe definitivo.', 21.00, 'Nova', 'mistborn.jpg'),
('9788499082479', 'Yo, Robot', 5, 350, 60, 'Las tres leyes de la robótica puestas a prueba.', 12.95, 'Debolsillo', 'yo_robot.jpg');

-- 3. INSERTAR MÁS PERFILES Y USUARIOS (Opcional, para tener variedad)
INSERT INTO PROFILE_ (PROFILE_CODE, EMAIL, USER_NAME, PSWD, TELEPHONE, NAME_, SURNAME) VALUES
(6, 'laura.admin@email.com', 'laura_admin', '1234', 666777888, 'Laura', 'Gómez'),
(7, 'david.user@email.com', 'david_user', '1234', 699000111, 'David', 'Ruiz');

-- Laura será Admin
INSERT INTO ADMIN_ (PROFILE_CODE, CURRENT_ACCOUNT) VALUES
(6, 'ES55-1234-5678-9012-3456');

-- David será Usuario Normal
INSERT INTO USER_ (PROFILE_CODE, GENDER, CARD_NO) VALUES
(7, 'Man', '5555-4444-3333-2222');
DELIMITER //
CREATE PROCEDURE RegistrarUsuario( IN p_username VARCHAR(30), IN p_pswd VARCHAR(30))
BEGIN
    DECLARE  nuevo_profile_code INT;
    
    INSERT INTO PROFILE_ (EMAIL, USER_NAME, PSWD, TELEPHONE, NAME_, SURNAME)
    VALUES (null, p_username, p_pswd, null, null, null);

    SET nuevo_profile_code = LAST_INSERT_ID();

    INSERT INTO USER_ (PROFILE_CODE, GENDER, CARD_NO)
    VALUES (nuevo_profile_code, null, null);
    
    SELECT * FROM PROFILE_ P, USER_ U WHERE P.PROFILE_CODE = U.PROFILE_CODE AND P.PROFILE_CODE= nuevo_profile_code;
 END //

DELIMITER ; 
