<?php

namespace App\Entity;

use App\Model\Common\Meta;
use App\Model\Common\Trait\MetaOwner;
use App\Repository\ImageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
{
    use MetaOwner;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $path = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $mime = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'image', targetEntity: ImageMeta::class, orphanRemoval: true)]
    private Collection $meta;

    public static function getHost() {
        return "https://storage.googleapis.com/pandorabox_wpbuckets/";
    }

    public function __construct()
    {
        $this->meta = new ArrayCollection();
    }

    public function getURL(bool $encode = true): ?string
    {
        if ($this->path == null) return null;
        else return Image::getHost() . ($encode ?  rawurlencode($this->path) : $this->path);
    }

    public function getMetaByKey(string $key) {
        return $this->getMetaValue($key);
    }

    public function setMetaByKey(string $key, string|array|null $value): ImageMeta
    {
        return $this->setMetaValue($key, $value);
    }

    public function getMeta(): Collection
    {
        return $this->meta;
    }

    public function createMeta(): Meta
    {
        return new ImageMeta();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMime(): ?string
    {
        return $this->mime;
    }

    public function setMime(?string $mime): self
    {
        $this->mime = $mime;

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

    public function addMeta(ImageMeta $meta): self
    {
        if (!$this->meta->contains($meta)) {
            $this->meta->add($meta);
            $meta->setImage($this);
        }

        return $this;
    }

    public function removeMeta(ImageMeta $meta): self
    {
        if ($this->meta->removeElement($meta)) {
            // set the owning side to null (unless already changed)
            if ($meta->getImage() === $this) {
                $meta->setImage(null);
            }
        }

        return $this;
    }
}
