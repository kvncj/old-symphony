<?php

namespace App\Entity;

use App\Model\Common\Meta;
use App\Model\Common\Trait\MetaOwner;
use App\Model\Product\Enum\ProductMetaKey;
use App\Model\Product\Enum\ProductStatus;
use App\Model\Product\Enum\ProductType;
use App\Model\Product\ProductConverter;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    use MetaOwner;
    use ProductConverter;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sku = null;

    #[ORM\Column(length: 9)]
    private ?string $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Team $team = null;

    #[ORM\Column(length: 8)]
    private ?string $type = null;

    #[ORM\ManyToMany(targetEntity: Image::class)]
    private Collection $gallery;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductVariant::class, orphanRemoval: true)]
    private Collection $variants;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductMeta::class, orphanRemoval: true)]
    private Collection $metadata;

    public function __construct(ProductType $type)
    {
        $this->setType($type);
        $this->gallery = new ArrayCollection();
        $this->variants = new ArrayCollection();
        $this->metadata = new ArrayCollection();
    }

    public function addMeta(Meta $meta)
    {
        if ($meta instanceof ProductMeta)
            $this->addMetadata($meta);
    }

    public function createMeta(): ProductMeta
    {
        return new ProductMeta();
    }

    public function getMeta(): Collection
    {
        return $this->getMetadata();
    }

    public function getMetaByKey(string $key) {
        return $this->getMetaValue($key);
    }

    public function setMetaByKey(string $key, string|array|null $value): ProductMeta
    {
        return $this->setMetaValue($key, $value);
    }

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

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(ProductStatus $status): self
    {
        $this->status = $status->value;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(ProductType $type): self
    {
        $this->type = $type->value;

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getGallery(): Collection
    {
        return $this->gallery;
    }

    public function addGallery(Image $gallery): self
    {
        if (!$this->gallery->contains($gallery)) {
            $this->gallery->add($gallery);
        }

        return $this;
    }

    public function removeGallery(Image $gallery): self
    {
        $this->gallery->removeElement($gallery);

        return $this;
    }

    /**
     * @return Collection<int, ProductVariant>
     */
    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function addVariant(ProductVariant $variant): self
    {
        if (!$this->variants->contains($variant)) {
            $this->variants->add($variant);
            $variant->setProduct($this);
        }

        return $this;
    }

    public function removeVariant(ProductVariant $variant): self
    {
        if ($this->variants->removeElement($variant)) {
            // set the owning side to null (unless already changed)
            if ($variant->getProduct() === $this) {
                $variant->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductMeta>
     */
    public function getMetadata(): Collection
    {
        return $this->metadata;
    }

    public function addMetadata(ProductMeta $metadata): self
    {
        if (!$this->metadata->contains($metadata)) {
            $this->metadata->add($metadata);
            $metadata->setProduct($this);
        }

        return $this;
    }

    public function removeMetadata(ProductMeta $metadata): self
    {
        if ($this->metadata->removeElement($metadata)) {
            // set the owning side to null (unless already changed)
            if ($metadata->getProduct() === $this) {
                $metadata->setProduct(null);
            }
        }

        return $this;
    }
}
