<?php

namespace App\Controller\Page;

use App\Controller\ExtendedController;
use App\Entity\Platform\Store;
use App\Entity\Team;
use App\Entity\User;
use App\Model\Common\Enum\Platform;
use App\Model\Common\Enum\Region;
use App\Model\Store\Enum\StoreStatus;
use App\Model\User\Enum\UserSettingKey;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/store', name: 'store')]
class StoreController extends ExtendedController
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->em = $em;
    }


    #[Route(path: '', name: '', methods: ['GET', 'POST'])]
    public function list(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $teams = $user->getTeams();

        /** @var Team $team */
        $team = null == $request->get('team') ? $teams[0] : $teams[$request->get('team')] ?? $teams[0];
        $platforms = $team->getSupportedPlatforms();
        $activePlatform = Platform::tryFrom($request->get('platform')) ?? Platform::tryFrom($platforms[0]);

        $stores = $team->getStoresByPlatform($activePlatform);

        $page = $request->get('page', 1);
        $size = 10;
        $storesToDisplay = array_slice( $stores, $page - 1, $size ); 


        return $this->render('@Page/store/list.html.twig', [
            'locale' => $request->getLocale(),
            'stores' => $storesToDisplay,
            'platform' => $activePlatform,
            'region' => Region::tryFrom($user->getSetting(UserSettingKey::REGION)),
            'status' => StoreStatus::ACTIVE->value,
            'pageSettings' => [
                'current' => $page,
                'size' => $size,
                'total' => ceil(count($stores)/$size),
            ]
        ]);
    }

    #[Route(path: '/{storeId}/redirect/order', name: '_redirect_order_import', methods: ['GET', 'POST'], requirements: ['storeId' => '\d+'])]
    public function redirectToImportOrders(int $storeId)
    {
        try {
            $store = $this->getStore($storeId);
        } catch (\Exception $e) {
            throw $e;
            $this->handleException($e);
            return $this->redirectToRoute('store_platform_redirect');
        }

        $platform = Platform::tryFrom($store->getPlatform());
        return $this->redirectToRoute($platform->getOrderImportRoute(), ['storeId' => $storeId]);
    }

    #[Route(path: '/{storeId}/redirect/product', name: '_redirect_product_import', methods: ['GET', 'POST'], requirements: ['storeId' => '\d+'])]
    public function redirectToImportProducts(int $storeId)
    {
        try {
            $store = $this->getStore($storeId);
        } catch (\Exception $e) {
            $this->handleException($e);
            return $this->redirectToRoute('store_platform_redirect');
        }

        $platform = Platform::tryFrom($store->getPlatform());
        return $this->redirectToRoute($platform->getProductImportRoute(), ['storeId' => $storeId]);
    }

    #[Route('/{storeId}/unlink', name: '_redirect_unlink', methods: ['GET'], requirements: ['storeId' => '\d+'])]
    public function redirectToUnlink(int $storeId): Response
    {
        try {
            $store = $this->getStore($storeId);
        } catch (\Exception $e) {
            $this->handleException($e);
            return $this->redirectToRoute('store_platform_redirect');
        }

        $platform = Platform::tryFrom($store->getPlatform());
        return $this->redirectToRoute($platform->getUnlinkRoute(), ['storeId' => $storeId]);
    }

    protected function getStore($storeId)
    {
        /** @var User $user */
        $user = $this->getUser();

        $store = $this->em->getRepository(Store::class)->find($storeId);
        if ($store == null)
            throw new \Exception("Store #$storeId not found.");

        /** @var Store $store */
        if (!$store->hasPermissions($user))
            throw new \Exception("User #" . $user->getId() . " does not have permissions for store #$storeId");

        return $store;
    }
}
