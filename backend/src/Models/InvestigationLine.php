<?php


class InvestigationLine {
    private ?int $id;
    private string $name;


    public function __construct(?int $id, string $name) {
        $this->id = $id;
        $this->name = $name;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }

    // Setters
    public function setName(string $name): void {
        $this->name = $name;
    }

    public function toArray() : array {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}