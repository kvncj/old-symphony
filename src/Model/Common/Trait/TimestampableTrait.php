<?php

namespace App\Model\Common\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait TimestampableTrait
{
    #[ORM\Column(name: 'createdAt', type: Types::DATETIME_MUTABLE, options: ["default" => "1900-01-01"])]
    private $createdAt;

    #[ORM\Column(name: 'updatedAt', type: Types::DATETIME_MUTABLE, options: ["default" => "1900-01-01"])]
    private $updatedAt;

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): void {
        $this->updatedAt = $updatedAt;
    }
}
