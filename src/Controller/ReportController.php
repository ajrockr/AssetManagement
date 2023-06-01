<?php

namespace App\Controller;

use App\Repository\AssetCollectionRepository;
use App\Repository\AssetRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/report', name: 'app_report')]
class ReportController extends AbstractController
{
    #[Route('/', name: 'app_report_index')]
    public function index(): Response
    {
        return $this->render('report/index.html.twig', [
            'controller_name' => 'ReportController',
        ]);
    }

    #[Route('/collectedassets', name: 'app_report_collected_assets')]
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

        dd($reportArray);

        return $this->render('report/collectedassets.html.twig');
    }
}
