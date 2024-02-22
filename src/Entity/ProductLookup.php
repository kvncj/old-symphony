<?php

namespace App\Entity;

use App\Model\Common\Trait\TimestampableTrait;
use App\Repository\ProductLookupRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductLookupRepository::class)]
class ProductLookup
{
    use TimestampableTrait;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?float $priceMin = null;

    #[ORM\Column(nullable: true)]
    private ?float $priceMax = null;

    #[ORM\Column]
    private ?int $stockMin = null;

    #[ORM\Column(nullable: true)]
    private ?int $stockMax = null;

    #[ORM\Column(length: 9)]
    private ?string $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $skus = null;

    #[ORM\Column]
    private ?int $teamId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPriceMin(): ?float
    {
        return $this->priceMin;
    }

    public function setPriceMin(?float $priceMin): self
    {
        $this->priceMin = $priceMin;

        return $this;
    }

    public function getPriceMax(): ?float
    {
        return $this->priceMax;
    }

    public function setPriceMax(?float $priceMax): self
    {
        $this->priceMax = $priceMax;

        return $this;
    }

    public function getStockMin(): ?int
    {
        return $this->stockMin;
    }

    public function setStockMin(int $stockMin): self
    {
        $this->stockMin = $stockMin;

        return $this;
    }

    public function getStockMax(): ?int
    {
        return $this->stockMax;
    }

    public function setStockMax(?int $stockMax): self
    {
        $this->stockMax = $stockMax;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSkus(): ?string
    {
        return $this->skus;
    }

    public function setSkus(?string $skus): self
    {
        $this->skus = $skus;

        return $this;
    }

    public function getTeamId(): ?int
    {
        return $this->teamId;
    }

    public function setTeamId(int $teamId): self
    {
        $this->teamId = $teamId;

        return $this;
    }
}
