<?php

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

    public function getTitle()
    {
        return $this->title;
    }
    public function getAuthor()
    {
        return $this->author;
    }
    public function getIsbn()
    {
        return $this->isbn;
    }
    public function getPages()
    {
        return $this->pages;
    }
    public function getStock()
    {
        return $this->stock;
    }
    public function getSynopsis()
    {
        return $this->synopsis;
    }
    public function getPrice()
    {
        return $this->price;
    }
    public function getEditorial()
    {
        return $this->editorial;
    }
    public function getCover()
    {
        return $this->cover;
    }
    public function setTitle($title)
    {
        $this->title = $title;
    }
    public function setAuthor($author)
    {
        $this->author = $author;
    }
    public function setIsbn($isbn)
    {
        $this->isbn = $isbn;
    }
    public function setPages($pages)
    {
        $this->pages = $pages;
    }
    public function setStock($stock)
    {
        $this->stock = $stock;
    }
    public function setSynopsis($synopsis)
    {
        $this->synopsis = $synopsis;
    }
    public function setPrice($price)
    {
        $this->price = $price;
    }
    public function setEditorial($editorial)
    {
        $this->editorial = $editorial;
    }
    public function setCover($cover)
    {
        $this->cover = $cover;
    }
    public function toArray()
    {
        return [
            'isbn' => $this->isbn,
            'title' => $this->title,
            'author' => $this->author->toArray(), // Asumiendo que $author es un objeto Author
            'pages' => $this->pages,
            'stock' => $this->stock,
            'synopsis' => $this->synopsis,
            'price' => $this->price,
            'editorial' => $this->editorial,
            'cover' => $this->cover,
        ];
    }
}
