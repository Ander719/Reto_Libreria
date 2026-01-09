<?php
class Content
{
    public $id_pedido;
    public $isbn;
    public $quantity;

    public function __construct($id_pedido = null, $isbn = null, $quantity = null)
    {
        $this->id_pedido = $id_pedido;
        $this->isbn = $isbn;
        $this->quantity = $quantity;
    }

    public function getIdPedido()
    {
        return $this->id_pedido;
    }

    public function setIdPedido($id_pedido)
    {
        $this->id_pedido = $id_pedido;
        return $this;
    }

    public function getIsbn()
    {
        return $this->isbn;
    }

    public function setIsbn($isbn)
    {
        $this->isbn = $isbn;
        return $this;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }
}