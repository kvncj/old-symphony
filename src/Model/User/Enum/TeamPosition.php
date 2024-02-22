<?php

namespace App\Model\User\Enum;

enum TeamPosition: string
{
    case OWNER = 'owner';
    case EDITOR = 'editor';
    
}