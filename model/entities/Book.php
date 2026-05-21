<?php

/**
 * Libro del catalogo con su autor.
 */
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

    /**
     * @param string $title Titulo del libro.
     * @param Author $author Autor asociado.
     * @param string $isbn ISBN unico.
     * @param int $pages Numero de paginas.
     * @param int $stock Unidades disponibles.
     * @param string $synopsis Sinopsis comercial.
     * @param float $price Precio actual.
     * @param string $editorial Editorial.
     * @param string $cover Nombre de portada.
     */
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

    /** @return string Titulo del libro. */
    public function getTitle()
    {
        return $this->title;
    }
    /** @return Author Autor asociado. */
    public function getAuthor()
    {
        return $this->author;
    }
    /** @return string ISBN unico. */
    public function getIsbn()
    {
        return $this->isbn;
    }
    /** @return int Numero de paginas. */
    public function getPages()
    {
        return $this->pages;
    }
    /** @return int Unidades disponibles. */
    public function getStock()
    {
        return $this->stock;
    }
    /** @return string Sinopsis del libro. */
    public function getSynopsis()
    {
        return $this->synopsis;
    }
    /** @return float Precio actual. */
    public function getPrice()
    {
        return $this->price;
    }
    /** @return string Editorial. */
    public function getEditorial()
    {
        return $this->editorial;
    }
    /** @return string Nombre de archivo de portada. */
    public function getCover()
    {
        return $this->cover;
    }
    /** @param string $title Titulo actualizado. @return void */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    /** @param Author $author Autor actualizado. @return void */
    public function setAuthor($author)
    {
        $this->author = $author;
    }
    /** @param string $isbn ISBN actualizado. @return void */
    public function setIsbn($isbn)
    {
        $this->isbn = $isbn;
    }
    /** @param int $pages Paginas actualizadas. @return void */
    public function setPages($pages)
    {
        $this->pages = $pages;
    }
    /** @param int $stock Stock actualizado. @return void */
    public function setStock($stock)
    {
        $this->stock = $stock;
    }
    /** @param string $synopsis Sinopsis actualizada. @return void */
    public function setSynopsis($synopsis)
    {
        $this->synopsis = $synopsis;
    }
    /** @param float $price Precio actualizado. @return void */
    public function setPrice($price)
    {
        $this->price = $price;
    }
    /** @param string $editorial Editorial actualizada. @return void */
    public function setEditorial($editorial)
    {
        $this->editorial = $editorial;
    }
    /** @param string $cover Portada actualizada. @return void */
    public function setCover($cover)
    {
        $this->cover = $cover;
    }
    /**
     * Devuelve la forma que espera el frontend.
     *
     * @return array<string, mixed> Datos del libro con autor serializado.
     */
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
