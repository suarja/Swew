<?php

namespace App\Entity;

use App\Repository\DeviceLoginRequestRepository;
use DateInterval;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeviceLoginRequestRepository::class)]
#[ORM\Index(columns: ['device_code_hash'], name: 'device_code_hash_idx')]
class DeviceLoginRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 128, unique: true)]
    private string $deviceCodeHash;

    #[ORM\Column(length: 16, unique: true)]
    private string $userCode;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $approvedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $consumedAt = null;

    #[ORM\Column(options: ['default' => 5])]
    private int $pollInterval = 5;

    #[ORM\ManyToOne]
    private ?User $user = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->expiresAt = $this->createdAt->add(new DateInterval('PT10M'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeviceCodeHash(): string
    {
        return $this->deviceCodeHash;
    }

    public function setDeviceCodeHash(string $deviceCodeHash): self
    {
        $this->deviceCodeHash = $deviceCodeHash;

        return $this;
    }

    public function getUserCode(): string
    {
        return $this->userCode;
    }

    public function setUserCode(string $userCode): self
    {
        $this->userCode = $userCode;

        return $this;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getApprovedAt(): ?DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function approve(User $user): void
    {
        $this->user = $user;
        $this->approvedAt = new DateTimeImmutable();
    }

    public function getConsumedAt(): ?DateTimeImmutable
    {
        return $this->consumedAt;
    }

    public function consume(): void
    {
        $this->consumedAt = new DateTimeImmutable();
    }

    public function getPollInterval(): int
    {
        return $this->pollInterval;
    }

    public function setPollInterval(int $pollInterval): self
    {
        $this->pollInterval = $pollInterval;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt <= new DateTimeImmutable();
    }

    public function isApproved(): bool
    {
        return $this->approvedAt !== null;
    }

    public function isConsumed(): bool
    {
        return $this->consumedAt !== null;
    }
}
