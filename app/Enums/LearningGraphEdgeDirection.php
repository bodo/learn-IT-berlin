<?php

namespace App\Enums;

enum LearningGraphEdgeDirection: string
{
    case None = 'none';
    case To = 'to';
    case From = 'from';
    case Both = 'both';

    public function arrows(): string
    {
        return match ($this) {
            self::None => '',
            self::To => 'to',
            self::From => 'from',
            self::Both => 'to, from',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::None => __('No arrows'),
            self::To => __('Arrow to target'),
            self::From => __('Arrow to source'),
            self::Both => __('Arrows both ways'),
        };
    }
}
