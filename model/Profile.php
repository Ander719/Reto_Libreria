<?php
 abstract class Profile {
    private $profile_code;
    private $email;
    private $user_name;
    private $pswd;
    private $telephone;
    private $name_;
    private $surname;

    public function __construct($profile_code, $email, $user_name, $pswd, $telephone, $name_, $surname) {
        $this->profile_code = $profile_code;
        $this->email = $email;
        $this->user_name = $user_name;
        $this->pswd = $pswd;
        $this->telephone = $telephone;
        $this->name_ = $name_;
        $this->surname = $surname;
    }

    public function getProfile_code() { return $this->profile_code; }
    public function getEmail() { return $this->email; }
    public function getUser_name() { return $this->user_name; }
    public function getPswd() { return $this->pswd; }
    public function getTelephone() { return $this->telephone; }
    public function getName_() { return $this->name_; }
    public function getSurname() { return $this->surname; }

    public function mostrar() {
        return "[$this->profile_code] $this->email - $this->user_name" - $this->pswd - $this->telephone - $this->name_ - $this->surname;
    }
    
    public abstract function __toString();

    
}
?>