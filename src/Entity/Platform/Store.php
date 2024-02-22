<?php

namespace App\Entity\Platform;

use App\Entity\Platform\Lazada\LazadaStore;
use App\Entity\Team;
use App\Entity\User;
use App\Model\Common\Enum\Region;
use App\Model\Store\Enum\StoreStatus;
use App\Repository\Platform\StoreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StoreRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'platform', type: 'string', length: 20)]
#[ORM\DiscriminatorMap([
    '' => Store::class,
    'lazada' => LazadaStore::class
])]
abstract class Store
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ref = null;

    #[ORM\Column(length: 2)]
    private ?string $region = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 10)]
    private ?string $productSync = null;

    #[ORM\Column(length: 10)]
    private ?string $orderSync = null;

    #[ORM\ManyToOne(inversedBy: 'stores')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Team $team = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    abstract public function getPlatform(): string;

    public function __construct() {
        $this->productSync = 'unsynced';
        $this->orderSync = 'unsynced';
    }

    public function hasPermissions(User $user): bool
    {
        return $this->team->hasMember($user);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(Region $region): self
    {
        $this->region = $region->value;

        return $this;
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

    public function getProductSync(): ?string
    {
        return $this->productSync;
    }

    public function setProductSync(string $productSync): self
    {
        $this->productSync = $productSync;

        return $this;
    }

    public function getOrderSync(): ?string
    {
        return $this->orderSync;
    }

    public function setOrderSync(string $orderSync): self
    {
        $this->orderSync = $orderSync;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(StoreStatus $status): self
    {
        $this->status = $status->value;

        return $this;
    }
}
