<?php
require_once 'Profile.php';

// Usuario comprador, con datos de tarjeta y direccion
class User extends Profile {
    private $gender;
    private $cardNumber;
    private $direction;

    // Crea un usuario con los datos del perfil y los datos de compra
    public function __construct($profile_code, $email, $user_name, $pswd, $telephone, $name_, $surname, $gender, $cardnumber, $direction) {
        parent::__construct($profile_code, $email, $user_name, $pswd, $telephone, $name_, $surname);
        $this->gender = $gender;
        $this->cardNumber = $cardnumber;
        $this->direction = $direction;
    }
    // Devuelve el genero del usuario
    public function getGender() { return $this->gender; }
    // Devuelve el numero de tarjeta
    public function getCardNumber() { return $this->cardNumber; }
    // Devuelve la direccion postal
    public function getDirection() { return $this->direction; }

    // Cambia el genero del usuario
    public function setGender($gender) { $this->gender = $gender; }
    // Cambia el numero de tarjeta
    public function setCardNumber($cardNumber) { $this->cardNumber = $cardNumber; }
    // Cambia la direccion postal
    public function setDirection($direction) { $this->direction = $direction; }

    // Muestra los datos del usuario para depuracion (puede tener datos sensibles)
    public function __toString() {
        return "User: " . parent::mostrar() . " - Gender: " . $this->gender . " - Card Number: " . $this->cardNumber . " - Direction: " . $this->direction;
    }

    // Prepara los datos del usuario para JSON, la tarjeta solo muestra ultimos 4 digitos
    public function toArray() {
        $data = parent::toArray(); 
        $data['gender'] = $this->gender;
        $data['has_card'] = !empty($this->cardNumber);
        $data['card_last_four'] = !empty($this->cardNumber) ? substr($this->cardNumber, -4) : null;
        $data['direction'] = $this->direction;
        return $data;
    }
}
