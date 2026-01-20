<?php
require_once 'Profile.php';

class Admin extends Profile {
    private $currentAccount;

    public function __construct($profile_code, $email, $user_name, $pswd, $telephone, $name_, $surname, $currentAccount) {
        parent::__construct($profile_code, $email, $user_name, $pswd, $telephone, $name_, $surname);
        $this->currentAccount = $currentAccount;
    }
    
    // Getters y Setters
    public function getCurrentAccount() { return $this->currentAccount; }
    public function setCurrentAccount($currentAccount) { $this->currentAccount = $currentAccount; }
    
    public function __toString() {
        return "Admin: " . parent::mostrar() . " - Current Account: " . $this->currentAccount;
    }
    // Conversión a Array para JSON
    public function toArray() {
        $data = parent::toArray();
        $data['current_account'] = $this->currentAccount;
        $data['role'] = 'admin';
        return $data;
    }
}