<?php

namespace App\Model\Common\Enum;

use App\Entity\Platform\Lazada\LazadaStore;

enum Platform: string
{
    case LAZADA = 'lazada';
    case SHOPEE = 'shopee';
    case WOOCOMMERCE = 'wc';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getProductController(): string
    {
        return match ($this) {
            Platform::LAZADA => LazadaProductController::class,
            Platform::SHOPEE => ShopeeProductController::class,
            Platform::WOOCOMMERCE => WCProductController::class,
        };
    }

    public function getName()
    {
        return match ($this) {
            Platform::LAZADA => 'Lazada',
            Platform::SHOPEE => 'Shopee',
            Platform::WOOCOMMERCE => 'WooCommerce',
        };
    }

    public function getAuthRoute()
    {
        return match ($this) {
            Platform::LAZADA => 'lazada_auth',
            Platform::SHOPEE => 'shopee_auth',
            Platform::WOOCOMMERCE => 'wc_auth',
        };
    }

    public function getUnlinkRoute()
    {
        return match ($this) {
            Platform::LAZADA => 'lazada_unlink',
            Platform::SHOPEE => 'shopee_unlink',
            Platform::WOOCOMMERCE => 'wc_unlink',
        };
    }

    public function getOrderImportRoute()
    {
        return match ($this) {
            Platform::LAZADA => 'lazada_order_import_form',
            Platform::SHOPEE => 'shopee_order_import_form',
            Platform::WOOCOMMERCE => 'wc_order_import_form',
        };
    }

    public function getProductImportRoute()
    {
        return match ($this) {
            Platform::LAZADA => 'lazada_product_import_form',
            Platform::SHOPEE => 'shopee_product_import_form',
            Platform::WOOCOMMERCE => 'wc_product_import_form',
        };
    }

    public function getOrderClass()
    {
        return match ($this) {
            Platform::LAZADA => LazadaOrder::class,
            Platform::SHOPEE => ShopeeOrder::class,
            Platform::WOOCOMMERCE => WCOrder::class,
        };
    }

    public function getProductClass()
    {
        return match ($this) {
            Platform::LAZADA => LazadaStore::class,
            Platform::SHOPEE => ShopeeOrder::class,
            Platform::WOOCOMMERCE => 'WooCommerce',
        };
    }

    public function getProductSettingsClass()
    {
        return match ($this) {
            Platform::LAZADA => LazadaProductSettings::class,
            Platform::SHOPEE => ShopeeProductSettings::class,
            Platform::WOOCOMMERCE => 'WooCommerce',
        };
    }

    public function getStoreClass()
    {
        return match ($this) {
            Platform::LAZADA => LazadaStore::class,
            Platform::SHOPEE => ShopeeStore::class,
            Platform::WOOCOMMERCE => WCStore::class,
        };
    }
}
