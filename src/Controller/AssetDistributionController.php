<?php

namespace App\Controller;

use App\Entity\AssetCollection;
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

    #[Route('/asset/distribution/roulette', name: 'app_asset_distribution_roulette')]
    public function roulette(AssetDistributionRepository $assetDistributionRepository, AssetRepository $assetRepository, UserRepository $userRepository): Response
    {
        $returnArray = [];
        foreach ($assetDistributionRepository->getDistributions() as $asset) {
            $assetInfo = $assetRepository->findOneBy(['id' => $asset->getDeviceId()]);
            $userInfo = $userRepository->findOneBy(['id' => $asset->getUserId()]);
            $returnArray[] = [
                'id' => $asset->getId(),
                'assettag' => !($assetInfo) ? 'null' : $assetInfo->getAssettag(),
                'serialnumber' => !($assetInfo) ? 'null' : $assetInfo->getSerialnumber(),
                'userFirstName' => !($userInfo) ? 'null' : $userInfo->getFirstname(),
                'userSurname' => !($userInfo) ? 'null' : $userInfo->getSurname(),
                'location' => ($asset->getLocation()) ?: null,
                'createdAt' => $asset->getCreatedAt()
            ];
        }

        return $this->render('asset_distribution/roulette.html.twig', [
            'assets' => $returnArray
        ]);
    }

    #[Route('/asset/distribution/clear/{id}', name: 'app_distribute_clear')]
    public function clear(AssetDistributionRepository $assetDistributionRepository, AssetCollectionRepository $assetCollectionRepository, int $id): Response
    {
        if (gettype($id) !== "integer") {
            throw new \Exception("Uh uh uh...");
        }

        $asset = $assetDistributionRepository->findOneBy(['id' => $id]);
        $assetId = $asset->getDeviceId();

        if ($assetCollection = $assetCollectionRepository->findOneBy(['DeviceID' => $assetId])) {
            $assetCollectionRepository->remove($assetCollection, true);
        }

        $asset->setDistributedBy($this->getUser()->getId());
        $asset->setDistributedAt(new \DateTimeImmutable('now'));
        $assetDistributionRepository->save($asset, true);

        return $this->redirectToRoute('app_asset_distribution_roulette');
    }

    #[Route('/asset/distribution/revoke', name: 'app_distribute_revoke')]
    public function revoke(AssetRepository $assetRepository, UserRepository $userRepository, AssetDistributionRepository $assetDistributionRepository): Response
    {
        $distributions = $assetDistributionRepository->getAllDistributions();
        $returnArray = [];

        foreach ($distributions as $distribution) {
            $assetInfo = $assetRepository->findOneBy(['id' => $distribution->getDeviceId()]);
            $userInfo = $userRepository->findOneBy(['id' => $distribution->getUserId()]);
            if (null === $distUserInfo = $userRepository->findOneBy(['id' => $distribution->getDistributedBy()])) {
                $distUserInfo = $userRepository->findOneBy(['id' => $distribution->getDistributionSetBy()]);
            }

            $returnArray[] = [
                'id' => $distribution->getId(),
                'assetTag' => $assetInfo->getAssettag(),
                'serialNumber' => $assetInfo->getSerialnumber(),
                'location' => $distribution->getLocation(),
                'distributedAt' => $distribution->getDistributedAt(),
                'distributedByFirstName' => $distUserInfo->getFirstname(),
                'distributedBySurname' => $distUserInfo->getSurname(),
                'userFirstName' => $userInfo->getFirstname(),
                'userSurname' => $userInfo->getSurname(),
                'notes' => $distribution->getNotes()
            ];
        }

        return $this->render('asset_distribution/revoke.html.twig', [
            'assets' => $returnArray
        ]);
    }

    #[Route('/asset/distribution/revokedist', name: 'app_distribute_revoke_id')]
    public function revokeAsset(Request $request, AssetDistributionRepository $assetDistributionRepository, AssetCollectionRepository $assetCollectionRepository): Response
    {
        $id = $request->request->get('distributionId');

        $assetDistribution = $assetDistributionRepository->findOneBy(['id' => $id]);
        $assetCollection = $assetCollectionRepository->findOneBy(['DeviceID' => $assetDistribution->getDeviceId()]);

        $assetDistribution->setDistributedAt(null);
        $assetDistribution->setDistributedBy(null);

        // If the collection item was removed, add it again
        if (null === $assetCollection) {
            $assetCollection = new AssetCollection;
            $assetCollection->setCollectedBy($this->getUser()->getId());
            $assetCollection->setDeviceID($assetDistribution->getDeviceId());
            $assetCollection->setCollectionLocation($assetDistribution->getLocation());
            $assetCollection->setCollectedFrom($assetDistribution->getUserId());
            $assetCollection->setCollectedDate($assetDistribution->getCreatedAt());
            $assetCollection->setCheckedout(false);
            $assetCollectionRepository->save($assetCollection, true);
        }

        $assetDistributionRepository->remove($assetDistribution, true);

        $this->addFlash('success', 'Revoked asset from distribution.');
        return $this->redirectToRoute('app_distribute_revoke');
    }
}
