<?php

namespace App\Entity\Platform\Lazada;

use App\Repository\Platform\Lazada\LazadaCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LazadaCategoryRepository::class)]
class LazadaCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $leaf = null;

    #[ORM\Column]
    private ?int $ref = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'child')]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $child;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $path = null;

    public function __construct()
    {
        $this->child = new ArrayCollection();
    }

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

    public function isLeaf(): ?bool
    {
        return $this->leaf;
    }

    public function setLeaf(bool $leaf): self
    {
        $this->leaf = $leaf;

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

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChild(): Collection
    {
        return $this->child;
    }

    public function addChild(self $child): self
    {
        if (!$this->child->contains($child)) {
            $this->child->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->child->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }
}
