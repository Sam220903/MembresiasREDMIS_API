<?php 
class State {
    private ?int $id;
    private string $name;
    private int $countryId;

    public function __construct(?int $id, string $name, int $countryId){
        $this->id = $id;
        $this->name = $name;
        $this->countryId = $countryId;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getCountryId(): ?int { return $this->countryId; }

    // Setters
    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setCountryId(?int $countryId) : void {
        $this->countryId = $countryId;
    }

    public function toArray() : array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'countryId' => $this->countryId
        ];
    }
}