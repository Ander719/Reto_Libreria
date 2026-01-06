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

CREATE TABLE BOOK_ (
    Isbn VARCHAR(20) NOT NULL PRIMARY KEY, 
    Title VARCHAR(100),
    Author VARCHAR(100),
    Description TEXT,
    Price DECIMAL(10, 2),
    Stock INT,
    Category VARCHAR(50),
    Image VARCHAR(200)
);

CREATE TABLE ADMIN_(
PROFILE_CODE INT NOT NULL PRIMARY KEY,
CURRENT_ACCOUNT VARCHAR (50),
FOREIGN KEY (PROFILE_CODE) REFERENCES PROFILE_(PROFILE_CODE) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE COMENT_ (
    COMENT_ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    PROFILE_CODE INT NOT NULL,
    Isbn VARCHAR(20) NOT NULL,
    comment_text TEXT,
    valoration INT,
    dateComent DATE,
    
  
    FOREIGN KEY (PROFILE_CODE) REFERENCES PROFILE_(PROFILE_CODE) ON UPDATE CASCADE ON DELETE CASCADE,

    FOREIGN KEY (Isbn) REFERENCES BOOK_(Isbn) ON UPDATE CASCADE ON DELETE CASCADE
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


INSERT INTO BOOK_ (Isbn, Author, Title, Description, Price, Stock, Category, Image)
VALUES ('123-PRUEBA', 'Autor Prueba', 'Libro de Prueba', 'Descripción...', 10, 100, 'Test', 'https://placehold.co/400x600');
INSERT INTO COMENT_ (PROFILE_CODE, Isbn, comment_text, valoration, dateComent)
VALUES (1, '123-PRUEBA', '¡Me ha encantado! Es un libro fantástico.', 5, CURDATE());


INSERT INTO AUTHOR_ (ID_AUTHOR, NameAuthor, LastName) VALUES 
(1, 'Brandon', 'Sanderson'),
(2, 'J.K.', 'Rowling');


INSERT INTO Book_ (Isbn, title, id_author, pages, stock, sipnosis, price, editorial, cover) VALUES 
('9780765326355', 'The Way of Kings', 1, 1007, 10, 'Roshar is a world of stone and storms. Uncanny tempests of incredible power sweep across the rocky terrain so frequently that they have shaped ecology and civilization alike.', 25.99, 'Tor Books', 'https://placehold.co/400x600'),
('9780439064873', 'Harry Potter and the Chamber of Secrets', 2, 251, 5, 'The Dursleys were so mean and hideous that summer that all Harry Potter wanted was to get back to the Hogwarts School for Witchcraft and Wizardry.', 14.50, 'Scholastic', 'https://placehold.co/400x600');

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
