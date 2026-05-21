<?php

/**
 * Datos comunes que comparten usuarios y administradores.
 */
 abstract class Profile {
    private $profile_code;
    private $email;
    private $user_name;
    private $pswd;
    private $telephone;
    private $name_;
    private $surname;

    /**
     * Crea la parte comun del perfil.
     *
     * @param int $profile_code Codigo interno del perfil.
     * @param string $email Correo electronico.
     * @param string $user_name Nombre de usuario unico.
     * @param string $pswd Hash de contrasena.
     * @param string $telephone Telefono de contacto.
     * @param string $name_ Nombre propio.
     * @param string $surname Apellidos.
     */
    public function __construct($profile_code, $email, $user_name, $pswd, $telephone, $name_, $surname) 
    {
        $this->profile_code = $profile_code;
        $this->email = $email;
        $this->user_name = $user_name;
        $this->pswd = $pswd;
        $this->telephone = $telephone;
        $this->name_ = $name_;
        $this->surname = $surname;
    }

    /** @return int Codigo interno del perfil. */
    public function getProfile_code() { return $this->profile_code; }
    /** @return string Correo electronico. */
    public function getEmail() { return $this->email; }
    /** @return string Nombre de usuario. */
    public function getUser_name() { return $this->user_name; }
    /** @return string Hash usado solo para password_verify(), nunca para respuestas API. */
    public function getPswd() { return $this->pswd; }
    /** @return string Telefono de contacto. */
    public function getTelephone() { return $this->telephone; }
    /** @return string Nombre propio. */
    public function getName_() { return $this->name_; }
    /** @return string Apellidos. */
    public function getSurname() { return $this->surname; }

    /**
     * Devuelve una representacion de depuracion del perfil.
     *
     * @return string Cadena con campos internos; no debe usarse como salida publica.
     */
    public function mostrar() 
    {
        return "[$this->profile_code] $this->email - $this->user_name - $this->pswd - $this->telephone - $this->name_ - $this->surname";
    }
    /**
     * Pasa el perfil a array sin incluir la contrasena.
     *
     * @return array<string, mixed> Datos publicos del perfil sin la contrasena.
     */
    public function toArray() {
        return [
            'profile_code' => $this->profile_code,
            'email' => $this->email,
            'user_name' => $this->user_name,
            'telephone' => $this->telephone,
            'name_' => $this->name_,
            'surname' => $this->surname
        ];
    }
    
    /**
     * Representacion textual de la entidad concreta.
     *
     * @return string Texto descriptivo para depuracion.
     */
    public abstract function __toString();   
}
?>
