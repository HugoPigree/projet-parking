<?php

namespace App\Domain\ValueObject;

class Email
{
    private string $value;

    public function __construct(string $email)
    {
        $email = trim($email);
        
        if (empty($email)) {
            throw new \InvalidArgumentException("L'email ne peut pas être vide");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Email invalide: $email");
        }
        
        $this->value = strtolower($email);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

