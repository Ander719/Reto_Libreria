<?php
require_once 'Content.php';

class Order {
    private $id_order;
    private $userId;
    private $dateBuy;
    private $bought;
    
    private $contents = [];

    public function __construct($id_order, $userId, $dateBuy, $bought) {
        $this->id_order = $id_order;
        $this->userId = $userId;
        $this->dateBuy = $dateBuy;
        $this->bought = $bought;
    }
    public function addItem( Content $content) {
        $this->contents[] = $content;
    }

    public function getIdOrder() {
        return $this->id_order;
    }
    public function getUserId() {
        return $this->userId;
    }
    public function getDateBuy() {
        return $this->dateBuy;
    }
    public function getBought() {
        return $this->bought;
    }
    public function addContent(Content $content) {
        $this->contents[] = $content;
    }

    public function toArray() {
        // Convertimos cada objeto Content a array
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