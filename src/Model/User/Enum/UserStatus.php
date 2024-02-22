<?php

namespace App\Model\User\Enum;

enum UserStatus: string
{
    case EMAIL_UNVERIFIED = 'unverified_e';
    case COMPANY_UNSET = 'unverified_c1';
    case COMPANY_UNVERIFIED = 'unverified_c2';
    case VERIFIED = 'verified';
}