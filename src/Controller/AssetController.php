<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Form\AssetType;
use App\Repository\AssetRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/asset')]
class AssetController extends AbstractController
{
    #[Route('/', name: 'app_asset_index', methods: ['GET'])]
    public function index(AssetRepository $assetRepository, UserRepository $userRepository): Response
    {
        $assets = $assetRepository->findAll();
        $users = $userRepository->findAll();
        $assetsArray = [];
        foreach ($assets as $asset) {
            $usersName = '';
            if ($asset->getAssignedTo()) {
                // Get user information
                if ($user = $userRepository->findOneBy(['id' => $asset->getAssignedTo()])) {
                    $usersName = $user->getFirstname() . ' ' . $user->getSurname();
                }
            }

            $assetsArray[] = [
                'id' => $asset->getId(),
                'serialnumber' => $asset->getSerialnumber(),
                'assettag' => $asset->getAssettag(),
                'purchasedate' => $asset->getPurchasedate(),
                'purchasedfrom' => $asset->getPurchasedfrom(),
                'warrantystartdate' => $asset->getWarrantystartdate(),
                'warrantyenddate' => $asset->getWarrantyenddate(),
                'condition' => $asset->getCondition(),
                'make' => $asset->getMake(),
                'model' => $asset->getModel(),
                'assignedTo' => $usersName,
                'decomisioned' => $asset->isDecomisioned()
            ];
        }


        return $this->render('asset/index.html.twig', [
            'assets' => $assetsArray,
            'users' => $users
        ]);
    }

    #[Route('/assign', name: 'app_assign_user_to_device', methods: ['POST'])]
    public function assignUserToDevice(Request $request, AssetRepository $assetRepository, EntityManagerInterface $entityManager): Response
    {
        $data = $request->request->all();
        if ($asset = $assetRepository->findOneBy(['id' => $data['assetId']])) {
            $asset->setAssignedTo($data['userId']);
            $entityManager->persist($asset);
            $entityManager->flush();
            $this->addFlash('success', 'Assigned user to device.');
        } else {
            $this->addFlash('warning', 'Could not find asset.');
        }

        return $this->redirectToRoute('app_asset_index');
    }

    #[Route('/new', name: 'app_asset_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AssetRepository $assetRepository): Response
    {
        $asset = new Asset();
        $form = $this->createForm(AssetType::class, $asset);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $assetRepository->save($asset, true);

            return $this->redirectToRoute('app_asset_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('asset/new.html.twig', [
            'asset' => $asset,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_asset_show', methods: ['GET'])]
    public function show(Asset $asset): Response
    {
        return $this->render('asset/show.html.twig', [
            'asset' => $asset,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_asset_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Asset $asset, AssetRepository $assetRepository): Response
    {
        $form = $this->createForm(AssetType::class, $asset);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $assetRepository->save($asset, true);

            return $this->redirectToRoute('app_asset_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('asset/edit.html.twig', [
            'asset' => $asset,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_asset_delete', methods: ['POST'])]
    public function delete(Request $request, Asset $asset, AssetRepository $assetRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$asset->getId(), $request->request->get('_token'))) {
            $assetRepository->remove($asset, true);
        }

        return $this->redirectToRoute('app_asset_index', [], Response::HTTP_SEE_OTHER);
    }
}
