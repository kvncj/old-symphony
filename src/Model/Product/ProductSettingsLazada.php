<?php

namespace App\Model\Product;

use App\Entity\Product;
use App\Model\Common\Enum\Platform;

class ProductSettingsLazada
{
    private $storeData = [];
    private $brand, $category;

    public static function createFromProduct(Product $product)
    {
        return [
            'brand' => $product->getMetaByKey(Platform::LAZADA->value . "_brand"),
            'category' => $product->getMetaByKey(Platform::LAZADA->value . "_category")
        ];
    }

    public function __construct(?array $data = [])
    {
        $this->storeData = $data['stores'] ?? [];
        $this->brand = $data['brand'] ?? null;
        $this->category = $data['category'] ?? null;
    }

    public function getData(): array
    {
        return [
            'stores' => $this->storeData,
            'brand' => $this->brand,
            'category' => $this->category
        ];
    }

    public function getStoreData(): array
    {
        return $this->storeData;
    }

    public function setStoreData(array $storeData): void
    {
        $this->storeData = implode(',', $storeData);
    }

    public function getBrand(): ?string
    {
        return $this->brand ?? null;
    }

    public function setBrand(?string $brandName): void
    {
        $this->brand = $brandName;
    }

    public function getCategory(): ?int
    {
        return $this->category ?? null;
    }

    public function setCategory(?int $categoryId): void
    {
        $this->category = $categoryId;
    }
}
