<?php

namespace App\Model\Doctrine;

enum Sequence: string
{
    case ASC = 'asc';
    case DESC = 'desc';
}