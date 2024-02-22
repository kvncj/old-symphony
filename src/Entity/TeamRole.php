<?php

namespace App\Entity;

use App\Model\User\Enum\TeamPosition;
use App\Repository\TeamRoleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRoleRepository::class)]
class TeamRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'members')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Team $team = null;

    #[ORM\Column(length: 8)]
    private ?string $role = null;

    #[ORM\ManyToOne(inversedBy: 'teamRoles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $member = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(TeamPosition $role): self
    {
        $this->role = $role->value;

        return $this;
    }

    public function getMember(): ?User
    {
        return $this->member;
    }

    public function setMember(?User $member): self
    {
        $this->member = $member;

        return $this;
    }
}
