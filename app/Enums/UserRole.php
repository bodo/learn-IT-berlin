<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case TrustedUser = 'trusted_user';
    case Admin = 'admin';
    case Superuser = 'superuser';

    public function label(): string
    {
        return match ($this) {
            self::User => __('User'),
            self::TrustedUser => __('Trusted User'),
            self::Admin => __('Admin'),
            self::Superuser => __('Superuser'),
        };
    }

    public function rank(): int
    {
        return match ($this) {
            self::User => 10,
            self::TrustedUser => 20,
            self::Admin => 30,
            self::Superuser => 40,
        };
    }

    public function isAtLeast(self $other): bool
    {
        return $this->rank() >= $other->rank();
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $case) => ['value' => $case->value, 'label' => $case->label()])
            ->all();
    }
}
