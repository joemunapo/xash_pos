<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case User = 'user';
    case Manager = 'manager';
    case Cashier = 'cashier';

    public function redirectPath(): string
    {
        return match ($this) {
            self::Admin => '/admin/dashboard',
            self::Cashier => '/cashier/dashboard',
            self::User => '/user/dashboard',
            self::Manager => '/manager/mobile-only',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Manager => 'Branch Manager',
            self::Cashier => 'Cashier',
            self::User => 'User',
        };
    }
}
