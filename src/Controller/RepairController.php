<?php

namespace App\Controller;

use App\Entity\Repair;
use App\Form\RepairType;
use App\Repository\AssetRepository;
use App\Repository\RepairRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/repair')]
class RepairController extends AbstractController
{
    #[Route('/', name: 'app_repair_index', methods: ['GET'])]
    public function index(RepairRepository $repairRepository): Response
    {
        $repairs = $repairRepository->getAllOpen();
        $returnArray = [];

        foreach($repairs as $repair) {
            $returnArray[] = [
                'id' => $repair['id'],
                'assetUniqueIdentifier' => $repair['asset_identifier'],
                'createdDate' => $repair['created_date'],
                'startedDate' => $repair['started_date'],
                'technicianId' => $repair['technician'],
                'issue' => $repair['issue'],
                'partsNeeded' => $repair['parts_needed'],
                'status' => $repair['status'],
                'lastModifiedDate' => $repair['modified_date']
            ];
        }
        return $this->render('repair/index.html.twig', [
            'repairs' => $returnArray,
        ]);
    }

    #[Route('/new', name: 'app_repair_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RepairRepository $repairRepository, AssetRepository $assetRepository, ?array $data = null): Response
    {
        $repair = new Repair();
        $form = $this->createForm(RepairType::class, $repair);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->createRepair($assetRepository, $repairRepository, $repair, true)) {
                return $this->redirectToRoute('app_repair_new');
            }

            return $this->redirectToRoute('app_repair_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('repair/new.html.twig', [
            'repair' => $repair,
            'form' => $form,
        ]);
    }

    public function createRepair(AssetRepository $assetRepository, RepairRepository $repairRepository, Repair|array $repairData, bool $isInternalReferred = false): bool
    {
        $repair = $repairData;

        // If a Repair entity instance was not passed
        if (!$repairData instanceof Repair) {
            $repair = new Repair;
            $repair->setAssetUniqueIdentifier($repairData['asset']);
            $repair->setIssue($repairData['issue']);
            $repair->setAssetId($repairData['assetId']);
            $repair->setStatus('Not Started');
        }

        if (null === ($asset = $assetRepository->findOneBy(['assettag' => $repair->getAssetUniqueIdentifier()]))) {
            if (null === ($asset = $assetRepository->findOneBy(['serialnumber' => $repair->getAssetUniqueIdentifier()]))) {
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
            $repairRepository->save($repair, true);
        } catch(\Exception $e) {
            $this->addFlash('error', 'There was an error creating the repair.');
            return false;
        }

        $this->addFlash('success', 'Repair created successfully.');
        return true;
    }

    #[Route('/{id}/show', name: 'app_repair_show', methods: ['GET'])]
    public function show(Repair $repair): Response
    {
        return $this->render('repair/show.html.twig', [
            'repair' => $repair,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_repair_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Repair $repair, RepairRepository $repairRepository): Response
    {
        $form = $this->createForm(RepairType::class, $repair);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repairRepository->save($repair, true);

            return $this->redirectToRoute('app_repair_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('repair/edit.html.twig', [
            'repair' => $repair,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_repair_delete', methods: ['POST'])]
    public function delete(Request $request, Repair $repair, RepairRepository $repairRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$repair->getId(), $request->request->get('_token'))) {
            $repairRepository->remove($repair, true);
        }

        return $this->redirectToRoute('app_repair_index', [], Response::HTTP_SEE_OTHER);
    }
}
