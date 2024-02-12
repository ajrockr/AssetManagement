<?php

namespace App\Controller;

use App\Entity\Repair;
use App\Entity\RepairParts;
use App\Form\RepairType;
use App\Repository\AssetRepository;
use App\Repository\RepairPartsRepository;
use App\Repository\RepairRepository;
use App\Repository\UserRepository;
use App\Service\UserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
        private readonly AssetRepository $assetRepository,
        private readonly UserService $userService,
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

    #[Route('/{id}', name: 'app_repair_show', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ASSET_REPAIR_READ')]
    public function show(Request $request, int $id): Response
    {
        $canRepair = $this->userService->hasRole('ROLE_REPAIR_TECHNICIAN', $this->getUser()->getRoles());

        // Set the repairEntity, if no entity exists then redirect to list
        if ( !$repairEntity = $this->repairRepository->findOneBy(['id' => $id])) {
            $this->addFlash('error', 'Repair ID does not exist.');
            return $this->redirectToRoute('app_repair_index');
        }

        // Get parts needed for entity
        $partsNeeded = [];
        foreach ($repairEntity->getPartsNeeded() as $parts) {
            $partsNeeded[] = $parts['id'];
        }

        // Create the editing form
        $form = $this->createFormBuilder(options: ['disabled' => !$canRepair])
            ->add('repairId', HiddenType::class, [
                'data' => $repairEntity->getId()
            ])
            ->add('assetId', HiddenType::class, [
                'data' => $repairEntity->getAssetId()
            ])
            ->add('technicianId', HiddenType::class, [
                'data' => $repairEntity->getTechnicianId()
            ])
            ->add('createdDate', DateTimeType::class, [
                'data' => $repairEntity->getCreatedDate()
            ])
            ->add('startedDate', DateTimeType::class, [
                'data' => $repairEntity->getStartedDate()
            ])
            ->add('modifiedDate', DateTimeType::class, [
                'data' => $repairEntity->getLastModifiedDate()
            ])
            ->add('issue', TextareaType::class, [
                'data' => $repairEntity->getIssue()
            ])
            ->add('parts', EntityType::class, [
                'class' => RepairParts::class,
                'expanded' => true,
                'multiple' => true,
                'choice_label' => 'name',
                'choice_attr' => function ($choice, string $key, mixed $value) use ($partsNeeded) {
                    return [
                        'class' => 'form-check-input',
                        'checked' => in_array($choice->getId(), $partsNeeded),
                    ];
                },
            ])
            ->add('actionstaken', TextType::class)
            ->add('status', ChoiceType::class, [
                // TODO: Make this based off config
                'choices' => [
                    'Not Started' => Repair::STATUS_NOT_STARTED,
                    'In Progress' => Repair::STATUS_IN_PROGRESS,
                    'Deferred' => Repair::STATUS_DEFERRED,
                    'Waiting On Parts' => Repair::STATUS_WAITING_ON_PARTS,
                    'Waiting On Technician' => Repair::STATUS_WAITING_ON_TECHNICIAN,
                    'Waiting On User' => Repair::STATUS_WAITING_ON_USER,
                    'Resolved' => Repair::STATUS_CLOSED,
                    'Open' => Repair::STATUS_OPEN,
                ],
                'data' => $repairEntity->getStatus()
            ])
            ->add('usersfollowing', TextType::class)
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ( !$canRepair) exit;

            $data = $form->getData();

            // blah blah blah
        }


        return $this->render('repair/show.html.twig', [
            'repair' => $repairEntity,
            'form' => $form->createView(),
            'canRepair' => $canRepair,
        ]);
    }

    private function prettifyRepairStatus(string $repairStatus): string
    {
        return ucfirst(str_replace('_', ' ', $repairStatus));
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
