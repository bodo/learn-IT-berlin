<?php

namespace App\Enums;

enum LearningGraphBlockType: string
{
    case Text = 'text';
    case Image = 'image';

    public function label(): string
    {
        return match ($this) {
            self::Text => __('Text block'),
            self::Image => __('Image block'),
        };
    }
}
