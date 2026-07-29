<?php
class Cv {
    private ?int $id;
    private int $memberId;
    private string $fileName;
    private ?string $uploadedAt;

    public function __construct(?int $id, int $memberId, string $fileName, ?string $uploadedAt = null) {
        $this->id = $id;
        $this->memberId = $memberId;
        $this->fileName = $fileName;
        $this->uploadedAt = $uploadedAt;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getMemberId(): int { return $this->memberId; }
    public function getFileName(): string { return $this->fileName; }
    public function getUploadedAt(): ?string { return $this->uploadedAt; }

    // Setters
    public function setFileName(string $fileName): void { $this->fileName = $fileName; }
    public function setUploadedAt(?string $uploadedAt): void { $this->uploadedAt = $uploadedAt; }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'memberId' => $this->memberId,
            'fileName' => $this->fileName,
            'uploadedAt' => $this->uploadedAt,
        ];
    }
}
