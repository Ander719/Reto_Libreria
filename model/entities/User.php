<?php
require_once 'Profile.php';

class User extends Profile {
    private $gender;
    private $cardNumber;

    public function __construct($profile_code, $email, $user_name, $pswd, $telephone, $name_, $surname, $gender, $cardnumber) {
        parent::__construct($profile_code, $email, $user_name, $pswd, $telephone, $name_, $surname);
        $this->gender = $gender;
        $this->cardNumber = $cardnumber;
    }
    // Getters
    public function getGender() { return $this->gender; }
    public function getCardNumber() { return $this->cardNumber; }

    // Setters
    public function setGender($gender) { $this->gender = $gender; }
    public function setCardNumber($cardNumber) { $this->cardNumber = $cardNumber; }

    public function __toString() {
        return "User: " . parent::mostrar() . " - Gender: " . $this->gender . " - Card Number: " . $this->cardNumber;
    }

    // Conversión a Array para JSON (Combina padre + hijo)
    public function toArray() {
        $data = parent::toArray(); // Obtiene los datos básicos (Profile)
        $data['gender'] = $this->gender;
        $data['card_number'] = $this->cardNumber;
        $data['role'] = 'user'; // Útil para el frontend saber qué es
        return $data;
    }
}