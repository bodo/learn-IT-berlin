<?php

namespace App\Enums;

enum GroupRole: string
{
    case Owner = 'owner';
    case Moderator = 'moderator';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Owner => __('Owner'),
            self::Moderator => __('Moderator'),
            self::Member => __('Member'),
        };
    }
}
