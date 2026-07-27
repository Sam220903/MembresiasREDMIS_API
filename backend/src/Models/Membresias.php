<?php
class Membresias {
    private ?int $id;
    private string $name;
    private string $type;
    private bool $active;

    public function __construct(?int $id, string $name, string $type, bool $active = true) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->active = $active;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getType(): string { return $this->type; }
    public function isActive(): bool { return $this->active; }

    // Setters
    public function setName(string $name): void { $this->name = $name; }
    public function setType(string $type): void { $this->type = $type; }
    public function setActive(bool $active): void { $this->active = $active; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'nombre' => $this->name,
            'tipo' => $this->type,
        ];
    }
}
