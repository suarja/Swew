<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SubmissionRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubmissionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Submission
{
    public const STATUS_PASSED = 'passed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_PENDING = 'pending';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Assignment $assignment = null;

    #[ORM\Column(length: 32)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(length: 32)]
    private string $cliVersion;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $kitVersion = 1;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $checks = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $prompts = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $systemInfo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $logs = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAssignment(): ?Assignment
    {
        return $this->assignment;
    }

    public function setAssignment(Assignment $assignment): self
    {
        $this->assignment = $assignment;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCliVersion(): string
    {
        return $this->cliVersion;
    }

    public function setCliVersion(string $cliVersion): self
    {
        $this->cliVersion = $cliVersion;

        return $this;
    }

    public function getKitVersion(): int
    {
        return $this->kitVersion;
    }

    public function setKitVersion(int $kitVersion): self
    {
        $this->kitVersion = $kitVersion;

        return $this;
    }

    public function getChecks(): ?array
    {
        return $this->checks;
    }

    public function setChecks(?array $checks): self
    {
        $this->checks = $checks;

        return $this;
    }

    public function getPrompts(): ?array
    {
        return $this->prompts;
    }

    public function setPrompts(?array $prompts): self
    {
        $this->prompts = $prompts;

        return $this;
    }

    public function getSystemInfo(): ?array
    {
        return $this->systemInfo;
    }

    public function setSystemInfo(?array $systemInfo): self
    {
        $this->systemInfo = $systemInfo;

        return $this;
    }

    public function getLogs(): ?string
    {
        return $this->logs;
    }

    public function setLogs(?string $logs): self
    {
        $this->logs = $logs;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
