<?php

// Autor que puede tener uno o varios libros
class Author {
    private $id_author;
    private $name;
    private $lastName;

    // Guarda los datos del autor cuando se crea
    public function __construct($id_author, $name, $lastName) {
        $this->id_author = $id_author;
        $this->name = $name;
        $this->lastName = $lastName;
    }
    // Devuelve el id del autor
    public function getId(): ?int { return $this->id_author; }
    // Devuelve el nombre del autor
    public function getName(): ?string { return $this->name; }
    // Devuelve el apellido del autor
    public function getApellido(): ?string { return $this->lastName; }

    // Cambia el id del autor
    public function setId(?int $id_author): void { $this->id_author = $id_author; }
    // Cambia el nombre del autor
    public function setName(?string $name): void { $this->name = $name; }
    // Cambia el apellido del autor
    public function setApellido(?string $lastName): void { $this->lastName = $lastName; }

    // Prepara los datos del autor para mandarlos como JSON
    public function toArray() {
        return [
            'id' => $this->id_author,
            'name' => $this->name,
            'lastname' => $this->lastName
        ];
    }
}
