<?php

namespace App\Controller\Page;

use App\Controller\ExtendedController;
use App\Entity\Image;
use App\Entity\Product;
use App\Entity\ProductLookup;
use App\Entity\ProductVariant;
use App\Entity\Team;
use App\Entity\User;
use App\Model\Common\Enum\Platform;
use App\Model\Common\Enum\Region;
use App\Model\Doctrine\Sequence;
use App\Model\Exception\Enum\FlashLevel;
use App\Model\Exception\FlashException;
use App\Model\Product\Enum\ProductMetaKey;
use App\Model\Product\Enum\ProductSortable;
use App\Model\Product\Enum\ProductStatus;
use App\Model\Product\Enum\ProductType;
use App\Model\Product\ProductLookupQuery;
use App\Model\User\Enum\UserSettingKey;
use App\Repository\ProductLookupRepository;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/product', name: 'product')]
class ProductController extends ExtendedController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->em = $em;
    }

    #[Route(name: '_list')]
    public function list(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $teams = $user->getTeams();

        /** @var ProductLookupRepository $lookupRepo */
        $lookupRepo = $this->em->getRepository(ProductLookup::class);

        $params = new ProductLookupQuery(
            orderBy: ProductSortable::from($request->get('orderBy', ProductSortable::UPDATED->value)),
            sequence: Sequence::from($request->get('order', Sequence::DESC->value)),
            search: $request->get('search', null),
            status: ProductStatus::from($request->get('status', ProductStatus::ALL->value)),
            page: $request->get('page', 1),
            pageSize: $request->get('size', 10),
            teams: $teams->toArray()
        );
        $lookupResults = $lookupRepo->search($params);
        $currentPage = $params->getPage();
        $totalPages = ceil($lookupResults['total'] / $params->getPageSize());
        if ($currentPage > $totalPages) $currentPage = 1;

        return $this->render('@Page/product/list.html.twig', [
            'locale' => $request->getLocale(),
            'products' => $lookupResults['products'],
            'region' => Region::tryFrom($user->getSetting(UserSettingKey::REGION)),
            'status' => $params->getStatus(),
            'pageSettings' => [
                'current' => $currentPage,
                'size' => $params->getPageSize(),
                'total' => $totalPages == 0 ? 1 : $totalPages,
            ]
        ]);
    }

    #[Route(path: '/add', name: '_add')]
    public function add(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $teams = $user->getTeams();

        if (count($teams) == 1) $team = $teams[0];
        else {
            try {
                $requestData = $request->request->all();
                $formData = $requestData['product_add'] ?? null;
                if ($formData) {
                    $token = $formData['_csrf_token'];
                    if (!$this->isCsrfTokenValid('product_add', $token))
                        throw FlashException::danger('Invalid form token.');

                    $teamId = $formData['team'];
                    foreach ($teams as $userTeam)
                        if ($userTeam->getId() == $teamId) {
                            $team = $userTeam;
                            goto team_selected;
                        }
                    throw FlashException::danger("Invalid team ID.");
                }
            } catch (\Exception $e) {
                $this->handleException($e);
            }
            return $this->render('@Page/product/add.html.twig', [
                'locale' => $request->getLocale(),
                'teams' => $teams
            ]);
        }

        team_selected:
        /** @var Team $team */
        $product = $this->em->getRepository(Product::class)->findOneBy(['status' => ProductStatus::NEW, 'team' => $team->getId()]);
        if (null == $product) {
            $product = new Product(type: ProductType::SIMPLE);
            $product->setStatus(ProductStatus::NEW);
            $product->setTeam($team);

            $this->em->persist($product);
            $this->em->flush();
        };
        return $this->redirectToRoute('product_edit', ['productId' => $product->getId()]);
    }

    #[Route(path: '/{productId}', name: '_edit', requirements: ['productId' => '\d+'])]
    public function edit(int $productId, EntityManagerInterface $em, ImageService $imageService, Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        /** Retrieve product to be edited. */
        $product = $this->em->getRepository(Product::class)->find($productId);
        if (!$product instanceof Product)
            return $this->redirectToRoute('product_add');

        $team = $product->getTeam();
        if (!$team->hasMember($user)) {
            $this->addFlash(FlashLevel::DANGER->value, 'User does not have permission to edit product.');
            $this->redirectToRoute('product_list');
        }

        $requestData = $request->request->all();
        $formData = $requestData['product_edit'] ?? null;
        if ($formData) {
            try {
                $this->validateCSRFToken($request, $formData['_csrf_token']);

                /** Set general product data */
                $product->setName($formData['name'] ?? null);
                $product->setSku($formData['sku'] ?? null);
                $product->setType(ProductType::tryFrom($formData['type']) ?? ProductType::SIMPLE);
                $product->setDescription($formData['description'] ?? null);
                $product->setStatus(ProductStatus::ACTIVE);
                $em->persist($product->setMetaByKey('currency', $formData['price']['currency']));

                /** Create new product images */
                $imageRepo = $em->getRepository(image::class);
                $primaryImage = null;
                $existingImages = $product->getGallery()->toArray();
                if (isset($formData['gallery'])) {
                    foreach ($formData['gallery'] as $index => $imageData) {
                        $id = $imageData['id'];
                        if ($id != 0) {
                            foreach ($existingImages as $index2 => $existingImage)
                                if ($existingImage->getId() == $id) {
                                    $image = $existingImage;
                                    unset($existingImages[$index2]);
                                }
                        } else {
                            $uploadResponse = json_decode($this->forward('App\Controller\API\ImageController::upload', array(
                                'src'  => $imageData['url'],
                                'mime' => $imageData['mime']
                            ))->getContent(), true);

                            $image = $imageRepo->find($uploadResponse['data']['id']);
                            $product->addGallery($image);
                        }
                        if ($index == 0) {
                            $primaryImage = $image;
                            $em->persist($product->setMetaByKey('primary_img', $primaryImage->getId()));
                        }
                    }
                }
                /** Delete images not included in the form */
                foreach ($existingImages as $toRemove) {
                    $product->removeGallery($toRemove);
                    $this->forward('App\Controller\API\ImageController::delete', array(
                        'id'  => $toRemove->getId()
                    ));
                }

                /** Create a dummy product variant for simple products, and handle options accordingly */
                if ($product->getType() == ProductType::SIMPLE->value) {
                    $em->persist($product->setMetaByKey('opt_a', null));
                    $em->persist($product->setMetaByKey('opt_a_val', null));
                    $em->persist($product->setMetaByKey('opt_b', null));
                    $em->persist($product->setMetaByKey('opt_b_val', null));

                    $formData['variants'] = [$formData];
                    $formData['variants'][0]['name'] = 'Base';
                    $formData['variants'][0]['image'] = [];
                } else {
                    if (isset($formData['options'])) {
                        switch (count($formData['options'])) {
                            case 1:
                                $em->persist($product->setMetaByKey('opt_a', $formData['options'][0]['name']));
                                $em->persist($product->setMetaByKey('opt_a_val', $formData['options'][0]['value']));
                                $em->persist($product->setMetaByKey('opt_b', null));
                                $em->persist($product->setMetaByKey('opt_b_val', null));
                                break;
                            case 2:
                                $em->persist($product->setMetaByKey('opt_a', $formData['options'][0]['name']));
                                $em->persist($product->setMetaByKey('opt_a_val', $formData['options'][0]['value']));
                                $em->persist($product->setMetaByKey('opt_b', $formData['options'][1]['name']));
                                $em->persist($product->setMetaByKey('opt_b_val', $formData['options'][1]['value']));
                                break;
                            default:
                                $em->persist($product->setMetaByKey('opt_a', null));
                                $em->persist($product->setMetaByKey('opt_a_val', null));
                                $em->persist($product->setMetaByKey('opt_b', null));
                                $em->persist($product->setMetaByKey('opt_b_val', null));
                                break;
                        }
                    }
                }

                /** Create and update product variants */
                $variantData = $formData['variants'];
                $oldVariants = $product->getVariants()->toArray();
                foreach ($variantData as $data) {
                    $variant = null;
                    foreach ($oldVariants as $index => $oldVariant) {
                        /** @var ProductVariant $oldVariant */
                        if ($oldVariant->getName() == $data['name']) {
                            $variant = $oldVariant;
                            unset($oldVariants[$index]);
                            break;
                        }
                    }
                    if ($variant == null) {
                        $variant = new ProductVariant();
                        $variant->setName($data['name']);
                        $product->addVariant($variant);
                        $em->persist($variant);
                    }
                    $variant->setStatus($data['status'] ?? "inactive");
                    $variant->setSku($data['sku'] ?? null);
                    $variant->setStock($data['stock'] ?? null);
                    $variant->setNormalPrice($data['price']['normal'] ?? null);
                    $variant->setSalePrice($data['price']['sale'] ?? null);

                    $existingImage = $variant->getImage();
                    $imageData = isset($data['image']) ? $data['image'][0] ?? null : null;
                    if ($imageData == null) {
                        $variantImage = null;
                    } elseif ($imageData['id'] == 0) {
                        $uploadResponse = json_decode($this->forward('App\Controller\API\ImageController::upload', array(
                            'src'  => $imageData['url'],
                            'mime' => $imageData['mime']
                        ))->getContent(), true);
                        $variantImage = $imageRepo->find($uploadResponse['data']['id']);
                    } else {
                        $variantImage = $existingImage->getId() == $imageData['id'] ? $existingImage : $imageRepo->find($imageData['id']);
                    }

                    $variant->setImage($variantImage);
                    if ($existingImage instanceof Image && $variantImage != $existingImage)
                        $this->forward('App\Controller\API\ImageController::delete', array(
                            'id'  => $existingImage->getId()
                        ));


                    if (isset($data['price']['date'])) {
                        $range = explode(' to ', $data['price']['date']);
                        $variant->setSaleStart(new \DateTime($range[0]));
                        $variant->setSaleEnd(new \DateTime(count($range) == 1 ? $range[0] : $range[1]));
                    } else {
                        $variant->setSaleStart(null);
                        $variant->setSaleEnd(null);
                    }

                    $variant->setLength($data['dimensions']['length']);
                    $variant->setWidth($data['dimensions']['width']);
                    $variant->setHeight($data['dimensions']['height']);
                    $variant->setWeight($data['dimensions']['weight']);
                }
                /** Delete variants not included in the form */
                foreach ($oldVariants as $variant) {
                    /** @var ProductVariant $variant */
                    $image = $variant->getImage();
                    if ($image = null) {
                        $variant->setImage(null);
                        $this->forward('App\Controller\API\ImageController::delete', array(
                            'id'  => $image->getId()
                        ));
                    }
                    $product->removeVariant($variant);
                    $em->remove($variant);
                }

                function savePlatformMeta($platformMeta, string $path, EntityManagerInterface $em, Product $product)
                {
                    foreach ($platformMeta as $key => $values) {
                        if (is_array($values)) {
                            foreach ($values as $key2 => $value)
                                if (is_array($value))
                                    savePlatformMeta($value, $path . "_$key", $em, $product);
                                else $em->persist($product->setMetaByKey($path . "_$key" . "_$key2", $value));
                        } else $em->persist($product->setMetaByKey($path . "_$key", $values));
                    }
                };
                foreach ($team->getSupportedPlatforms() as $platform) {
                    $platformMeta = $formData['platforms'][$platform] ?? [];
                    //dd($platformMeta);
                    savePlatformMeta($platformMeta, $platform, $em, $product);
                }
                
                /** Commit changes */
                $em->persist($product);
                $em->flush();
            } catch (\Exception $e) {
                throw $e;
                $this->handleException($e, "There was an uncaught error when updating the product. Please try again later.");
            }
            return $this->response(['product' => $product->getId()]);
        }

        return $this->render('@Page/product/edit.html.twig', [
            'locale' => $request->getLocale(),
            'platforms' => $team->getSupportedPlatforms(),
            'product' => $product
        ]);
    }
}
