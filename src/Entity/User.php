<?php

namespace App\Entity;

use App\Model\Common\Meta;
use App\Model\Common\Trait\MetaOwner;
use App\Model\User\Enum\UserSettingKey;
use App\Model\User\Enum\UserStatus;
use App\Model\User\Enum\UserType;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use MetaOwner;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 15)]
    private ?string $status = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserSetting::class, orphanRemoval: true)]
    private Collection $settings;

    #[ORM\OneToMany(mappedBy: 'member', targetEntity: TeamRole::class, orphanRemoval: true)]
    private Collection $teamRoles;

    #[ORM\Column(length: 13)]
    private ?string $type = null;

    public function __construct()
    {
        $this->setStatus(UserStatus ::EMAIL_UNVERIFIED);
        $this->settings = new ArrayCollection();
        $this->teamRoles = new ArrayCollection();
    }

    public function getTeams(): Collection {
        $teams = new ArrayCollection();
        foreach($this->teamRoles as $teamRole) {
            /** @var TeamRole $teamRole */
            $team = $teamRole->getTeam();
            if(!$teams->contains($team))
                $teams->add($team);
        }

        return $teams;
    }

    public function getSetting(UserSettingKey $key) {
        return $this->getMetaValue($key->value);
    }

    public function addMeta(Meta $setting) {
        $this->addSetting($setting);
    }

    public function createMeta(): UserSetting {
        return new UserSetting();
    }

    public function getMeta(): Collection {
        return $this->getSettings();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(UserStatus $status): self
    {
        $this->status = $status->value;

        return $this;
    }

    /**
     * @return Collection<int, UserSetting>
     */
    public function getSettings(): Collection
    {
        return $this->settings;
    }

    public function addSetting(UserSetting $setting): self
    {
        if (!$this->settings->contains($setting)) {
            $this->settings->add($setting);
            $setting->setUser($this);
        }

        return $this;
    }

    public function removeSetting(UserSetting $setting): self
    {
        if ($this->settings->removeElement($setting)) {
            // set the owning side to null (unless already changed)
            if ($setting->getUser() === $this) {
                $setting->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TeamRole>
     */
    public function getTeamRoles(): Collection
    {
        return $this->teamRoles;
    }

    public function addTeamRole(TeamRole $teamRole): self
    {
        if (!$this->teamRoles->contains($teamRole)) {
            $this->teamRoles->add($teamRole);
            $teamRole->setMember($this);
        }

        return $this;
    }

    public function removeTeamRole(TeamRole $teamRole): self
    {
        if ($this->teamRoles->removeElement($teamRole)) {
            // set the owning side to null (unless already changed)
            if ($teamRole->getMember() === $this) {
                $teamRole->setMember(null);
            }
        }

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(UserType $type): self
    {
        $this->type = $type->value;

        return $this;
    }
}
