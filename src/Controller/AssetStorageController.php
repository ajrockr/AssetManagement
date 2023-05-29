<?php

namespace App\Controller;

use App\Entity\AssetStorage;
use App\Form\AssetStorageNewType;
use App\Repository\AssetStorageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route('/storage')]
class AssetStorageController extends AbstractController
{
    #[Route('/', name: 'app_storage')]
    public function index(AssetStorageRepository $assetStorageRepository): Response
    {
        $storageAll = $assetStorageRepository->findAll();

        return $this->render('asset_storage/index.html.twig', [
            'storageAll' => $storageAll
        ]);
    }

    #[Route('/new', name: 'app_storage_new')]
    public function new(Request $request, AssetStorageRepository $assetStorageRepository): Response
    {
//        $encoder = [new JsonEncoder()];
//        $normalizer = [new ObjectNormalizer()];
//        $serializer = new Serializer($normalizer, $encoder);
//
        $form = $this->createForm(AssetStorageNewType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $jsonStorageData = json_decode(
                trim(
                    preg_replace('/\s\s+/', '', $data['storageData'])
                ), true
            );

            $assetStorage = new AssetStorage();
            $assetStorage->setName($data['name']);
            $assetStorage->setDescription($data['description']);
            $assetStorage->setLocation($data['location']);
            $assetStorage->setStorageData($jsonStorageData);

            $assetStorageRepository->save($assetStorage, true);

            return $this->redirectToRoute('app_storage', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('asset_storage/_new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'app_storage_view')]
    public function show(AssetStorageRepository $assetStorageRepository, int $id): Response
    {
        $storage = $assetStorageRepository->findOneBy(['id' => $id]);
        return $this->render('asset_storage/_view.html.twig', [
            'storage' => $storage
        ]);
    }

    #[Route('/{id}/update', name: 'app_storage_update')]
    public function update(int $id): Response
    {
        return new Response();
    }

    #[Route('/{id}/delete', name: 'app_storage_delete')]
    public function delete(int $id): Response
    {
        return new Response();
    }
}
