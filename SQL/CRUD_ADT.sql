DROP DATABASE IF EXISTS crud_adt;
CREATE DATABASE IF NOT EXISTS crud_adt;
USE crud_adt;

-- 1. TABLAS PRINCIPALES (Usuarios y Perfiles)
CREATE TABLE profile_ (
    profile_code INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(40) UNIQUE,
    user_name VARCHAR(30) UNIQUE,
    pswd VARCHAR(255),
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
    synopsis text,
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
    comment_text text,
    valoration float,
    date_comment DATE,
    PRIMARY KEY(profile_code, isbn),
    FOREIGN KEY (isbn) REFERENCES book_(isbn) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (profile_code) REFERENCES profile_(profile_code) ON UPDATE CASCADE ON DELETE CASCADE
);

-- 1. Perfiles
INSERT INTO profile_ (profile_code, email, user_name, pswd, telephone, name_, surname) VALUES
(1, 'admin@admin.com', 'admin', '$2y$10$batxaCdhUC7LYhE5YDVv8u7Rtn3ZfuehmLzHu7GWek7mUViODhVGy', 123456789, 'Jefe', 'Supremo'),
(2, 'juan.perez@email.com', 'juanP', '$2y$10$batxaCdhUC7LYhE5YDVv8u7Rtn3ZfuehmLzHu7GWek7mUViODhVGy', 611223344, 'Juan', 'Pérez'),
(3, 'maria.garcia@email.com', 'mariag', '$2y$10$batxaCdhUC7LYhE5YDVv8u7Rtn3ZfuehmLzHu7GWek7mUViODhVGy', 622334455, 'María', 'García'),
(4, 'carlos.lopez@email.com', 'carlosl', '$2y$10$batxaCdhUC7LYhE5YDVv8u7Rtn3ZfuehmLzHu7GWek7mUViODhVGy', 633445566, 'Carlos', 'López');

-- 2. Usuarios y Admins
INSERT INTO user_ (profile_code, gender, card_no) VALUES
(2, 'Man', '1234-5678-9012-3456'),
(3, 'Female', '2345-6789-0123-4567'),
(4, 'Man', '3456-7890-1234-5678');

INSERT INTO admin_ (profile_code, current_account) VALUES
(1, 'ES12-3456-7890-1234-5678');

-- 3. Autores
INSERT INTO author_ (id_author, name_author, last_name) VALUES
(1, null ,null),
(2, 'J.K.', 'Rowling'),
(3, 'Miguel', 'de Cervantes'),
(4, 'George', 'Orwell'),
(5, 'Gabriel', 'García Márquez'),
(6, 'Brandon', 'Sanderson'),
(7, 'Brandon', 'Sanderson'),
(8, 'Fiódor', 'Dostoievski'),
(9, 'Osamu', 'Dazai');

-- 4. Libros
INSERT INTO book_ (isbn, title, id_author, pages, stock, synopsis, price, editorial, cover) VALUES
('9780439139595', 'Harry Potter y el Cáliz de Fuego', 2, 636, 10, 'Harry se enfrenta a desafíos mortales en el Torneo de los Tres Magos.', 22.50, 'Salamandra', 'hp4.jpg'),
('9788420412146', 'Don Quijote de la Mancha', 3, 1345, 5, 'Las aventuras de un hidalgo que enloquece leyendo libros de caballerías.', 15.99, 'Alfaguara', 'quijote.jpg'),
('9780451524935', '1984', 4, 328, 20, 'El Gran Hermano te vigila. Una distopía sobre el control total.', 12.00, 'Debolsillo', '1984.jpg'),
('9780307474728', 'Cien años de soledad', 5, 471, 8, 'La saga de la familia Buendía en el pueblo mágico de Macondo.', 18.50, 'Cátedra', 'cien_anos.jpg'),
('9788419035769', 'INDIGNO DE SER HUMANO', 9, 240, 10, 'Indigno de ser humano es la obra maestra de Osamu Dazai. Su protagonista Yozo, autorretrato crudo y revelador del propio Dazai, narra en primera persona las circunstancias de su vida desde su nacimiento en una familia de la aristocracia rural hasta su ruina y decadencia en Tokio. Un periplo vital que es un viaje sin retorno a través las sombras de la alienación, la adicción y la búsqueda incesante de identidad y que lleva a Yozo por sombríos callejones, antros de mala muerte y sórdidos rincones de la ciudad para acabar hundido sin remedio en el foso de la autodestrucción.Mientras Yozo busca consuelo y comprensión en un mundo que le resulta a la vez indiferente e implacable, Dazai elabora una narrativa que trasciende fronteras y sondea con amarga intuición los temas universales de la desesperación, el aislamiento y la angustia vital.', 22.80, 'Satori Ediciones', 'indigno_de_ser_humano.jpg'),
('9788416440047', 'NOCHES BLANCAS', 8, 128, 30, 'Un joven solitario e introvertido narra cómo conoce de forma accidental a una muchacha durante una “noche blanca”, fenómeno que se da en la ciudad rusa durante la época del solsticio de verano y a causa del cual la oscuridad nunca es completa. Tras el primer encuentro, la pareja de desconocidos se citará durante las cuatro noches siguientes, noches en las que la chica, de nombre Nastenka, relatará su triste historia, y en las que harán acto de presencia, de forma sutil y envolvente, las grandes pasiones que mueven al ser humano: el amor, la ilusión, la esperanza, el desamor, el desengaño.', 17.10, 'Nórdica Libros', 'noches_blancas.jpg'),
('9788419306074', 'SAKAMOTO DAYS 1', 7, 192, 20, 'Taro Sakamoto era un asesino a sueldo que lo dejó todo tras conocer a la mujer de su vida. Tiempo después, con una hija y unos cuantos kilos de más, vive una vida tranquila regentando un combini, aunque su instinto sigue intacto. Un antiguo compañero descubre su paradero y a partir de ahí, otros conocidos suyos y rivales empezarán a buscarlo para darle pasaporte al otro mundo de una vez. Con una familia y un negocio que proteger, la cosas se van a poner complicadas para Taro. Pero eso no es todo: entre otras muchas reglas que ha acordado con su mujer, Taro tiene prohibido volver a matar a nadie más! ¿Cómo se las apañará para proteger su pacífica vida con la que se le viene encima?', 7.60, 'Ivrea', 'sakamoto_days1.jpg'),
('9788466657523', 'El Imperio Final', 6, 672, 12, 'En un mundo donde cae ceniza del cielo, un ladrón planea el robo definitivo.', 21.90, 'Nova', 'mistborn.jpg');

-- 5. Pedidos
INSERT INTO order_ (id_order, profile_code, date_buy, buyed) VALUES
(101, 2, '2024-12-01', true),
(102, 3, '2025-01-10', true),
(103, 2, '2025-01-14', false); 

-- 6. Contenido Pedidos
INSERT INTO content_ (id_order, isbn, quantity) VALUES
(101, '9780439139595', 1),
(102, '9788420412146', 1),
(102, '9780451524935', 2),
(103, '9780307474728', 1);

-- 7. Comentarios
INSERT INTO comment_ (profile_code, isbn, comment_text, valoration, date_comment) VALUES
(2, '9780439139595', 'Increíble libro, la mejor entrega de la saga hasta ahora.', 5, '2024-12-05'),
(3, '9788420412146', 'Un clásico imprescindible, aunque el lenguaje es denso.', 4, '2025-01-11'),
(4, '9780451524935', 'Aterradoramente actual. Me encantó.', 5, '2024-11-20'),
(3, '9780439139595', 'Entretenido, pero prefiero las películas.', 3, '2025-01-12');


DELIMITER //

-- Procedimiento: Registrar Usuario
CREATE PROCEDURE register_user(IN p_username VARCHAR(30), IN p_password VARCHAR(255))
BEGIN
    DECLARE v_new_profile_code INT;
    
    -- 1. Insertamos en la tabla padre (PROFILE)
    -- Dejamos email, telefono, nombre y apellido como NULL por ahora
    INSERT INTO profile_ (email, user_name, pswd, telephone, name_, surname)
    VALUES (NULL, p_username, p_password, NULL, NULL, NULL);

    -- 2. Obtenemos el ID generado
    SET v_new_profile_code = LAST_INSERT_ID();

    -- 3. Insertamos en la tabla hija (USER)
    -- Por defecto creamos el usuario vacío (sin género ni tarjeta)
    INSERT INTO user_ (profile_code, gender, card_no)
    VALUES (v_new_profile_code, NULL, NULL);
    
    -- 4. Devolvemos los datos del usuario recién creado
    -- Esto es lo que tu PHP leerá en $stmt->fetch()
    SELECT p.*, u.gender, u.card_no 
    FROM profile_ p 
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
