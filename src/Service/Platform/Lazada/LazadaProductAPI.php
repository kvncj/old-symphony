<?php

namespace App\Service\Platform\Lazada;

use App\Entity\Image;
use DateTime;

class LazadaProductAPI extends LazadaAPIClient
{
    public function createProduct(string $payload)
    {
        return $this->post('/product/create', [
            'payload' => $payload
        ]);
    }

    public function getProducts(int $offset = 0, int $limit = 50, ?ProductFilter $dateFilter = null, ?DateTime $from = null, ?DateTime $to = null, string $filter = 'all', array $options = [], array $skus = [])
    {
        $params = ['filter' => $filter, 'limit' => $limit, 'offset' => $offset, 'options' => $options, 'sku_seller_list' => $skus];
        if ($from != null)
            $params[$dateFilter->dateFrom()] = $from->format('c');
        if ($to != null)
            $params[$dateFilter->dateTo()] = $to->format('c');

        return $this->get('/products/get', $params);
    }

    public function getProductItem(string $ref)
    {
        return $this->get('/product/item/get', [
            'item_id' => $ref
        ]);
    }

    public function getAttributes(int $catId)
    {
        return $this->get('/category/attributes/get', [
            'primary_category_id' => $catId
        ]);
    }

    public function getBrands(int $batch, int $pageSize = 200)
    {
        return $this->get('/category/brands/query', [
            'startRow' => $batch * $pageSize,
            'pageSize' => $pageSize
        ]);
    }

    public function getCategories()
    {
        return $this->get('/category/tree/get', []);
    }

    public function migrateImage(Image $image)
    {
        return $this->post('/image/migrate', [
            'payload' => strstr($this->twig->render('api/lazada/image-payload.xml.twig', ['url' => $image->getURL()]), '<?xml')
        ]);
    }

    public function removeSku(string $payload)
    {
        return $this->post('/product/sku/remove', [
            'payload' => $payload
        ]);
    }

    public function removeProduct(array $skus)
    {
        return $this->post('/product/remove', [
            'seller_sku_list' => "[" . implode(',', $skus) . "]"
        ]);
    }

    public function updateProduct(string $payload)
    {
        return $this->post('/product/update', [
            'payload' => $payload
        ]);
    }
}

enum ProductFilter: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';

    public function dateFrom(): string
    {
        return match ($this) {
            ProductFilter::CREATED => 'create_after',
            ProductFilter::UPDATED => 'update_after',
        };
    }

    public function dateTo(): string
    {
        return match ($this) {
            ProductFilter::CREATED => 'create_before',
            ProductFilter::UPDATED => 'update_before',
        };
    }
}
