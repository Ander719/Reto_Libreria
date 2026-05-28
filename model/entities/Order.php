<?php
require_once 'Content.php';

// Pedido con sus libros comprados
class Order {
    private $id_order;
    private $userId;
    private $dateBuy;
    private $bought;
    
    private $contents = [];

    // Guarda los datos del pedido cuando se crea
    public function __construct($id_order, $userId, $dateBuy, $bought) {
        $this->id_order = $id_order;
        $this->userId = $userId;
        $this->dateBuy = $dateBuy;
        $this->bought = $bought;
    }
    // Anade un item al pedido (es lo mismo que addContent)
    public function addItem( Content $content) {
        $this->contents[] = $content;
    }

    // Devuelve el id del pedido
    public function getIdOrder() {
        return $this->id_order;
    }
    // Devuelve el id del usuario que compro
    public function getUserId() {
        return $this->userId;
    }
    // Devuelve la fecha de compra
    public function getDateBuy() {
        return $this->dateBuy;
    }
    // Devuelve si esta comprado o no
    public function getBought() {
        return $this->bought;
    }
    // Anade una linea de contenido al pedido
    public function addContent(Content $content) {
        $this->contents[] = $content;
    }

    // Prepara el pedido con items y total para mandarlo como JSON
    public function toArray() {
    
        $itemsArray = [];
        foreach ($this->contents as $contentObj) {
            $itemsArray[] = $contentObj->toArray();
        }

        return [
            'id_order' => $this->id_order,
            'date_buy' => $this->dateBuy,
            'status'   => 'Completado',
            'total'    => $this->calcularTotal(),
            'items'    => $itemsArray
        ];
    }
    // Calcula el total sumando los subtotales de cada linea
    private function calcularTotal() {
        $total = 0;
        foreach ($this->contents as $c) {
            $item = $c->toArray();
            if (isset($item['subtotal'])) {
                $total += floatval($item['subtotal']);
            }
        }
        return round($total, 2);
    }
}
