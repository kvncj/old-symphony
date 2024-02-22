<?php

namespace App\Entity\Platform;

use App\Entity\Platform\Lazada\LazadaProduct;
use App\Repository\Platform\PlatformProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlatformProductRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'platform', type: 'string', length: 20)]
#[ORM\DiscriminatorMap([
    '' => PlatformProduct::class,
    'lazada' => LazadaProduct::class
])]
class PlatformProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $ref = null;

    #[ORM\Column(nullable: true)]
    private ?float $minPrice = null;

    #[ORM\Column(nullable: true)]
    private ?float $maxPrice = null;

    #[ORM\Column]
    private ?int $minStock = null;

    #[ORM\Column]
    private ?int $maxStock = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRef(): ?int
    {
        return $this->ref;
    }

    public function setRef(int $ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    public function getMinPrice(): ?float
    {
        return $this->minPrice;
    }

    public function setMinPrice(?float $minPrice): self
    {
        $this->minPrice = $minPrice;

        return $this;
    }

    public function getMaxPrice(): ?float
    {
        return $this->maxPrice;
    }

    public function setMaxPrice(?float $maxPrice): self
    {
        $this->maxPrice = $maxPrice;

        return $this;
    }

    public function getMinStock(): ?int
    {
        return $this->minStock;
    }

    public function setMinStock(int $minStock): self
    {
        $this->minStock = $minStock;

        return $this;
    }

    public function getMaxStock(): ?int
    {
        return $this->maxStock;
    }

    public function setMaxStock(int $maxStock): self
    {
        $this->maxStock = $maxStock;

        return $this;
    }
}
