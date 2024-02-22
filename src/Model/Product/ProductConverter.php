<?php

namespace App\Model\Product;

use App\Entity\Image;
use App\Entity\ProductVariant;
use App\Model\Product\Enum\ProductMetaKey;
use App\Model\Product\Enum\ProductType;
use Doctrine\Common\Collections\Collection;

trait ProductConverter
{
    public function getAllImages(): array {
        /** @var Collection $images */
        $images = $this->getGallery();
        
        foreach($this->getVariants() as $variant) {
            /** @var ProductVariant $variant */
            $variantImage = $variant->getImage();
            if($variantImage != null && !$images->contains($variantImage))
                $images->add($variantImage);
        }
        return $images->toArray();
    }

    public function getPricing(): array
    {
        $pricing = [
            'currency' => $this->getMetadata('currency')
        ];

        if (!$this->variants->isEmpty()) {
            /** @var ProductVariant $variant */
            $variant = $this->variants[0];
            $pricing['normal'] = $variant->getNormalPrice();
            $pricing['sale'] = $variant->getSalePrice();

            $date = '';
            if ($variant->getSaleStart() != null) {
                $date = $variant->getSaleStart()->format('Y-m-d');
                if ($variant->getSaleEnd() != $variant->getSaleStart())
                    $date .= " to " . $variant->getSaleEnd()->format('Y-m-d');
            }
            $pricing['date'] = $date;
        }
        return $pricing;
    }

    public function getDimensions(): array
    {
        $dimensions = [];
        if (!$this->variants->isEmpty()) {
            /** @var ProductVariant $variant */
            $variant = $this->variants[0];
            $dimensions['length'] = $variant->getLength();
            $dimensions['width'] = $variant->getWidth();
            $dimensions['height'] = $variant->getHeight();
            $dimensions['weight'] = $variant->getWeight();
        }
        return $dimensions;
    }

    public function getStock(): ?int
    {
        if (!$this->variants->isEmpty()) {
            /** @var ProductVariant $variant */
            $variant = $this->variants[0];
            return $variant->getStock();
        }
        return null;
    }

    public function getGalleryData(): array
    {
        $images = $this->getGallery();
        $primaryImageId = $this->getMetaByKey('primary_img');

        $galleryData = [];
        foreach ($images as $image) {
            /** @var Image $image */
            $imageData = [
                'key' => $image->getId(),
                'id' => $image->getId(),
                'name' => $image->getName(),
                'mime' => $image->getMime(),
                'url' => $image->getURL()
            ];
            if ($image->getId() == $primaryImageId) array_unshift($galleryData, $imageData);
            else array_push($galleryData, $imageData);
        }
        return $galleryData;
    }

    public function getOptions(): array
    {
        $options = [];

        $opt_a = $this->getMetaByKey('opt_a');
        if ($opt_a != null) array_push($options, ['name' => $opt_a, 'values' => explode(',', $this->getMetaByKey('opt_a_val'))]);

        $opt_b = $this->getMetaByKey('opt_b');
        if ($opt_b != null) array_push($options, ['name' => $opt_b, 'values' => explode(',', $this->getMetaByKey('opt_b_val'))]);


        return $options;
    }

    public function getVariantData(): array
    {
        if ($this->getType() == ProductType::SIMPLE->value)
            return [];
        else {
            $variants = [];
            foreach ($this->getVariants() as $variant) {
                /** @var ProductVariant $variant */
                $image = $variant->getImage();

                $date = '';
                if ($variant->getSaleStart() != null) {
                    $date = $variant->getSaleStart()->format('Y-m-d');
                    if ($variant->getSaleEnd() != $variant->getSaleStart())
                        $date .= " to " . $variant->getSaleEnd()->format('Y-m-d');
                }

                array_push($variants, [
                    'id' => $variant->getId(),
                    'name' => $variant->getName(),
                    'sku' => $variant->getSku(),
                    'status' => $variant->getStatus(),
                    'stock' => $variant->getStock(),
                    'image' => $image == null ? null : [
                        'id' => $image->getId(),
                        'name' => $image->getName(),
                        'mime' => $image->getMime(),
                        'url' => $image->getUrl()
                    ],
                    'price' => [
                        'currency' => $this->getMetaByKey('currency'),
                        'normal' => $variant->getNormalPrice(),
                        'sale' => $variant->getSalePrice(),
                        'date' => $date
                    ],
                    'dimensions' => [
                        'length' => $variant->getLength(),
                        'width' => $variant->getWidth(),
                        'height' => $variant->getHeight(),
                        'weight' => $variant->getWeight()
                    ]
                ]);
            }
            return $variants;
        }
    }
}
