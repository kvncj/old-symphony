<?php

namespace App\Entity;

use App\Entity\Platform\Store;
use App\Model\Common\Enum\Platform;
use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'team', targetEntity: TeamRole::class, orphanRemoval: true)]
    private Collection $members;

    #[ORM\OneToMany(mappedBy: 'team', targetEntity: Store::class, orphanRemoval: true)]
    private Collection $stores;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->stores = new ArrayCollection();
    }

    public function hasMember(User $user): bool {
        foreach($this->members as $member) {
            /** @var TeamRole $member */
            if($member->getMember() == $user)
                return true;
        }
        return false;
    }

    public function getSupportedPlatforms(): array {
        return Platform::values();
    }

    public function getStoresByPlatform(Platform $platform): array {
        $stores = [];
        foreach($this->getStores() as $store) {
            /** @var Store $store */
            if($store->getPlatform() == $platform->value)
                array_push($stores, $store);
        }
        return $stores;
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

    /**
     * @return Collection<int, TeamRole>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(TeamRole $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
            $member->setTeam($this);
        }

        return $this;
    }

    public function removeMember(TeamRole $member): self
    {
        if ($this->members->removeElement($member)) {
            // set the owning side to null (unless already changed)
            if ($member->getTeam() === $this) {
                $member->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Store>
     */
    public function getStores(): Collection
    {
        return $this->stores;
    }

    public function addStore(Store $store): self
    {
        if (!$this->stores->contains($store)) {
            $this->stores->add($store);
            $store->setTeam($this);
        }

        return $this;
    }

    public function removeStore(Store $store): self
    {
        if ($this->stores->removeElement($store)) {
            // set the owning side to null (unless already changed)
            if ($store->getTeam() === $this) {
                $store->setTeam(null);
            }
        }

        return $this;
    }
}
