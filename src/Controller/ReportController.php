<?php

namespace App\Controller;

use App\Repository\AssetCollectionRepository;
use App\Repository\AssetRepository;
use App\Repository\AssetStorageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/report')]
class ReportController extends AbstractController
{
    #[Route('/', name: 'app_report_index')]
    public function index(): Response
    {
        return $this->render('report/index.html.twig', [
            'controller_name' => 'ReportController',
        ]);
    }

    #[Route('/collectedassets', name: 'app_report_collectedassets')]
    public function collectedAssets(Request $request, AssetRepository $assetRepository, UserRepository $userRepository, AssetCollectionRepository $assetCollectionRepository): Response
    {
        $collectedAssets = $assetCollectionRepository->findAll();
	$reportArray = [];
	$assignedTo = [];

        foreach ($collectedAssets as $collectedAsset) {
            $user = $userRepository->findOneBy(['id' => $collectedAsset->getCollectedFrom()]);
            $asset = $assetRepository->findOneBy(['id' => $collectedAsset->getDeviceID()]);

            // Check if collected from is different from assigned to
            if ($asset->getAssignedTo() !== $collectedAsset->getCollectedFrom()) {
                $diffUser = $userRepository->findOneBy(['id' => $asset->getAssignedTo()]);
                $assignedTo = [
                    'assignedToFirstName' => $diffUser->getFirstname(),
                    'assignedToSurName' => $diffUser->getSurname(),
                    'assignedToTitle' => $diffUser->getTitle(),
                    'assignedToType' => $diffUser->getType(),
                    'assignedToEmail' => $diffUser->getEmail(),
                    'assignedToUniqueId' => $diffUser->getUserUniqueId()
                ];
            }

            $collectedFrom = [
                'collectedFromFirstName' => $user->getFirstname(),
                'collectedFromSurName' => $user->getSurname(),
                'collectedFromTitle' => $user->getTitle(),
                'collectedFromType' => $user->getType(),
                'collectedFromEmail' => $user->getEmail(),
                'collectedFromUniqueId' => $user->getUserUniqueId(),
                'collectedDeviceAssetTag' => $asset->getAssettag(),
                'collectedDeviceSerialNumber' => $asset->getSerialnumber()
            ];

            $reportArray[] = array_merge($assignedTo, $collectedFrom);
        }

        return $this->render('report/collectedassets.html.twig', [
            'reportData' => $reportArray
        ]);
    }

    #[Route('/usersnotcollected/{userType}', name: 'app_report_usersnotcollected')]
    public function usersNotCollected(Request $request, UserRepository $userRepository, AssetCollectionRepository $assetCollectionRepository, string $userType = null): Response
    {
        $assetsQuery = $assetCollectionRepository->findAll();
        $users = $userRepository->findBy(['type' => ucfirst($userType)]);
        $reportArray = [];

        if (null === $users) {
            return $this->render('report/usersnotcollected.html.twig', [
                'reportData' => $reportArray
            ]);
        }

        $assetsCollected = [];
        foreach($assetsQuery as $asset) {
            $assetsCollected[] = $asset->getCollectedFrom();
        }

        foreach ($users as $user) {
            if (!in_array($user->getId(), $assetsCollected)) {
                $reportArray[] = [
                    'firstName' => $user->getFirstname(),
                    'surName' => $user->getSurname(),
                    'uniqueId' => $user->getUserUniqueId(),
                    'email' => $user->getEmail(),
                    'title' => $user->getTitle()
                ];
            }
        }

        return $this->render('report/usersnotcollected.html.twig', [
            'reportData' => $reportArray
        ]);
    }

    #[Route('/assetsperstorage', name: 'app_report_assetsperstorage')]
    public function assetsPerStorage(AssetStorageRepository $assetStorageRepository, AssetCollectionRepository $assetCollectionRepository, ?int $storageId = null): array|Response
    {
        $assetStorages = (null === $storageId) ? $assetStorageRepository->findAll() : $assetStorageRepository->findOneBy(['id' => $storageId]);
        $collectedAssets = $assetCollectionRepository->findAll();

        $storages = [];
        if (gettype($assetStorages) == 'array') {
            foreach ($assetStorages as $assetStorage) {
                $storages[] = [
                    'name' => $assetStorage->getName(),
                    'data' => $assetStorage->getStorageData()
                ];
            }
        } else {
            $storages[] = [
                'name' => $assetStorages->getName(),
                'data' => $assetStorages->getStorageData()
            ];
        }

        $reportArray = [];
        $count = 1;
        foreach($storages as $storage) {
            foreach($collectedAssets as $collectedAsset) {
                $index = $this->array_compound_key_alias($storage);
                $storageDataCount = count($index) - 1;

                if (in_array($collectedAsset->getCollectionLocation(), $index)) {
                    $reportArray[$storage['name']] = [
                        'collected' => $count++,
                        'total' => $storageDataCount
                    ];
                }
            }
            $count = 1;
        }

        if (!is_null($storageId)) {
            return $reportArray;
        }

        return $this->render('report/collectedassetsperstorage.html.twig', [
            'reportData' => $reportArray
        ]);
    }

    /**
     * create an array of compound array keys aliasing the non-array values
     * of the original array.
     * https://stackoverflow.com/a/9020725
     * by https://stackoverflow.com/users/367456/hakre
     *
     * @param string $separator
     * @param array $array
     * @return array
     */
    private function array_compound_key_alias(array &$array, $separator = '.')
    {
        $index = array();
        foreach($array as $key => &$value)
        {
            if (is_string($key) && FALSE !== strpos($key, $separator))
            {
                throw new \InvalidArgumentException(sprintf('Array contains key ("%s") with separator ("%s").', $key, $separator));
            }
            if (is_array($value))
            {
                $subindex = $this->array_compound_key_alias($value, $separator);
                foreach($subindex as $subkey => &$subvalue)
                {
                    $index[$key.$separator.$subkey] = &$subvalue;
                }
            }
            else
            {
                $index[$key] = &$value;
            }
        }
        return $index;
    }
}
