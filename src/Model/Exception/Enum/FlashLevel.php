<?php

namespace App\Model\Exception\Enum;

enum FlashLevel: string
{
    case WARNING = 'warning';
    case DANGER = 'danger';
    case SUCCESS = 'success';
}