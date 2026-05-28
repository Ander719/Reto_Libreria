<?php

// Libro del catalogo con su autor.
class Book
{
    private $title;
    private $author;
    private $isbn;
    private $pages;
    private $stock;
    private $synopsis;
    private $price;
    private $editorial;
    private $cover;

    // Guarda todos los datos del libro cuando se crea
    public function __construct($title, $author, $isbn, $pages, $stock, $synopsis, $price, $editorial, $cover)
    {
        $this->title = $title;
        $this->author = $author;
        $this->isbn = $isbn;
        $this->pages = $pages;
        $this->stock = $stock;
        $this->synopsis = $synopsis;
        $this->price = $price;
        $this->editorial = $editorial;
        $this->cover = $cover;
    }

    // Devuelve el titulo del libro
    public function getTitle()
    {
        return $this->title;
    }
    // Devuelve el autor del libro
    public function getAuthor()
    {
        return $this->author;
    }
    // Devuelve el ISBN del libro
    public function getIsbn()
    {
        return $this->isbn;
    }
    // Devuelve el numero de paginas
    public function getPages()
    {
        return $this->pages;
    }
    // Devuelve el stock disponible
    public function getStock()
    {
        return $this->stock;
    }
    // Devuelve la sinopsis del libro
    public function getSynopsis()
    {
        return $this->synopsis;
    }
    // Devuelve el precio del libro
    public function getPrice()
    {
        return $this->price;
    }
    // Devuelve la editorial del libro
    public function getEditorial()
    {
        return $this->editorial;
    }
    // Devuelve el nombre de la portada
    public function getCover()
    {
        return $this->cover;
    }
    // Cambia el titulo del libro
    public function setTitle($title)
    {
        $this->title = $title;
    }
    // Cambia el autor del libro
    public function setAuthor($author)
    {
        $this->author = $author;
    }
    // Cambia el ISBN del libro
    public function setIsbn($isbn)
    {
        $this->isbn = $isbn;
    }
    // Cambia el numero de paginas
    public function setPages($pages)
    {
        $this->pages = $pages;
    }
    // Cambia el stock del libro
    public function setStock($stock)
    {
        $this->stock = $stock;
    }
    // Cambia la sinopsis del libro
    public function setSynopsis($synopsis)
    {
        $this->synopsis = $synopsis;
    }
    // Cambia el precio del libro
    public function setPrice($price)
    {
        $this->price = $price;
    }
    // Cambia la editorial del libro
    public function setEditorial($editorial)
    {
        $this->editorial = $editorial;
    }
    // Cambia la portada del libro
    public function setCover($cover)
    {
        $this->cover = $cover;
    }
    // Prepara los datos del libro para mandarlos como JSON
    public function toArray()
    {
        return [
            'isbn' => $this->isbn,
            'title' => $this->title,
            'author' => $this->author->toArray(), 
            'pages' => $this->pages,
            'stock' => $this->stock,
            'synopsis' => $this->synopsis,
            'price' => $this->price,
            'editorial' => $this->editorial,
            'cover' => $this->cover,
        ];
    }
}
