<?php
<<<<<<< HEAD
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

=======

namespace App\Domain\Entity;

use App\Domain\ValueObject\Email;
use App\Domain\Enum\UserRole;

class User
{
    private ?int $id;
    private Email $email;
    private string $passwordHash;
    private UserRole $role;
    private string $nom;
    private string $prenom;
    private \DateTimeImmutable $createdAt;

    public function __construct(
        Email $email,
        string $passwordHash,
        string $nom,
        string $prenom,
        UserRole $role = UserRole::USER,
        ?int $id = null,
        ?\DateTimeImmutable $createdAt = null
    ) {
        $this->validateName($nom, 'nom');
        $this->validateName($prenom, 'prénom');

        $this->id = $id;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->nom = trim($nom);
        $this->prenom = trim($prenom);
        $this->role = $role;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
    }

    private function validateName(string $name, string $field): void
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException("Le $field est obligatoire");
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Assigne un ID (uniquement si non déjà assigné)
     * Utilisé par le repository lors de la création
     */
    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException("L'ID est déjà assigné");
        }
        $this->id = $id;
    }

    public function getEmail(): string
    {
        return $this->email->getValue();
    }

    public function getEmailObject(): Email
    {
        return $this->email;
    }

    /**
     * Vérifie si le mot de passe fourni correspond au hash stocké
     * Le hash n'est JAMAIS exposé directement
     */
    public function verifyPassword(string $password, callable $verifier): bool
    {
        return $verifier($password, $this->passwordHash);
    }

    /**
     * Retourne le hash du mot de passe (usage interne repository uniquement)
     * @internal
     */
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function getRoleValue(): string
    {
        return $this->role->value;
    }

    public function isOwner(): bool
    {
        return $this->role === UserRole::OWNER;
    }

    public function isUser(): bool
    {
        return $this->role === UserRole::USER;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getFullName(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Convertit en tableau pour la sérialisation
     * NE CONTIENT PAS le mot de passe
     */
>>>>>>> origin/feat/subscription-session
    public function toArray(): array
    {
        return [
            'id' => $this->id,
<<<<<<< HEAD
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
=======
            'email' => $this->getEmail(),
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'role' => $this->role->value,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
>>>>>>> origin/feat/subscription-session
        ];
    }
}
