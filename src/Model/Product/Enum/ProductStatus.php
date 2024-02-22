<?php

namespace App\Model\Product\Enum;

enum ProductStatus: string
{
    case ALL = 'all';
    case NEW = 'new';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case WARNING = 'warning';
    case DELETED = 'deleted';
    case UNDEFINED = 'undefined';

    public static function convertFromLazada(string $lazadaStatus): self
    {
        switch($lazadaStatus) {
            case 'InActive':
                return ProductStatus::INACTIVE;
            case 'Active':
                return ProductStatus::ACTIVE;
            case 'Suspended':
                return ProductStatus::WARNING;
            default:
                return ProductStatus::UNDEFINED;
        }
    }

    public static function convertFromShopee(string $shopeeStatus): self
    {
        switch($shopeeStatus) {
            case 'NORMAL':
                return ProductStatus::ACTIVE;
            case 'BANNED':
                return ProductStatus::WARNING;
            case 'DELETED':
                return ProductStatus::DELETED;
            case 'UNLIST':
                return ProductStatus::INACTIVE;
            default:
                return ProductStatus::UNDEFINED;
        }
    }

    public static function convertFromWooCommerce(string $wcStatus): self
    {
        if (in_array($wcStatus, ['draft', 'pending', 'private']))
            return ProductStatus::INACTIVE;
        else if (in_array($wcStatus, ['publish']))
            return ProductStatus::ACTIVE;
        else
            return ProductStatus::UNDEFINED;
    }
}
