<?php

// Linea de un pedido
class Content
{
    public $id_pedido;
    public $isbn;
    public $quantity;
    private $price_moment;

    private $bookTitle;
    private $bookCover;

    // Guarda los datos de la linea del pedido cuando se crea
    public function __construct($id_pedido, $isbn, $quantity, $price_moment)
    {
        $this->id_pedido = $id_pedido;
        $this->isbn = $isbn;
        $this->quantity = $quantity;
        $this->price_moment = $price_moment;
    }

    // Devuelve el id del pedido
    public function getIdPedido()
    {
        return $this->id_pedido;
    }

    // Devuelve el ISBN comprado
    public function getIsbn()
    {
        return $this->isbn;
    }

    // Devuelve la cantidad comprada
    public function getQuantity()
    {
        return $this->quantity;
    }
    // Devuelve el precio que tenia en el momento de la compra
    public function getPriceMoment()
    {
        return $this->price_moment;
    }
    // Guarda el titulo y la portada del libro (vienen de un JOIN)
    public function setBookDetails($title, $cover) {
        $this->bookTitle = $title;
        $this->bookCover = $cover;
    }
    // Prepara la linea del pedido para mandarla como JSON con el precio historico
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
