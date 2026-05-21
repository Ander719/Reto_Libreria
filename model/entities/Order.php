<?php
require_once 'Content.php';

/**
 * Pedido con sus libros comprados.
 */
class Order {
    private $id_order;
    private $userId;
    private $dateBuy;
    private $bought;
    
    private $contents = [];

    /**
     * @param int $id_order Identificador del pedido.
     * @param int $userId Perfil comprador.
     * @param string $dateBuy Fecha de compra.
     * @param bool|int $bought Estado interno de compra.
     */
    public function __construct($id_order, $userId, $dateBuy, $bought) {
        $this->id_order = $id_order;
        $this->userId = $userId;
        $this->dateBuy = $dateBuy;
        $this->bought = $bought;
    }
    /**
     * Alias de addContent(), se mantiene porque ya aparece en codigo anterior.
     *
     * @param Content $content Linea del pedido.
     * @return void
     */
    public function addItem( Content $content) {
        $this->contents[] = $content;
    }

    /** @return int Identificador del pedido. */
    public function getIdOrder() {
        return $this->id_order;
    }
    /** @return int Perfil comprador. */
    public function getUserId() {
        return $this->userId;
    }
    /** @return string Fecha de compra. */
    public function getDateBuy() {
        return $this->dateBuy;
    }
    /** @return bool|int Estado interno de compra. */
    public function getBought() {
        return $this->bought;
    }
    /**
     * Anade una linea de contenido al pedido.
     *
     * @param Content $content Linea del pedido.
     * @return void
     */
    public function addContent(Content $content) {
        $this->contents[] = $content;
    }

    /**
     * Devuelve el pedido con items y total.
     *
     * @return array<string, mixed> Pedido listo para JSON.
     */
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
    /**
     * Calcula el total sumando subtotales serializados de cada linea.
     *
     * @return float Total redondeado a dos decimales.
     */
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
