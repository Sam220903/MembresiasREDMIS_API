<?php
class MembresiaUsuario {
    private ?int $id;
    private int $memberId;
    private int $membershipId;
    private ?string $startDate;
    private ?string $endDate;
    private string $status;

    public function __construct(
        ?int $id,
        int $memberId,
        int $membershipId,
        ?string $startDate = null,
        ?string $endDate = null,
        string $status = 'ACTIVA'
    ) {
        $this->id = $id;
        $this->memberId = $memberId;
        $this->membershipId = $membershipId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getMemberId(): int { return $this->memberId; }
    public function getMembershipId(): int { return $this->membershipId; }
    public function getStartDate(): ?string { return $this->startDate; }
    public function getEndDate(): ?string { return $this->endDate; }
    public function getStatus(): string { return $this->status; }

    // Setters
    public function setStartDate(?string $startDate): void { $this->startDate = $startDate; }
    public function setEndDate(?string $endDate): void { $this->endDate = $endDate; }
    public function setStatus(string $status): void { $this->status = $status; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'memberId' => $this->memberId,
            'membershipId' => $this->membershipId,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'status' => $this->status,
        ];
    }
}
