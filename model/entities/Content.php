<?php
class Content
{
    public $id_pedido;
    public $isbn;
    public $quantity;
    private $price_moment;

    private $bookTitle;
    private $bookCover;

    public function __construct($id_pedido, $isbn, $quantity, $price_moment)
    {
        $this->id_pedido = $id_pedido;
        $this->isbn = $isbn;
        $this->quantity = $quantity;
        $this->price_moment = $price_moment;
    }

    public function getIdPedido()
    {
        return $this->id_pedido;
    }

    public function getIsbn()
    {
        return $this->isbn;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }
    public function getPriceMoment()
    {
        return $this->price_moment;
    }
    public function setBookDetails($title, $cover) {
        $this->bookTitle = $title;
        $this->bookCover = $cover;
    }
    public function toArray() {
        return [
            'isbn'        => $this->isbn,
            'quantity'    => intval($this->quantity),
            'price_unit'  => floatval($this->price_moment), // Precio histórico
            'subtotal'    => floatval($this->price_moment) * intval($this->quantity),
            // Datos visuales del libro
            'title'       => $this->bookTitle,
            'cover'       => $this->bookCover
        ];
    }
}