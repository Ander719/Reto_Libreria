<?php

class Order {
    private $id_order;
    private $userId;
    private $dateBuy;
    private $buyed;

    public function __construct($id_order, $userId, $orderDate) {
        $this->id_order = $id_order;
        $this->userId = $userId;
        $this->dateBuy = $dateBuy;
        $this->buyed = $buyed;
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
    public function getBuyed() {
        return $this->buyed;
    }
    public function setIdOrder($id_order) {
        $this->id_order = $id_order;
    }
    public function setUserId($userId) {
        $this->userId = $userId;
    }
    public function setDateBuy($dateBuy) {
        $this->dateBuy = $dateBuy;
    }
    public function setBuyed($buyed) {
        $this->buyed = $buyed;
    }
}