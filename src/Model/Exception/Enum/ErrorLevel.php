<?php

namespace App\Model\Exception\Enum;

enum ErrorLevel: int
{
    case WARNING = 0;
    case DANGER = 1;

    public function getFlashClass()
    {
        return match ($this) {
            ErrorLevel::WARNING => 'warning',
            ErrorLevel::DANGER => 'danger'
        };
    }
}