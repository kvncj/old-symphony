<?php

namespace App\Model\Common;

use Doctrine\ORM\Mapping as ORM;

abstract class Meta
{
    #[ORM\Column(length: 255)]
    protected ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $value = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getValue()
    {
        $value = $this->value;
        if($this->isJson($value))
            $value = json_decode($value, true);

        if(is_numeric($value)) {
            return floor($value) != $value ? floatval($value) : intval($value);
        } else return $value;
        
        //return $this->isJson($this->value) ? json_decode($this->value, true) : $this->value;
    }

    public function setValue(string|array|null $value): self
    {
        if (is_array($value))
            $value = json_encode($value);

        $this->value = $value;

        return $this;
    }

    private function isJson($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
