<?php

namespace App\Controller;

use App\Entity\Repair;
use App\Form\RepairType;
use App\Repository\AssetRepository;
use App\Repository\RepairPartsRepository;
use App\Repository\RepairRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/repair')]
#[IsGranted('ROLE_ASSET_REPAIR_READ')]
class RepairController extends AbstractController
{
    public const STATUS_RESOLVED = 'status_resolved';
    public const STATUS_OPEN = 'status_open';
    public const STATUS_NOT_STARTED = 'status_not_started';
    private array $parts;
    public function __construct(
        private readonly RepairPartsRepository $repairPartsRepository,
        private readonly RepairRepository $repairRepository,
        private readonly AssetRepository $assetRepository
    )
    {
        $this->parts = $this->repairPartsRepository->getAllParts();
    }
    #[Route('/', name: 'app_repair_index', methods: ['GET'])]
    public function index(RepairRepository $repairRepository): Response
    {
        $repairs = $repairRepository->getAllOpen();
        $returnArray = [];

        foreach($repairs as $repair) {
            $returnArray[] = [
                'id' => $repair['r_id'],
                'assetUniqueIdentifier' => $repair['r_assetUniqueIdentifier'],
                'createdDate' => $repair['r_createdDate'],
                'startedDate' => $repair['r_startedDate'],
                'technicianId' => $repair['r_technicianId'],
                'issue' => $repair['r_issue'],
                'partsNeeded' => $this->convertPartIdsToName($repair['r_partsNeeded']),
                'status' => $repair['r_status'],
                'lastModifiedDate' => $repair['r_lastModifiedDate'],
            ];

        }

        return $this->render('repair/index.html.twig', [
            'repairs' => $returnArray,
        ]);
    }

    #[Route('/new', name: 'app_repair_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ASSET_REPAIR_MODIFY')]
    public function new(Request $request, RepairRepository $repairRepository, AssetRepository $assetRepository, ?array $data = null): Response
    {
        $repair = new Repair();
        $form = $this->createForm(RepairType::class, $repair);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->createRepair($repair, true)) {
                return $this->redirectToRoute('app_repair_new');
            }

            return $this->redirectToRoute('app_repair_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('repair/new.html.twig', [
            'repair' => $repair,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_ASSET_REPAIR_MODIFY')]
    public function createRepair(Repair|array $repairData, bool $isInternalReferred = false): bool
    {
        $repair = $repairData;

        // If a Repair entity instance was not passed
        if (!$repairData instanceof Repair) {
            $repair = new Repair;
            $repair->setAssetUniqueIdentifier($repairData['asset']);
            $repair->setIssue($repairData['issue']);
            $repair->setAssetId($repairData['assetId']);
            $repair->setStatus('Not Started');
            $repair->setPartsNeeded($repairData['partsNeeded']);
        }

        if (null === ($asset = $this->assetRepository->findOneBy(['asset_tag' => $repair->getAssetUniqueIdentifier()]))) {
            if (null === ($asset = $this->assetRepository->findOneBy(['serial_number' => $repair->getAssetUniqueIdentifier()]))) {
                $this->addFlash('error', 'The asset could not be found.');

                if ($isInternalReferred) {
                    return false;
                }
            }
        } else {
            $repair->setAssetId($asset->getId());
        }

        $repair->setCreatedDate(new \DateTimeImmutable('now'));
        $repair->setLastModifiedDate(new \DateTimeImmutable('now'));

        try {
            $this->repairRepository->save($repair, true);
        } catch(\Exception $e) {
            $this->addFlash('error', 'There was an error creating the repair.');
            return false;
        }

        $this->addFlash('success', 'Repair created successfully.');
        return true;
    }

    #[Route('/{id}/show', name: 'app_repair_show', methods: ['GET'])]
    #[IsGranted('ROLE_ASSET_REPAIR_READ')]
    public function show(Repair $repairEntity): Response
    {
//        $getRepair = $repairRepository->getRepair($id);
//        dd($getRepair);
//        $parts = $this->convertPartIdsToName($getRepair['parts_needed']);
//        $getRepair = array_merge($getRepair, $parts);
        $repairParts = $this->convertPartIdsToName($repairEntity->getPartsNeeded());

        return $this->render('repair/show.html.twig', [
            'repair' => $repairEntity,
            'parts' => $repairParts
        ]);
    }

    #[Route('/{id}/edit', name: 'app_repair_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ASSET_REPAIR_MODIFY')]
    public function edit(Request $request, Repair $repair): Response
    {
        $form = $this->createForm(RepairType::class, $repair);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repairRepository->save($repair, true);

            return $this->redirectToRoute('app_repair_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('repair/edit.html.twig', [
            'repair' => $repair,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_repair_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ASSET_REPAIR_MODIFY')]
    public function delete(Request $request, Repair $repair): Response
    {
        if ($this->isCsrfTokenValid('delete'.$repair->getId(), $request->request->get('_token'))) {
            $this->repairRepository->remove($repair, true);
        }

        return $this->redirectToRoute('app_repair_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Convert part IDs to part names
     *
     * @param array $parts
     * @return array
     */
    private function convertPartIdsToName(array $parts = []): array
    {
        $partsArray = [];
        foreach ($this->parts as $part) {
            foreach ($parts as $partId) {
                if (in_array($partId, $part)) {
                    $partsArray[] = $part['name'];
                }
            }
        }

        return array_values($partsArray);
    }

    public function setResolved(int $id): Response
    {
        $this->repairRepository->findOneBy(['id' => $id])->setStatus(self::STATUS_RESOLVED);
        return $this->redirectToRoute('app_repair_index');
    }
}
