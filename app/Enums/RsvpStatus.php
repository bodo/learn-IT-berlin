<?php

namespace App\Enums;

enum RsvpStatus: string
{
    case Going = 'going';
    case NotGoing = 'not_going';
    case Interested = 'interested';

    public function label(): string
    {
        return match ($this) {
            self::Going => __('Going'),
            self::NotGoing => __('Not Going'),
            self::Interested => __('Interested'),
        };
    }
}

