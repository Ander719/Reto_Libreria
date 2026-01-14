DROP DATABASE IF EXISTS crud_adt;
CREATE DATABASE IF NOT EXISTS crud_adt;
USE crud_adt;

-- 1. TABLAS PRINCIPALES (Usuarios y Perfiles)
CREATE TABLE profile_ (
    profile_code INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(40) UNIQUE,
    user_name VARCHAR(30) UNIQUE,
    pswd VARCHAR(30),
    telephone BIGINT,
    name_ VARCHAR(30),
    surname VARCHAR(30)
);

CREATE TABLE user_ (
    profile_code INT NOT NULL PRIMARY KEY,
    gender VARCHAR(10),
    card_no VARCHAR(50),
    FOREIGN KEY (profile_code) REFERENCES profile_(profile_code) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE admin_ (
    profile_code INT NOT NULL PRIMARY KEY,
    current_account VARCHAR(50),
    FOREIGN KEY (profile_code) REFERENCES profile_(profile_code) ON UPDATE CASCADE ON DELETE CASCADE
);

-- 2. TABLAS DE PRODUCTOS (Autores y Libros)
CREATE TABLE author_ (
    id_author INT NOT NULL PRIMARY KEY,
    name_author VARCHAR(100),
    last_name VARCHAR(100)
);

CREATE TABLE book_ (
    isbn CHAR(13) NOT NULL PRIMARY KEY,
    title VARCHAR(100),
    id_author INT,
    pages INT,
    stock INT,
    synopsis VARCHAR(300),
    price FLOAT,
    editorial VARCHAR(100),
    cover VARCHAR(100),
    FOREIGN KEY (id_author) REFERENCES author_(id_author) ON UPDATE CASCADE ON DELETE CASCADE
);

-- 3. TABLAS TRANSACCIONALES (Pedidos y Contenido)
CREATE TABLE order_ (
    id_order INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    profile_code INT NOT NULL,
    date_buy DATE,
    buyed BOOLEAN,
    FOREIGN KEY (profile_code) REFERENCES profile_(profile_code) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE content_ (
    id_order INT NOT NULL,
    isbn CHAR(13) NOT NULL,
    quantity INT,
    PRIMARY KEY(id_order, isbn),
    FOREIGN KEY (id_order) REFERENCES order_(id_order) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (isbn) REFERENCES book_(isbn) ON UPDATE CASCADE ON DELETE CASCADE
);

-- 4. TABLA DE OPINIONES
CREATE TABLE comment_ (
    profile_code INT NOT NULL,
    isbn CHAR(13) NOT NULL,
    comment_text VARCHAR(200),
    valoration INT,
    date_comment DATE,
    PRIMARY KEY(profile_code, isbn),
    FOREIGN KEY (isbn) REFERENCES book_(isbn) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (profile_code) REFERENCES profile_(profile_code) ON UPDATE CASCADE ON DELETE CASCADE
);

-- 1. Perfiles
INSERT INTO profile_ (profile_code, email, user_name, pswd, telephone, name_, surname) VALUES
(1, 'juan.perez@email.com', 'juanP', '1234', 611223344, 'Juan', 'Pérez'),
(2, 'maria.garcia@email.com', 'mariag', '1234', 622334455, 'María', 'García'),
(3, 'carlos.lopez@email.com', 'carlosl', '1234', 633445566, 'Carlos', 'López'),
(4, 'ana.martinez@email.com', 'anam', '1234', 644556677, 'Ana', 'Martínez'),
(5, 'pedro.rodriguez@email.com', 'pedror', '1234', 655667788, 'Pedro', 'Rodríguez');

-- 2. Usuarios y Admins
INSERT INTO user_ (profile_code, gender, card_no) VALUES
(1, 'Man', '1234-5678-9012-3456'),
(2, 'Female', '2345-6789-0123-4567'),
(3, 'Man', '3456-7890-1234-5678');

INSERT INTO admin_ (profile_code, current_account) VALUES
(4, 'ES12-3456-7890-1234-5678'),
(5, 'ES98-7654-3210-9876-5432');

-- 3. Autores
INSERT INTO author_ (id_author, name_author, last_name) VALUES
(1, 'J.K.', 'Rowling'),
(2, 'Miguel', 'de Cervantes'),
(3, 'George', 'Orwell'),
(4, 'Gabriel', 'García Márquez'),
(5, 'Brandon', 'Sanderson');

-- 4. Libros
INSERT INTO book_ (isbn, title, id_author, pages, stock, synopsis, price, editorial, cover) VALUES
('9780439139595', 'Harry Potter y el Cáliz de Fuego', 1, 636, 10, 'Harry se enfrenta a desafíos mortales en el Torneo de los Tres Magos.', 22.50, 'Salamandra', 'hp4.jpg'),
('9788420412146', 'Don Quijote de la Mancha', 2, 1345, 5, 'Las aventuras de un hidalgo que enloquece leyendo libros de caballerías.', 15.99, 'Alfaguara', 'quijote.jpg'),
('9780451524935', '1984', 3, 328, 20, 'El Gran Hermano te vigila. Una distopía sobre el control total.', 12.00, 'Debolsillo', '1984.jpg'),
('9780307474728', 'Cien años de soledad', 4, 471, 8, 'La saga de la familia Buendía en el pueblo mágico de Macondo.', 18.50, 'Cátedra', 'cien_anos.jpg'),
('9788466657523', 'El Imperio Final', 5, 672, 12, 'En un mundo donde cae ceniza del cielo, un ladrón planea el robo definitivo.', 21.90, 'Nova', 'mistborn.jpg');

-- 5. Pedidos
INSERT INTO order_ (id_order, profile_code, date_buy, buyed) VALUES
(101, 1, '2024-12-01', true),
(102, 2, '2025-01-10', true),
(103, 1, '2025-01-14', false); 

-- 6. Contenido Pedidos
INSERT INTO content_ (id_order, isbn, quantity) VALUES
(101, '9780439139595', 1),
(102, '9788420412146', 1),
(102, '9780451524935', 2),
(103, '9780307474728', 1);

-- 7. Comentarios
INSERT INTO comment_ (profile_code, isbn, comment_text, valoration, date_comment) VALUES
(1, '9780439139595', 'Increíble libro, la mejor entrega de la saga hasta ahora.', 5, '2024-12-05'),
(2, '9788420412146', 'Un clásico imprescindible, aunque el lenguaje es denso.', 4, '2025-01-11'),
(3, '9780451524935', 'Aterradoramente actual. Me encantó.', 5, '2024-11-20'),
(2, '9780439139595', 'Entretenido, pero prefiero las películas.', 3, '2025-01-12');


DELIMITER //

-- Procedimiento: Registrar Usuario
CREATE PROCEDURE register_user( IN p_username VARCHAR(30), IN p_pswd VARCHAR(30))
BEGIN
    DECLARE v_new_profile_code INT;
    
    INSERT INTO profile_ (email, user_name, pswd, telephone, name_, surname)
    VALUES (null, p_username, p_pswd, null, null, null);

    SET v_new_profile_code = LAST_INSERT_ID();

    INSERT INTO user_ (profile_code, gender, card_no)
    VALUES (v_new_profile_code, null, null);
    
    SELECT * FROM profile_ p 
    JOIN user_ u ON p.profile_code = u.profile_code 
    WHERE p.profile_code = v_new_profile_code;
 END //

-- Procedimiento: Obtener Todos los Libros
CREATE PROCEDURE GetAllBooks()
BEGIN
    SELECT b.*, 
           a.name_author, 
           a.last_name, 
           IFNULL(AVG(c.valoration), 0) as rating
    FROM book_ b
    INNER JOIN author_ a ON b.id_author = a.id_author
    LEFT JOIN comment_ c ON b.isbn = c.isbn
    GROUP BY b.isbn;
END //

-- Procedimiento: Obtener Libro por ISBN
CREATE PROCEDURE GetBookByISBN(IN p_isbn CHAR(13))
BEGIN
    SELECT b.*, 
           a.name_author, 
           a.last_name, 
           IFNULL(AVG(c.valoration), 0) as rating
    FROM book_ b
    INNER JOIN author_ a ON b.id_author = a.id_author
    LEFT JOIN comment_ c ON b.isbn = c.isbn
    WHERE b.isbn = p_isbn
    GROUP BY b.isbn;
END //

DELIMITER ;