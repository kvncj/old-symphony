<?php

namespace App\Entity;

use App\Repository\ProductVariantRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductVariantRepository::class)]
class ProductVariant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?float $normalPrice = null;

    #[ORM\Column(nullable: true)]
    private ?float $salePrice = null;

    #[ORM\Column(nullable: true)]
    private ?int $stock = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $saleStart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $saleEnd = null;

    #[ORM\Column(length: 255)]
    private ?string $sku = null;

    #[ORM\ManyToOne(inversedBy: 'variants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Image $image = null;

    #[ORM\Column(nullable: true)]
    private ?float $length = null;

    #[ORM\Column(nullable: true)]
    private ?float $width = null;

    #[ORM\Column(nullable: true)]
    private ?float $height = null;

    #[ORM\Column(nullable: true)]
    private ?float $weight = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    public function __construct()
    {
        $this->setStatus('active');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNormalPrice(): ?float
    {
        return $this->normalPrice;
    }

    public function setNormalPrice(float|string|null $normalPrice): self
    {
        if (is_string($normalPrice))
            $normalPrice = floatval($normalPrice);

        $this->normalPrice = $normalPrice;

        return $this;
    }

    public function getSalePrice(): ?float
    {
        return $this->salePrice;
    }

    public function setSalePrice(float|string|null $salePrice): self
    {
        if (is_string($salePrice))
            $salePrice = floatval($salePrice);
            
        $this->salePrice = $salePrice;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int|string|null $stock): self
    {
        if (is_string($stock))
            $stock = intval($stock);

        $this->stock = $stock;

        return $this;
    }

    public function getSaleStart(): ?\DateTimeInterface
    {
        return $this->saleStart;
    }

    public function setSaleStart(?\DateTimeInterface $saleStart): self
    {
        $this->saleStart = $saleStart;

        return $this;
    }

    public function getSaleEnd(): ?\DateTimeInterface
    {
        return $this->saleEnd;
    }

    public function setSaleEnd(?\DateTimeInterface $saleEnd): self
    {
        $this->saleEnd = $saleEnd;

        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getLength(): ?float
    {
        return $this->length;
    }

    public function setLength(float|string|null $length): self
    {
        if (is_string($length))
            $length = intval($length);

        $this->length = $length;

        return $this;
    }

    public function getWidth(): ?float
    {
        return $this->width;
    }

    public function setWidth(float|string|null $width): self
    {
        if (is_string($width))
            $width = intval($width);

        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(float|string|null $height): self
    {
        if (is_string($height))
            $height = intval($height);

        $this->height = $height;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(float|string|null $weight): self
    {
        if (is_string($weight))
            $weight = intval($weight);

        $this->weight = $weight;

        return $this;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
