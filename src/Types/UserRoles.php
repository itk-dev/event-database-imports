<?php

namespace App\Types;

enum UserRoles: string
{
    case ROLE_USER = 'USER';
    case ROLE_ADMIN = 'ADMIN';
    case ROLE_API_USER = 'API USER';

    public static function array(): array
    {
        return array_combine(array_column(self::cases(), 'value'), array_column(self::cases(), 'name'));
    }
}
