<?php

namespace App\Entity\Platform\Lazada;

use App\Entity\Platform\PlatformProduct;
use App\Repository\Platform\Lazada\LazadaProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LazadaProductRepository::class)]
class LazadaProduct extends PlatformProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
