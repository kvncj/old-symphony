<?php

namespace App\Model\Store\Enum;

enum StoreStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case UNDEFINED = 'undefined';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function convertFromLazada(string $lazadaStatus): self
    {
        switch ($lazadaStatus) {
            case 'ACTIVE':
                return StoreStatus::ACTIVE;
            case 'INACTIVE':
                return StoreStatus::INACTIVE;
            case 'DELETED':
                return StoreStatus::INACTIVE;
            default:
                return StoreStatus::UNDEFINED;
        }
    }
}
