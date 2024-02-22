<?php

namespace App\Model\Product\Enum;

enum ProductSortable: string
{
    case ID = 'id';
    case NAME = 'name';
    case SKU = 'sku';
    case CREATED = 'createdAt';
    case UPDATED = 'updatedAt';
}