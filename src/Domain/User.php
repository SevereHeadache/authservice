<?php

declare(strict_types=1);

namespace SevereHeadache\AuthService\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table('users')]
class User
{
    private const int MAX_ATTEMPTS = 5;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'string', length: 50, unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', length: 60, nullable: false)]
    private string $password;

    #[ORM\Column(type: 'smallint', nullable: false)]
    private int $attempts = 0;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $access = true;

    #[ORM\ManyToMany(targetEntity: Client::class, inversedBy: 'users')]
    private Collection $clients;

    public function __construct() {
        $this->clients = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function getAttemptsExhausted(): bool
    {
        return $this->attempts >= self::MAX_ATTEMPTS;
    }

    public function getIsBlocked(): bool
    {
        return !$this->access;
    }

    public function register(string $name, string $password): void
    {
        $this->name = $name;
        $this->setPassword($password);
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function setPassword(string $password): void
    {
        $this->password = $this->hashPassword($password);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function setAccess(bool $access): void
    {
        $this->access = $access;
    }

    public function resetAttempts(): void
    {
        $this->attempts = 0;
    }

    public function incrementAttempts(): void
    {
        ++$this->attempts;
    }

    public function getAccesses(): Collection
    {
        return $this->clients;
    }

    public function setAccesses(Collection $clients): void
    {
        $this->clients = $clients;
    }
}
