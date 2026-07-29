<?php
class Member {
    private ?int $id;
    private string $name;
    private string $lastName;
    private string $gender;
    private ?string $code;
    private bool $verified;
    private bool $active;
    private ?int $universityId;
    private ?int $stateId;
    private ?int $countryId;
    private ?int $statusId;
    private ?int $userTypeId;
    private ?int $investigationLineId;

    public function __construct(
        ?int $id,
        string $name,
        string $lastName,
        string $gender,
        ?int $universityId = null,
        ?int $stateId = null,
        ?int $countryId = null,
        ?int $statusId = null,
        ?int $userTypeId = null,
        ?string $code = null,
        bool $verified = false,
        bool $active = true,
        ?int $investigationLineId = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->lastName = $lastName;
        $this->gender = $gender;
        $this->universityId = $universityId;
        $this->stateId = $stateId;
        $this->countryId = $countryId;
        $this->statusId = $statusId;
        $this->userTypeId = $userTypeId;
        $this->code = $code;
        $this->verified = $verified;
        $this->active = $active;
        $this->investigationLineId = $investigationLineId;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getLastName(): string { return $this->lastName; }
    public function getGender(): string { return $this->gender; }
    public function getCode(): ?string { return $this->code; }
    public function isVerified(): bool { return $this->verified; }
    public function isActive(): bool { return $this->active; }
    public function getUniversityId(): ?int { return $this->universityId; }
    public function getStateId(): ?int { return $this->stateId; }
    public function getCountryId(): ?int { return $this->countryId; }
    public function getStatusId(): ?int { return $this->statusId; }
    public function getUserTypeId(): ?int { return $this->userTypeId; }
    public function getInvestigationLineId(): ?int { return $this->investigationLineId; }

    // Setters
    public function setName(string $name): void { $this->name = $name; }
    public function setLastName(string $lastName): void { $this->lastName = $lastName; }
    public function setGender(string $gender): void { $this->gender = $gender; }
    public function setCode(?string $code): void { $this->code = $code; }
    public function setVerified(bool $verified): void { $this->verified = $verified; }
    public function setActive(bool $active): void { $this->active = $active; }
    public function setUniversityId(?int $universityId): void { $this->universityId = $universityId; }
    public function setStateId(?int $stateId): void { $this->stateId = $stateId; }
    public function setCountryId(?int $countryId): void { $this->countryId = $countryId; }
    public function setStatusId(?int $statusId): void { $this->statusId = $statusId; }
    public function setUserTypeId(?int $userTypeId): void { $this->userTypeId = $userTypeId; }
    public function setInvestigationLineId(?int $investigationLineId): void { $this->investigationLineId = $investigationLineId; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'nombre' => $this->name,
            'apellidos' => $this->lastName,
            'genero' => $this->gender,
            'codigo' => $this->code,
            'verificado' => $this->verified,
            'activo' => $this->active,
            'universidadId' => $this->universityId,
            'estadoId' => $this->stateId,
            'paisId' => $this->countryId,
            'estatusId' => $this->statusId,
            'tipoUsuarioId' => $this->userTypeId,
            'investigationLineId' => $this->investigationLineId,
        ];
    }
}
