<?php

namespace App\Model\Store\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait AccessTokenOwner
{
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $accessToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['default' => '1996-01-01'], nullable: true)]
    private ?\DateTimeInterface $accessExpires = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $refreshToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['default' => '1996-01-01'], nullable: true)]
    private ?\DateTimeInterface $refreshExpires = null;

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getAccessExpires(): ?\DateTimeInterface
    {
        return $this->accessExpires;
    }

    public function setAccessExpires(\DateTimeInterface $accessExpires): self
    {
        $this->accessExpires = $accessExpires;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getRefreshExpires(): ?\DateTimeInterface
    {
        return $this->refreshExpires;
    }

    public function setRefreshExpires(\DateTimeInterface $refreshExpires): self
    {
        $this->refreshExpires = $refreshExpires;

        return $this;
    }
}
