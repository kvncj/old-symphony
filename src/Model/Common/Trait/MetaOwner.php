<?php

namespace App\Model\Common\Trait;

use App\Model\Common\Meta;
use Doctrine\Common\Collections\Collection;

trait MetaOwner
{
    abstract protected function createMeta(): Meta;
    abstract protected function getMeta(): Collection;

    protected function getMetaValue(string $name)
    {
        if (empty($this->metaIndex)) {
            foreach ($this->getMeta() as $meta)
                if ($meta->getName() == $name)
                    return $meta->getValue();
        } else {
            $meta = $this->getMetaInstanceByKey($name);
            return $meta instanceof Meta ? $meta->getValue() : null;
        }
    }

    protected function setMetaValue(string $name, string|array|null $value): Meta
    {
        $meta = $this->getMetaInstanceByKey($name) ?? null;
        if (is_array($value)) $value = json_encode($value);

        if ($meta instanceof Meta)
            $meta->setValue($value);
        else {
            $meta = $this->createMeta();
            $meta->setName($name);
            $meta->setValue($value);

            $this->addMeta($meta);
        }

        return $meta;
    }

    protected function getMetaInstanceByKey(string $key): ?Meta
    {
        foreach ($this->getMeta() as $meta) {
            /** @var Meta $meta */
            if ($meta->getName() == $key)
                return $meta;
        }
        return null;
    }
}
