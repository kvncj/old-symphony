<?php

namespace App\Controller\API;

use App\Controller\ExtendedController;
use App\Entity\Image;
use App\Entity\User;
use App\Service\ImageService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/image', name: 'image')]
class ImageController extends ExtendedController
{
    private EntityManagerInterface $em;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em)
    {
        parent::__construct($logger);
        $this->em = $em;
        //$this->imageService = $imageService;
    }

    #[Route('/upload', name: '_upload', methods: ['POST'])]
    public function upload(EntityManagerInterface $em, ImageService $imageService, Request $request): Response
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            if (!$user instanceof User)
                throw new \Exception("No user in session.");

            $params = $request->request->all();
            if (!isset($params['src'], $params['mime'])) {
                $params = $request->attributes->all();
                if (!isset($params['src'], $params['mime']))
                    throw new \Exception("Required parameters not found.");
            }

            $extension = $imageService->getExtFromMime($params['mime']);
            $name = $params['name'] ?? (rand(5, 20) . time() . ".$extension");
            $path = $imageService->uploadImage($name, $params['src'], 'bargus/' . $user->getId());

            $image = new Image();
            $image->setPath($path);

            $em->persist($image);
            $em->flush();

            return $this->response([
                'id' => $image->getId(),
                'name' => $name,
                'path' => $path,
                'url' => $image->getUrl()
            ]);
        } catch (\Exception $e) {
            if (isset($path)) $imageService->deleteImage($path);
            $this->handleException($e);
            return $this->response([$e->getMessage()]);
        }
    }

    public function delete(int $id, EntityManagerInterface $em, ImageService $imageService): Response
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            if (!$user instanceof User)
                throw new \Exception("No user in session.");

            $image = $em->getRepository(Image::class)->find($id);
            $imageService->deleteImage($image->getPath());
            $em->remove($image);
        } catch (\Exception $e) {
            $this->handleException($e);
        }

        return $this->response();
    }
}
