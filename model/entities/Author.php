<?php
class Author {
    private $id_author;
    private $name;
    private $lastName;

    public function __construct($id_author, $name, $lastName) {
        $this->id_author = $id_author;
        $this->name = $name;
        $this->lastName = $lastName;
    }
    // Getters / Setters
    public function getId(): ?int { return $this->id_author; }
    public function getName(): ?string { return $this->name; }
    public function getApellido(): ?string { return $this->lastName; }

    public function setId(?int $id_author): void { $this->id_author = $id_author; }
    public function setName(?string $name): void { $this->name = $name; }
    public function setApellido(?string $lastName): void { $this->lastName = $lastName; }

    // ESTO ES LO IMPORTANTE:
    public function toArray() {
        return [
            'id' => $this->id_author,
            'name' => $this->name,
            'lastname' => $this->lastName
        ];
    }
}