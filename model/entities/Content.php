<?php

/**
 * Linea de un pedido.
 */
class Content
{
    public $id_pedido;
    public $isbn;
    public $quantity;
    private $price_moment;

    private $bookTitle;
    private $bookCover;

    /**
     * @param int $id_pedido Identificador del pedido.
     * @param string $isbn ISBN comprado.
     * @param int $quantity Cantidad comprada.
     * @param float $price_moment Precio unitario en el momento de la compra.
     */
    public function __construct($id_pedido, $isbn, $quantity, $price_moment)
    {
        $this->id_pedido = $id_pedido;
        $this->isbn = $isbn;
        $this->quantity = $quantity;
        $this->price_moment = $price_moment;
    }

    /** @return int Identificador del pedido. */
    public function getIdPedido()
    {
        return $this->id_pedido;
    }

    /** @return string ISBN comprado. */
    public function getIsbn()
    {
        return $this->isbn;
    }

    /** @return int Cantidad comprada. */
    public function getQuantity()
    {
        return $this->quantity;
    }
    /** @return float Precio unitario historico. */
    public function getPriceMoment()
    {
        return $this->price_moment;
    }
    /**
     * Anade titulo y portada obtenidos por JOIN.
     *
     * @param string $title Titulo del libro.
     * @param string $cover Portada del libro.
     * @return void
     */
    public function setBookDetails($title, $cover) {
        $this->bookTitle = $title;
        $this->bookCover = $cover;
    }
    /**
     * Devuelve la linea con el precio que tenia el libro al comprarlo.
     *
     * @return array<string, mixed> Linea lista para JSON.
     */
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
