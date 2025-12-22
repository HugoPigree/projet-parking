<?php
namespace App\Domain\Entity;

use DateTime;

class User
{
    private ?int $id = null;
    private string $uuid;

    private string $firstName;
    private string $lastName;
    private string $email;
    private ?string $phone;

    private string $passwordHash;
    private string $role; // CLIENT | ADMIN

    private bool $isActive = true;
    private ?DateTime $emailVerifiedAt = null;

    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        string $uuid,
        string $firstName,
        string $lastName,
        string $email,
        string $passwordHash,
        string $role,
        ?string $phone = null
    ) {
        $this->uuid = $uuid;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
        $this->phone = $phone;

        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    public function getUuid(): string { return $this->uuid; }

    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getEmail(): string { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }

    public function getRole(): string { return $this->role; }
    public function isActive(): bool { return $this->isActive; }
    public function getEmailVerifiedAt(): ?DateTime { return $this->emailVerifiedAt; }

    /** Active l'utilisateur */
    public function activate(): void
    {
        $this->isActive = true;
        $this->touch();
    }

    /** Désactive l'utilisateur */
    public function deactivate(): void
    {
        $this->isActive = false;
        $this->touch();
    }

    /** Marque l'email comme vérifié */
    public function verifyEmail(): void
    {
        $this->emailVerifiedAt = new DateTime();
        $this->touch();
    }

    /** Vérification du mot de passe */
    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->passwordHash);
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function getCreatedAt(): DateTime { return $this->createdAt; }
    public function getUpdatedAt(): DateTime { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'is_active' => $this->isActive,
            'email_verified_at' => $this->emailVerifiedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
