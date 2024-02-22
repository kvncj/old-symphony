<?php

namespace App\Controller\API\Platform\Lazada;

use App\Controller\API\Platform\PlatformProductController;
use App\Entity\Image;
use App\Entity\Platform\Lazada\LazadaProduct;
use App\Entity\Platform\Lazada\LazadaStore;
use App\Entity\Platform\Store;
use App\Entity\Product;
use App\Entity\User;
use App\Model\Common\Enum\Platform;
use App\Model\Product\Enum\ProductMetaKey;
use App\Model\Product\Enum\ProductType;
use App\Model\Product\ProductSettingsLazada;
use App\Service\Platform\Lazada\LazadaProductAPI;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/lazada/product', name: 'lazada_product')]
class LazadaProductController extends PlatformProductController
{
    private LazadaProductAPI $apiClient;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, LazadaProductAPI $apiClient)
    {
        parent::__construct($em, $logger, Platform::LAZADA);
        $this->apiClient = $apiClient;
    }

    #[Route('/import/{storeId}', name: '_import', methods: ['GET'])]
    public function productImport(int $storeId, EntityManagerInterface $em, LazadaProductAPI $api, Request $request): Response
    {
        $store = $this->getStore($storeId);
        $api->setStore($store);

        $batch = $request->get('batch');
        if ($batch != null) {

            $productData = $api->getProducts(offset: $batch * 50)['data']['products'];
            foreach ($productData as $data) {
                $lzd_product = new LazadaProduct();
                $lzd_product->setRef($data['item_id']);
                $lzd_product->setName($data['attributes']['name']);
                //$em->persist($brand);
                $em->flush();
            }


            return $this->response();
        } else {
            $productTotal = $api->getProducts(limit: 1)['data']['total_products'];
            $calls = ceil($productTotal / 200);

            return $this->render('batch.html.twig', [
                'url' => $this->generateUrl('lazada_product_import', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'param' => 'batch',
                'calls' => $calls
            ]);
        }
    }

    #[Route('/push/{productId}', name: '_push', methods: ['GET'])]
    public function pushToPlatform(int $productId, EntityManagerInterface $em, LazadaProductAPI $api, Request $request): Response
    {

        /** @var User $user */
        $user = $this->getUser();

        $product = $this->getProduct($productId);
        $team = $product->getTeam();

        $store = $em->getRepository(LazadaStore::class)->findOneBy(['ref' => 37113]);
        $api->setStore($store);

        /** Before updating the product, make sure all images have lazada URLs. */
        $images = $product->getAllImages();
        foreach ($images as $image) {
            /** @var Image $image */

            $imageKey = Platform::LAZADA->value . "_url";
            if ($image->getMetaByKey($imageKey) == null) {
                $migrateRequest = $api->migrateImage($image);
                $em->persist($image->setMetaByKey($imageKey, $migrateRequest['data']['image']['url']));
            }
        }
        $em->flush();


        $stores = $team->getStoresByPlatform(Platform::LAZADA);
        foreach ($stores as $store) {
            /** @var LazadaStore $store */
            $api->setStore($store);
            $productData = $this->convertToData($product, $store);

            $lazadaProdRef = $product->getMetaByKey(Platform::LAZADA->value . "_" . $store->getRef() . "_prod");
            if ($lazadaProdRef != null) {
                $lazadaProductQuery = $api->getProductItem($lazadaProdRef);
                if (isset($lazadaProductQuery['data'])) {
                    $lazadaProductData = $lazadaProductQuery['data'];
                    dd($lazadaProductData);
                    $productData['item_id'] = $lazadaProdRef;

                    /**
                     * Step 1: check if existing variation options need to be deleted
                     */
                    $oldVariations = array_map(fn ($variationData) => $variationData['name'], $lazadaProductData['variation'] ?? []);
                    $currentVariations = array_map(fn ($variationData) => $variationData['name'], $productData['variation'] ?? []);
                    $toDeleteVariations = [];
                    foreach ($oldVariations as $index => $oldVariation) {
                        if ($oldVariation == "PlaceholderBgs") {
                            $FLAG_ADD_PLACEHOLDER = false;
                            $FLAG_DELETE_PLACEHOLDER = true;
                        }
                        elseif (!in_array($oldVariation, $currentVariations))
                            $toDeleteVariations[$index] = ['name' => $oldVariation];
                    }

                    $FLAG_ADD_PLACEHOLDER = $FLAG_ADD_PLACEHOLDER ?? !empty($toDeleteVariations);
                    $FLAG_DELETE_PLACEHOLDER = $FLAG_DELETE_PLACEHOLDER ?? false;
                    $FLAG_DELETE_VARIATIONS = !empty($toDeleteVariations);


                    /**
                     * Step 2: Check if skus need to be removed, added or simply updated
                     */

                    $skuDiff = count($productData['skus']) - count($lazadaProductData['skus']);

                    $FLAG_ADD_SKU = $skuDiff > 0;
                    $FLAG_REMOVE_SKU = $skuDiff < 0;

                    /**
                     * Step 3: If $FLAG_ADD_PLACEHOLDER is true, add a placeholder variation.
                     */
                    if ($FLAG_ADD_PLACEHOLDER) {
                        $dummyData = [
                            'item_id' => $lazadaProductData['item_id'],
                            'variation' => $lazadaProductData['variation'],
                            'attributes' => [],
                            'skus' => array_map(fn ($data) => [
                                'SellerSku' => $data['SellerSku'],
                                'saleProp' => $data['saleProp']
                            ], $lazadaProductData['skus'])
                        ];
                        array_walk_recursive($dummyData, function (&$item) {
                            if ($item === true) {
                                $item = "true";
                            } else if ($item === false) {
                                $item = "false";
                            } else if (is_numeric($item)) {
                                $item = intval($item);
                            }
                        });
                        $dummyData['variation']['variation' . count($lazadaProductData['variation'])] = [
                            'name' => "PlaceholderBgs",
                            'options' => range(0, count($lazadaProductData['skus']) - 1),
                            'customize' => 'true',
                            'hasImage' => 'false'
                        ];
                        foreach ($dummyData['skus'] as $index => &$sku) {
                            $sku['saleProp']['PlaceholderBgs'] = $index;
                            foreach ($sku['saleProp'] as $key => $value)
                                unset($sku[$key]);
                        }

                        $dummyPayload = $this->getPayload($dummyData, true);
                        $dummyRequest = $api->updateProduct($dummyPayload);
                    }

                    /**
                     * Step 4: If $FLAG_DELETE_VARIATIONS is true, delete the remaining variations.
                     * Step 4: If $FLAG_DELETE_SKU is true, delete the SKUs as well.
                     */
                    $deleteCount = abs($skuDiff);
                    $deleteData = [
                        'item_id' => $lazadaProductData['item_id'],
                        'variation' => $toDeleteVariations,
                        'attributes' => [],
                        'skus' => array_map(fn ($data) => [
                            'SellerSku' => $data['SellerSku'],
                            'saleProp' => $data['saleProp']
                        ], $lazadaProductData['skus'])
                    ];
                    $deleteData['skus'] = array_splice($deleteData['skus'], 0, $deleteCount);
                    
                    $deletePayload = $this->getPayload($deleteData);
                    $deleteRequest = $api->removeSku($deletePayload);
                    dd($deleteRequest);



                    
                    /**
                     * Step 5: If $FLAG_ADD_SKU is true, add remaining variations.
                     */
                    $variationsToAdd = $skuDiff;
                    $addData = $productData;
                    foreach($lazadaProductData['skus'] as $index => $oldSku) {
                        if ($index == 0)
                            $addData['AssociatedSku'] = $oldSku['SellerSku'];
                        $addData['skus'][$index]['SellerSku'] = $oldSku['SellerSku'];
                    }




                    $update = function () use ($api, $lazadaProductData, &$productData) {
                        foreach ($lazadaProductData['skus'] as $index => $lazadaSkuData) {
                            if ($index == 0)
                                $productData['AssociatedSku'] = $lazadaSkuData['SellerSku'];
                            $productData['skus'][$index]['SkuId'] = $lazadaSkuData['SkuId'];
                            $productData['skus'][$index]['ShopSKU'] = $lazadaSkuData['ShopSku'];
                            $productData['skus'][$index]['SellerSku'] = $lazadaSkuData['SellerSku'];
                        }
                        //dd($productData);
                        //dd(str_replace(["\t", "\n"], '', strstr($this->render('api/lazada/product-payload.xml.twig', $productData), '<?xml')));
                        return $api->updateProduct(strstr($this->render('api/lazada/product-payload.xml.twig', $productData), '<?xml'));
                    };

                    $remove = function () use ($api, $lazadaProductData, &$productData) {
                        $oldVariations = array_map(fn ($variationData) => $variationData['name'], $lazadaProductData['variation'] ?? []);
                        $currentVariations = array_map(fn ($variationData) => $variationData['name'], $productData['variation'] ?? []);
                        $toDeleteVariations = [];
                        foreach ($oldVariations as $index => $oldVariation) {
                            if (!in_array($oldVariation, $currentVariations))
                                $toDeleteVariations[$index] = ['name' => $oldVariation];
                        }

                        $deleteParams = [
                            'item_id' => $productData['item_id'],
                            'variation' => $toDeleteVariations,
                            'skus' => []
                        ];
                        return $api->removeSku(strstr($this->render('api/lazada/product-payload.xml.twig', $deleteParams), '<?xml'));
                    };

                    // figure out if we need to add or remove skus
                    $skuDiff = count($productData['skus']) - count($lazadaProductData['skus']);

                    if ($skuDiff > 0) {
                        // update skus, then add skus
                        /*$addParams = $productData;
                        $addParams['skus'] = [];
                        for ($i = 0; $i < $skuDiff; $i++)
                            $addParams['skus'][] = array_pop($productData['skus']);*/

                        dd($update());
                        dd($remove());

                        $addParams['AssociatedSku'] = $productData['skus'][0]['SellerSku'];
                        $api->updateProduct(strstr($this->render('api/lazada/product-payload.xml.twig', $addParams), '<?xml'));
                    } elseif ($skuDiff == 0) {
                        // update skus only
                        $update();
                        dd($remove());
                    } else {
                        // remove skus, then update skus
                        $toRemove = array_map(fn ($skuData) => "\"SkuId_" . $lazadaProductData['item_id'] . "_" . $skuData['SkuId'] . "\"", array_splice($lazadaProductData['skus'], 0, abs($skuDiff)));
                        $api->removeProduct($toRemove);

                        $update();
                    }
                }
            } else {
                $addPayload = strstr($this->render('api/lazada/product-payload.xml.twig', $productData), '<?xml');
                $addRequest = $api->createProduct($addPayload);
                dd($addRequest);
            }

            /*$toRemove = [];
                foreach ($lazadaProductData['skus'] as $lazadaSkuData) {
                    $sku = $lazadaSkuData['SellerSku'];
                    $remove_flag = true;
                    foreach ($productData['skus'] as &$variantData) {
                        if ($sku == $variantData['SellerSku']) {
                            $remove_flag = false;
                            $variantData['SkuId'] = $lazadaSkuData['SkuId'];
                        }
                    }
                    if ($remove_flag) $toRemove[] =  "\"SkuId_" . $lazadaProductData['item_id'] . "_" . $lazadaSkuData['SkuId'] . "\"";
                }*/

            $debug = $request->get('debug', false);
            if ($debug) {
                $debugData = $request->get('debugData', 'platformData');
                switch ($debugData) {
                    case "platformData":
                        dd($lazadaProductData);
                        break;
                    case "productData":
                        dd($productData);
                        break;
                    case "xml":
                        dd(strstr($this->render('api/lazada/product-payload.xml.twig', $productData), '<?xml'));
                        break;
                    default:
                        break;
                }
            }
        }

        return $this->response();
    }


    protected function convertToData(Product $product, Store $store): array
    {
        $settings = ProductSettingsLazada::createFromProduct($product);

        $payloadData = [
            'primary_category' => $settings['category'],
            'images' => array_map(fn (Image $image) => $image->getMetaByKey(Platform::LAZADA->value . "_url"), $product->getGallery()->toArray()),
            'attributes' => [
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'brand_id' => $settings['brand']
            ],
            'skus' => []
        ];

        $options = $product->getOptions();
        foreach ($options as $index => $option) {
            $payloadData['variation']["variation" . $index + 1] = [
                'name' => $option['name'],
                'hasImage' => $index == 0 ? 'true' : 'false',
                'customize' => 'true',
                'options' => $option['values']
            ];
        }

        foreach ($product->getVariants() as $variant) {
            $skuData = [
                'SellerSku' => $variant->getSku(),
                'package_length' => $variant->getLength(),
                'package_width' => $variant->getWidth(),
                'package_height' => $variant->getHeight(),
                'package_weight' => $variant->getWeight(),
                'price' => $variant->getNormalPrice(),
                'special_price' => $variant->getSalePrice(),
                'special_from_date' => $variant->getSaleStart(),
                'special_to_date' => $variant->getSaleEnd(),
                'quantity' => $variant->getStock(),
                'saleProp' => []
            ];

            if (null != $skuData['special_from_date'])
                $skuData['special_from_date'] = $skuData['special_from_date']->format('Y-m-d H:i:s');
            if (null != $skuData['special_to_date'])
                $skuData['special_to_date'] = $skuData['special_to_date']->format('Y-m-d H:i:s');

            $variantImage = $variant->getImage();
            if ($variantImage instanceof Image)
                $skuData['Images'] = [$variantImage->getMetaByKey(Platform::LAZADA->value . "_url")];

            if ($product->getType() == ProductType::VARIABLE->value) {
                $variations = explode(',', $variant->getName());
                foreach ($variations as $index => $variation) {
                    $skuData['saleProp'][$options[$index]['name']] = $variation;
                }
            }

            $payloadData['skus'][] = array_filter($skuData);
        }

        return $payloadData;
    }

    private function getPayload(array $productData, bool $pretty = false)
    {
        return $pretty ? str_replace(["\t", "\n"], '', strstr($this->render('api/lazada/product-payload.xml.twig', $productData), '<?xml'))
            : strstr($this->render('api/lazada/product-payload.xml.twig', $productData), '<?xml');
    }
}
