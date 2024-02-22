<?php

namespace App\Model\Common\Enum;

enum Region: string
{
    case MY = 'my';
    case SG = 'sg';
    case ID = 'id';
    case US = 'us';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getName()
    {
        return match ($this) {
            Region::MY => 'Malaysia',
            Region::SG => 'Singapore',
            Region::ID => 'Indonesia',
            Region::US => 'USA',
        };
    }

    public function getCurrency()
    {
        return match ($this) {
            Region::MY => 'MYR',
            Region::SG => 'SGD',
            Region::ID => 'IDR',
        };
    }

    public function getCurrencyNotation()
    {
        return match ($this) {
            Region::MY => 'RM',
            Region::SG => 'S$',
            Region::ID => 'Rp',
        };
    }

    public function getTimezone() {
        return match ($this) {
            Region::MY => 'Asia/Kuala_Lumpur',
            Region::SG => 'Asia/Singapore',
            Region::ID => 'Asia/Jakarta',
        };
    }

    public static function getChoices(): array
    {
        return [
            'Malaysia' => Region::MY->value,
            'Singapore' => Region::SG->value,
            'Indonesia' => Region::ID->value
        ];
    }
}
