<?php

// Datos que comparten usuarios y administradores
 abstract class Profile {
    private $profile_code;
    private $email;
    private $user_name;
    private $pswd;
    private $telephone;
    private $name_;
    private $surname;

    // Guarda los datos del perfil cuando se crea un usuario o admin
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

    // Devuelve el codigo del perfil
    public function getProfile_code() { return $this->profile_code; }
    // Devuelve el correo electronico
    public function getEmail() { return $this->email; }
    // Devuelve el nombre de usuario
    public function getUser_name() { return $this->user_name; }
    // Devuelve el hash de la contrasena (solo para verificacion, no para mostrar)
    public function getPswd() { return $this->pswd; }
    // Devuelve el telefono de contacto
    public function getTelephone() { return $this->telephone; }
    // Devuelve el nombre propio
    public function getName_() { return $this->name_; }
    // Devuelve los apellidos
    public function getSurname() { return $this->surname; }

    // Muestra los datos del perfil para depuracion (no mandar al frontend)
    public function mostrar() 
    {
        return "[$this->profile_code] $this->email - $this->user_name - $this->pswd - $this->telephone - $this->name_ - $this->surname";
    }
    // Pasa el perfil a array sin incluir la contrasena para mandarlo como JSON
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
    
    // Devuelve un texto con los datos del perfil para depuracion
    public abstract function __toString();   
}
?>
