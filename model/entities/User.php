<?php
require_once 'Profile.php';

class User extends Profile {
    private $gender;
    private $cardNumber;
    private $direction;

    public function __construct($profile_code, $email, $user_name, $pswd, $telephone, $name_, $surname, $gender, $cardnumber, $direction) {
        parent::__construct($profile_code, $email, $user_name, $pswd, $telephone, $name_, $surname);
        $this->gender = $gender;
        $this->cardNumber = $cardnumber;
        $this->direction = $direction;
    }
    // Getters
    public function getGender() { return $this->gender; }
    public function getCardNumber() { return $this->cardNumber; }
    public function getDirection() { return $this->direction; }

    // Setters
    public function setGender($gender) { $this->gender = $gender; }
    public function setCardNumber($cardNumber) { $this->cardNumber = $cardNumber; }
    public function setDirection($direction) { $this->direction = $direction; }

    public function __toString() {
        return "User: " . parent::mostrar() . " - Gender: " . $this->gender . " - Card Number: " . $this->cardNumber . " - Direction: " . $this->direction;
    }

    // Conversión a Array para JSON (Combina padre + hijo)
    public function toArray() {
        $data = parent::toArray(); 
        $data['gender'] = $this->gender;
        $data['card_no'] = $this->cardNumber;
        $data['direction'] = $this->direction;
        $data['role'] = 'user'; 
        return $data;
    }
}