<?php
class MemberInvestigation {
    private ?int $id;
    private int $memberId;
    private int $lineId;
    private string $startDate;
    private ?string $endDate;

    public function __construct(?int $id, int $memberId, int $lineId, string $startDate, ?string $endDate = null) {
        $this->id        = $id;
        $this->memberId  = $memberId;
        $this->lineId    = $lineId;
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    // Getters
    public function getId(): ?int          { return $this->id; }
    public function getMemberId(): int     { return $this->memberId; }
    public function getLineId(): int       { return $this->lineId; }
    public function getStartDate(): string { return $this->startDate; }
    public function getEndDate(): ?string  { return $this->endDate; }

    // Setters
    public function setEndDate(?string $endDate): void  { $this->endDate = $endDate; }
    public function setStartDate(string $startDate): void { $this->startDate = $startDate; }

    public function toArray(): array {
        return [
            'id'         => $this->id,
            'member_id'  => $this->memberId,
            'line_id'    => $this->lineId,
            'start_date' => $this->startDate,
            'end_date'   => $this->endDate,
        ];
    }
}