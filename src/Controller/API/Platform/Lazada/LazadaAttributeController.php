<?php

namespace App\Controller\API\Platform\Lazada;

use App\Controller\ExtendedController;
use App\Entity\Platform\Lazada\LazadaBrand;
use App\Entity\Platform\Lazada\LazadaCategory;
use App\Entity\Platform\Lazada\LazadaStore;
use App\Repository\Platform\Lazada\LazadaBrandRepository;
use App\Repository\Platform\Lazada\LazadaCategoryRepository;
use App\Service\Platform\Lazada\LazadaProductAPI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/lazada', name: 'lazada')]
class LazadaAttributeController extends ExtendedController
{
    #[Route('/brand/search', name: '_brand_search', methods: ['GET'])]
    public function brandList(EntityManagerInterface $em, Request $request): Response
    {
        try {
            $name = $request->query->get('search');
            if (null == $name)
                return $this->response([0 => ['name' => "NoBrand", 'value' => 0]]);

            /** @var LazadaBrandRepository @brandRepo */
            $brandRepo = $em->getRepository(LazadaBrand::class);
            return $this->response($brandRepo->search($name));
        } catch (\Exception $e) {
            $this->handleException($e);
            return $this->response();
        }
    }

    #[Route('/brand/import', name: '_brand_import', methods: ['GET'])]
    public function brandImport(EntityManagerInterface $em, LazadaProductAPI $api, Request $request): Response
    {
        $adminStore = $em->getRepository(LazadaStore::class)->findOneBy(['ref' => 37113]);
        $api->setStore($adminStore);

        $batch = $request->get('batch');
        if ($batch != null) {

            $brandData = $api->getBrands(intval($batch))['data']['module'];
            foreach ($brandData as $data) {
                $brand = new LazadaBrand();
                $brand->setRef($data['brand_id']);
                $brand->setName($data['name']);
                $em->persist($brand);
                $em->flush();
            }


            return $this->response();
        } else {
            $brandTotal = $api->getBrands(0, 1)['data']['total_record'];
            $calls = ceil($brandTotal / 200);

            return $this->render('batch.html.twig', [
                'url' => $this->generateUrl('lazada_brand_import', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'param' => 'batch',
                'calls' => $calls
            ]);
        }
    }

    #[Route('/category/search', name: '_category_search', methods: ['GET'])]
    public function categoryList(EntityManagerInterface $em, Request $request): Response
    {
        try {
            $name = $request->query->get('search');
            if (null == $name)
                return $this->response();

            /** @var LazadaCategoryRepository @catRepo */
            $catRepo = $em->getRepository(LazadaCategory::class);
            $results = $catRepo->search($name);
            return $this->response(array_map(fn($result) => ['name' => $result['name'] . ' (' . $result['path'] . ')', 'value' => $result['value']] ,$results));
        } catch (\Exception $e) {
            $this->handleException($e);
            return $this->response();
        }
    }

    #[Route('/category/import', name: '_category_import', methods: ['GET'])]
    public function categoryImport(EntityManagerInterface $em, LazadaProductAPI $api): Response
    {
        $adminStore = $em->getRepository(LazadaStore::class)->findOneBy(['ref' => 37113]);
        $api->setStore($adminStore);

        $categoryData = $data = $api->getCategories();
        /** @var LazadaCategoryRepository $categoryRepo */
        $categoryRepo = $em->getRepository(LazadaCategory::class);

        $categories = $categoryRepo->listByRef();
        $traverse = function (array $categoryData, EntityManagerInterface $em, int $level = 0, LazadaCategory $parent = null) use (&$categories, &$traverse) {
            $categoryId = $categoryData['category_id'];
            
            $category = $categories[$categoryId] ?? null;
            if ($category instanceof LazadaCategory)
                unset($categories[$categoryId]);
            else $category = new LazadaCategory($categoryId);

            $category->setRef($categoryId);
            $category->setName($categoryData['name']);
            $category->setLeaf($categoryData['leaf']);
            if($parent instanceof LazadaCategory)
                $category->setPath(($parent->getPath() == null ? '' : $parent->getPath() . " > ") . $parent->getName());
            else $category->setPath(null);
            
            $em->persist($category);
            if (isset($categoryData['children'])) {
                foreach ($categoryData['children'] as $childCategoryData) {
                    $childCategory = $traverse($childCategoryData, $em, $level + 1, $category);
                    $category->addChild($childCategory);
                }
            }
            return $category;
        };

        $categoryData = $api->getCategories()['data'];
        try {
            foreach ($categoryData as $data)
                $traverse($data, $em);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
        $em->flush();
        return $this->response();
    }
}
