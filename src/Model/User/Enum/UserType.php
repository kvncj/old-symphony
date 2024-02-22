<?php

namespace App\Model\User\Enum;

enum UserType: string
{
    case INDIVIDUAL = 'individual';
    case COMPANY = 'company';

    public static function getChoices(): array
    {
        return [
            'Individual' => UserType::INDIVIDUAL->value,
            'Company' => UserType::COMPANY->value
        ];
    }
}
