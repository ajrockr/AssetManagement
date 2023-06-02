<?php

namespace App\Controller;

use App\Entity\RepairParts;
use App\Form\RepairPartsType;
use App\Repository\RepairPartsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/repair/parts')]
class RepairPartsController extends AbstractController
{
    #[Route('/', name: 'app_repair_parts_index', methods: ['GET'])]
    public function index(RepairPartsRepository $repairPartsRepository): Response
    {
        return $this->render('repair_parts/index.html.twig', [
            'repair_parts' => $repairPartsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_repair_parts_new', methods: ['GET', 'POST'])]
    public function new(Request $request, RepairPartsRepository $repairPartsRepository): Response
    {
        $repairPart = new RepairParts();
        $form = $this->createForm(RepairPartsType::class, $repairPart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repairPartsRepository->save($repairPart, true);

            return $this->redirectToRoute('app_repair_parts_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('repair_parts/new.html.twig', [
            'repair_part' => $repairPart,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_repair_parts_show', methods: ['GET'])]
    public function show(RepairParts $repairPart): Response
    {
        return $this->render('repair_parts/show.html.twig', [
            'repair_part' => $repairPart,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_repair_parts_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RepairParts $repairPart, RepairPartsRepository $repairPartsRepository): Response
    {
        $form = $this->createForm(RepairPartsType::class, $repairPart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repairPartsRepository->save($repairPart, true);

            return $this->redirectToRoute('app_repair_parts_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('repair_parts/edit.html.twig', [
            'repair_part' => $repairPart,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_repair_parts_delete', methods: ['POST'])]
    public function delete(Request $request, RepairParts $repairPart, RepairPartsRepository $repairPartsRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$repairPart->getId(), $request->request->get('_token'))) {
            $repairPartsRepository->remove($repairPart, true);
        }

        return $this->redirectToRoute('app_repair_parts_index', [], Response::HTTP_SEE_OTHER);
    }
}
