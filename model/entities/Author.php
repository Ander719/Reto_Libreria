<?php

/**
 * Autor asociado a uno o varios libros.
 */
class Author {
    private $id_author;
    private $name;
    private $lastName;

    /**
     * @param int|null $id_author Identificador del autor.
     * @param string|null $name Nombre del autor.
     * @param string|null $lastName Apellido del autor.
     */
    public function __construct($id_author, $name, $lastName) {
        $this->id_author = $id_author;
        $this->name = $name;
        $this->lastName = $lastName;
    }
    /** @return int|null Identificador del autor. */
    public function getId(): ?int { return $this->id_author; }
    /** @return string|null Nombre del autor. */
    public function getName(): ?string { return $this->name; }
    /** @return string|null Apellido del autor; se conserva el alias por compatibilidad. */
    public function getApellido(): ?string { return $this->lastName; }

    /** @param int|null $id_author Identificador actualizado. @return void */
    public function setId(?int $id_author): void { $this->id_author = $id_author; }
    /** @param string|null $name Nombre actualizado. @return void */
    public function setName(?string $name): void { $this->name = $name; }
    /** @param string|null $lastName Apellido actualizado; se conserva el alias por compatibilidad. @return void */
    public function setApellido(?string $lastName): void { $this->lastName = $lastName; }

    /**
     * Devuelve las claves que ya usa el frontend.
     *
     * @return array{id:int|null,name:string|null,lastname:string|null}
     */
    public function toArray() {
        return [
            'id' => $this->id_author,
            'name' => $this->name,
            'lastname' => $this->lastName
        ];
    }
}
