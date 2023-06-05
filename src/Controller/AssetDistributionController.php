<?php

namespace App\Controller;

use App\Entity\AssetDistribution;
use App\Repository\AssetCollectionRepository;
use App\Repository\AssetDistributionRepository;
use App\Repository\AssetRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AssetDistributionController extends AbstractController
{
    #[Route('/asset/distribution', name: 'app_asset_distribution')]
    public function index(AssetDistributionRepository $assetDistributionRepository, AssetCollectionRepository $assetCollectionRepository, AssetRepository $assetRepository, UserRepository $userRepository): Response
    {
        $returnArray = [];
        $assetsCollected = $assetCollectionRepository->findAll();

        foreach ($assetsCollected as $assetCollected) {
            $asset = $assetRepository->findOneBy(['id' => $assetCollected->getDeviceID()]);
            $assignedUser = $userRepository->findOneBy(['id' => $assetCollected->getCollectedFrom()]);
            $assetData = [
                'id' => $assetCollected->getDeviceID(),
                'assetTag' => $asset->getAssettag(),
                'serialNumber' => $asset->getSerialnumber(),
                'userId' => $assetCollected->getCollectedFrom(),
                'userFirstName' => $assignedUser->getFirstname(),
                'userSurname' => $assignedUser->getSurname(),
                'location' => $assetCollected->getCollectionLocation()
            ];

            // If asset has already been distributed
            if (null !== $assetDistributionRepository->findOneBy(['deviceId' => $assetCollected->getDeviceID()])) {
                $returnArray[] = array_merge($assetData, ['sentToDistribution' => 1]);
            } else {
                $returnArray[] = $assetData;
            }
        }

        return $this->render('asset_distribution/index.html.twig', [
            'collectedAssets' => $returnArray
        ]);
    }

    #[Route('/asset/distribute', name: 'app_distribute_handle')]
    public function distributeAsset(Request $request, AssetDistributionRepository $assetDistributionRepository): Response
    {
        $assetId = $request->request->get('assetId');
        $userId = $request->request->get('userId');
        $notes = $request->request->get('note');
        $location = $request->request->get('location');

        $distributeEntity = new AssetDistribution;
        $distributeEntity->setDistributionSetBy($this->getUser()->getId());
        $distributeEntity->setCreatedAt(new \DateTimeImmutable('now'));
        $distributeEntity->setDeviceId($assetId);
        $distributeEntity->setUserId($userId);
        $distributeEntity->setNotes($notes);
        $distributeEntity->setLocation($location);

        $assetDistributionRepository->save($distributeEntity, true);
        $this->addFlash('success', 'Sent asset for distribution.');

        return $this->redirectToRoute('app_asset_distribution');
    }
}
