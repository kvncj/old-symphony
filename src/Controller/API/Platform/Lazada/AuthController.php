<?php

namespace App\Controller\API\Platform\Lazada;

use App\Controller\ExtendedController;
use App\Entity\Image;
use App\Entity\Platform\Lazada\LazadaStore;
use App\Entity\Platform\Store;
use App\Entity\Team;
use App\Entity\User;
use App\Model\Common\Enum\Platform;
use App\Model\Common\Enum\Region;
use App\Model\Exception\FlashException;
use App\Model\Store\Enum\StoreStatus;
use App\Service\Platform\Lazada\LazadaAuthAPI;
use App\Service\ImageService;
use App\Service\Lazada\APIClient;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route(path: '/lazada', name: 'lazada')]
class AuthController extends ExtendedController
{
    private EntityManagerInterface $em;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em)
    {
        parent::__construct($logger);
        $this->em = $em;
    }

    #[Route('/auth/{teamId}', name: '_auth', methods: ['GET'], requirements: ['teamId' => '\d+'])]
    public function auth(int $teamId): Response
    {
        $lazadaKey = $this->getParameter('app.lazada_app_key');

        $redirectUri =  $this->generateUrl('lazada_auth_callback', ['teamId' => $teamId], UrlGeneratorInterface::ABSOLUTE_URL);
        if ($_SERVER['SERVER_NAME'] === 'bargus.test')
            $redirectUri = str_replace('http://bargus.test', 'https://bagus.pandorabox.com.my/placeholder', $redirectUri);

        $url = "https://auth.lazada.com/oauth/authorize?" . http_build_query([
            'client_id' => $lazadaKey,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code'
        ]);
        return $this->redirect($url);
    }

    #[Route('/authCallback/{teamId}', name: '_auth_callback', methods: ['GET'], requirements: ['teamId' => '\d+'])]
    public function authCallback(int $teamId, EntityManagerInterface $em, LazadaAuthAPI $api, Request $request): Response
    {
        try {
            $team = $em->getRepository(Team::class)->find($teamId);
            if (null === $team)
                throw FlashException::danger("Team ID is invalid.");

            /** @var User $user */
            $user = $this->getUser();
            if (!$team->hasMember($user))
                throw FlashException::danger("User does not belong to this team.");

            $code = $request->get('code');
            if (null === $code)
                throw FlashException::danger("Invalid callback parameters.");

            $accessData = $api->getAccessData($code);
            if (isset($accessData['error']))
                throw new \Exception($accessData['error']);

            $storeRef = $accessData['seller_id'];
            $store = $em->getRepository(LazadaStore::class)->findOneBy(['ref' => $storeRef]);
            if (null !== $store)
                throw FlashException::danger("The store is already linked to another account.");

            $store = new LazadaStore();
            $store->setTeam($team);

            $region = Region::tryFrom($accessData['region']);
            if ($region == null)
                throw FlashException::danger('The region is not yet supported.');

            $store->setRegion($region);
            $store->setAccessToken($accessData['access_token']);
            $store->setAccessExpires($accessData['access_expires']);
            $store->setRefreshToken($accessData['refresh_token']);
            $store->setRefreshExpires($accessData['refresh_expires']);

            $storeData = $api->getStoreData($store);
            $store->setName($storeData['name']);
            $store->setRef($storeData['ref']);
            $store->setStatus(StoreStatus::convertFromLazada($storeData['status']));

            $em->persist($store);
            $em->flush();

            $this->addFlash('success', "Store #$storeRef has been linked but its products are not imported. Please sync it via Settings->Stores to import products.");
        } catch (\Exception $e) {
            throw $e;
            $this->handleException($e);
        }
        return $this->redirectToRoute('store', ['platform' => Platform::LAZADA->value]);
    }
}
