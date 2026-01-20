<?php
class Coment
{
    public  $profile_code;
    public  $isbn;
    public  $coment;
    public  $valoration;
    public  $dateComent; // YYYY-MM-DD

    public function __construct( $profile_code = null, $isbn = null,  $coment = null,  $valoration = null, $dateComent = null)
    {
        if ($profile_code !== null) $this->profile_code = $profile_code;
        if ($isbn !== null) $this->isbn = $isbn;

        $this->coment = $coment;
        $this->valoration = $valoration;
        $this->dateComent = $dateComent;
    }

    public function getProfileCode()
    {
        return $this->profile_code;
    }

    public function setProfileCode( $profile_code)
    {
        $this->profile_code = $profile_code;
    }

    public function getIsbn()
    {
        return $this->isbn;
    }

    public function setIsbn( $isbn)
    {
        $this->isbn = $isbn;
    }

    public function getComent()
    {
        return $this->coment;
    }
    public function setComent( $coment)
    {
        $this->coment = $coment;
    }
    public function getValoration()
    {
        return $this->valoration;
    }
    public function setValoration( $valoration)
    {
        $this->valoration = $valoration;
    }
    public function getDateComent()
    {
        return $this->dateComent;
    }
    public function setDateComent( $dateComent)
    {
        $this->dateComent = $dateComent;
    }
}