<?php

namespace App\Types;

enum UserRoles: string
{
    case ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    case ROLE_ADMIN = 'ROLE_ADMIN';
    case ROLE_EDITOR = 'ROLE_EDITOR';
    case ROLE_ORGANIZATION_ADMIN = 'ROLE_ORGANIZATION_ADMIN';
    case ROLE_ORGANIZATION_EDITOR = 'ROLE_ORGANIZATION_EDITOR';

    case ROLE_USER = 'ROLE_USER';
    case ROLE_API_USER = 'ROLE_API_USER';

    public static function array(): array
    {
        return array_combine(array_column(self::cases(), 'value'), array_column(self::cases(), 'name'));
    }
}
