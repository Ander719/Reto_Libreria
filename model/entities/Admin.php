<?php
require_once 'Profile.php';

/**
 * Administrador de la tienda.
 */
class Admin extends Profile {
    private $currentAccount;

    /**
     * Crea un admin con los datos comunes del perfil y su cuenta corriente.
     *
     * @param int $profile_code Codigo del perfil.
     * @param string $email Correo electronico.
     * @param string $user_name Nombre de usuario.
     * @param string $pswd Hash de contrasena.
     * @param string $telephone Telefono.
     * @param string $name_ Nombre propio.
     * @param string $surname Apellidos.
     * @param string $currentAccount Cuenta corriente.
     */
    public function __construct($profile_code, $email, $user_name, $pswd, $telephone, $name_, $surname, $currentAccount) {
        parent::__construct($profile_code, $email, $user_name, $pswd, $telephone, $name_, $surname);
        $this->currentAccount = $currentAccount;
    }
    
    /** @return string Cuenta corriente del administrador. */
    public function getCurrentAccount() { return $this->currentAccount; }
    /** @param string $currentAccount Cuenta corriente actualizada. @return void */
    public function setCurrentAccount($currentAccount) { $this->currentAccount = $currentAccount; }
    
    /**
     * Devuelve una cadena de depuracion; puede contener datos sensibles.
     *
     * @return string Representacion interna del administrador.
     */
    public function __toString() {
        return "Admin: " . parent::mostrar() . " - Current Account: " . $this->currentAccount;
    }
    /**
     * Devuelve los datos del admin para la API.
     *
     * @return array<string, mixed> Datos publicos del administrador.
     */
    public function toArray() {
        $data = parent::toArray();
        $data['current_account'] = $this->currentAccount;
        return $data;
    }
}
