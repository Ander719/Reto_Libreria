<?php
require_once 'Profile.php';

// Administrador de la tienda
class Admin extends Profile {
    private $currentAccount;

    // Crea un admin con los datos del perfil y su cuenta corriente
    public function __construct($profile_code, $email, $user_name, $pswd, $telephone, $name_, $surname, $currentAccount) {
        parent::__construct($profile_code, $email, $user_name, $pswd, $telephone, $name_, $surname);
        $this->currentAccount = $currentAccount;
    }
    
    // Devuelve la cuenta corriente del administrador
    public function getCurrentAccount() { return $this->currentAccount; }
    // Cambia la cuenta corriente del administrador
    public function setCurrentAccount($currentAccount) { $this->currentAccount = $currentAccount; }
    
    // Muestra los datos del admin para depuracion (puede tener datos sensibles)
    public function __toString() {
        return "Admin: " . parent::mostrar() . " - Current Account: " . $this->currentAccount;
    }
    // Prepara los datos del admin para mandarlos como JSON
    public function toArray() {
        $data = parent::toArray();
        $data['current_account'] = $this->currentAccount;
        return $data;
    }
}
