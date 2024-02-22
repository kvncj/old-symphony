<?php

namespace App\Entity\Platform\Lazada;

use App\Entity\Platform\Store;
use App\Model\Common\Enum\Platform;
use App\Model\Store\Trait\AccessTokenOwner;
use App\Repository\Platform\Lazada\LazadaStoreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LazadaStoreRepository::class)]
class LazadaStore extends Store
{
    use AccessTokenOwner;

    public function getPlatform(): string {
        return Platform::LAZADA->value;
    }
}
