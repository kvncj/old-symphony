<?php

namespace App\Controller\API\Platform;

use App\Controller\ExtendedController;
use App\Entity\Platform\Store;
use App\Entity\Product;
use App\Entity\User;
use App\Model\Common\Enum\Platform;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

abstract class PlatformProductController extends ExtendedController
{
    protected EntityManagerInterface $em;
    protected Platform $platform;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, Platform $platform)
    {
        parent::__construct($logger);
        $this->em = $em;
        $this->platform = $platform;
    }

    protected abstract function convertToData(Product $product, Store $store): array;

    protected function getProduct(int $productId) {
        /** @var User $user */
        $user = $this->getUser();

        $product = $this->em->getRepository(Product::class)->find($productId);
        if(!$product instanceof Product)
            throw new \Exception("Product with ID $productId is not found.");

        $team = $product->getTeam();
        if(!$team->hasMember($user))
            throw new \Exception("User does not have permission to push this product.");

        return $product;
    }

    protected function getStore(int|string $storeId)
    {
        /** @var User $user */
        $user = $this->getUser();

        $storeClass = $this->platform->getStoreClass();
        $store = $this->em->getRepository($storeClass)->find($storeId);
        if ($store == null)
            throw new \Exception("Store #$storeId not found.");

        /** @var Store $store */
        if (!$store->hasPermissions($user))
            throw new \Exception("User #" . $user->getId() . " does not have permissions for store #$storeId");

        return $store;
    }
}
