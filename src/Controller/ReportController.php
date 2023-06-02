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
    public function assetsPerStorage(Request $request, AssetStorageRepository $assetStorageRepository, AssetCollectionRepository $assetCollectionRepository): Response
    {
        $assetStorages = $assetStorageRepository->findAll();
        $collectedAssets = $assetCollectionRepository->findAll();

        foreach ($assetStorages as $assetStorage) {
            $storages[] = [
                'name' => $assetStorage->getName(),
                'data' => $assetStorage->getStorageData()
            ];
        }

        $reportArray = [];
        $count = 1;
        foreach($storages as $storage) {
            foreach($collectedAssets as $collectedAsset) {
                $index = $this->array_compound_key_alias($storage);

                if (in_array($collectedAsset->getCollectionLocation(), $index)) {
                    $reportArray[$storage['name']] = $count++;
                }
            }
            $count = 1;
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
