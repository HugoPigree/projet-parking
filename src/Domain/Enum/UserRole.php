<?php

namespace App\Domain\Enum;

enum UserRole: string
{
    case USER = 'USER';
    case OWNER = 'OWNER';

    public function isOwner(): bool
    {
        return $this === self::OWNER;
    }

    public function isUser(): bool
    {
        return $this === self::USER;
    }
}

