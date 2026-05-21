<?php
require_once 'Profile.php';

/**
 * Usuario comprador, con datos de tarjeta y direccion.
 */
class User extends Profile {
    private $gender;
    private $cardNumber;
    private $direction;

    /**
     * Crea un usuario con los datos comunes del perfil y los datos de compra.
     *
     * @param int $profile_code Codigo del perfil.
     * @param string $email Correo electronico.
     * @param string $user_name Nombre de usuario.
     * @param string $pswd Hash de contrasena.
     * @param string $telephone Telefono.
     * @param string $name_ Nombre propio.
     * @param string $surname Apellidos.
     * @param string $gender Genero.
     * @param string $cardnumber Numero de tarjeta.
     * @param string $direction Direccion postal.
     */
    public function __construct($profile_code, $email, $user_name, $pswd, $telephone, $name_, $surname, $gender, $cardnumber, $direction) {
        parent::__construct($profile_code, $email, $user_name, $pswd, $telephone, $name_, $surname);
        $this->gender = $gender;
        $this->cardNumber = $cardnumber;
        $this->direction = $direction;
    }
    /** @return string Genero del usuario. */
    public function getGender() { return $this->gender; }
    /** @return string Numero de tarjeta almacenado. */
    public function getCardNumber() { return $this->cardNumber; }
    /** @return string Direccion postal. */
    public function getDirection() { return $this->direction; }

    /** @param string $gender Genero actualizado. @return void */
    public function setGender($gender) { $this->gender = $gender; }
    /** @param string $cardNumber Numero de tarjeta actualizado. @return void */
    public function setCardNumber($cardNumber) { $this->cardNumber = $cardNumber; }
    /** @param string $direction Direccion postal actualizada. @return void */
    public function setDirection($direction) { $this->direction = $direction; }

    /**
     * Devuelve una cadena de depuracion; puede contener datos sensibles.
     *
     * @return string Representacion interna del usuario.
     */
    public function __toString() {
        return "User: " . parent::mostrar() . " - Gender: " . $this->gender . " - Card Number: " . $this->cardNumber . " - Direction: " . $this->direction;
    }

    /**
     * Devuelve datos seguros para JSON. La tarjeta se reduce a has_card.
     *
     * @return array<string, mixed> Datos publicos del usuario.
     */
    public function toArray() {
        $data = parent::toArray(); 
        $data['gender'] = $this->gender;
        $data['has_card'] = !empty($this->cardNumber);
        $data['direction'] = $this->direction;
        return $data;
    }
}
