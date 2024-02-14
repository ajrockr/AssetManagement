<?php

namespace App\Controller;

use App\Entity\Repair;
use App\Entity\RepairParts;
use App\Repository\RepairPartsRepository;
use App\Repository\RepairRepository;
use App\Repository\UserRepository;
use App\Service\RepairService;
use App\Service\UserService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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

    public function __construct(
        private readonly RepairRepository $repairRepository,
        private readonly RepairService $repairService,
        private readonly UserService $userService,
        private readonly UserRepository $userRepository,
    ) { }
    #[Route('/', name: 'app_repair_index', methods: ['GET'])]
    public function index(RepairRepository $repairRepository): Response
    {
        $repairs = $repairRepository->getAllOpen();
        $returnArray = [];

        foreach($repairs as $repair) {
            $parts = [];
            foreach($repair['r_partsNeeded'] as $partsNeeded) {
                $parts[] = $partsNeeded['name'];
            }

            $returnArray[] = [
                'id' => $repair['r_id'],
                'assetUniqueIdentifier' => $repair['r_assetUniqueIdentifier'],
                'createdDate' => $repair['r_createdDate'],
                'startedDate' => $repair['r_startedDate'],
                'technicianId' => $this->userRepository->getFullName($repair['r_technicianId']) ?? 'Not Set',
                'issue' => $repair['r_issue'],
                'partsNeeded' => $parts,
                'status' => $repair['r_status'],
                'lastModifiedDate' => $repair['r_lastModifiedDate'],
            ];
        }

        return $this->render('repair/index.html.twig', [
            'repairs' => $returnArray,
        ]);
    }

    #[Route('/{id}', name: 'app_repair_show', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ASSET_REPAIR_READ')]
    public function show(Request $request, int $id): Response
    {
        $canRepair = $this->userService->hasRole(['ROLE_REPAIR_TECHNICIAN'], $this->getUser()->getRoles());

        // Set the repairEntity, if no entity exists then redirect to list
        if ( !$repairEntity = $this->repairRepository->findOneBy(['id' => $id])) {
            $this->addFlash('error', 'Repair ID does not exist.');
            return $this->redirectToRoute('app_repair_index');
        }

        $submittedBy = $this->userRepository->getFullName($repairEntity->getSubmittedById());

        $assignedTechnician = (null !== $repairEntity->getTechnicianId()) ? $this->userRepository->getFullName($repairEntity->getTechnicianId()) : 'Not Assigned';

        $validTechnicians = [];
        if ( !empty($validTechs = $this->getValidTechnicians())) {
            foreach ($validTechs as $tech) {
                $validTechnicians[$tech->getSurname() . ', ' . $tech->getFirstname()] = $tech->getId();
            }
        }
        array_unshift($validTechnicians, '');

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
            ->add('issue', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ],
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
            ->add('actionstaken', TextType::class, [
                'label' => 'Actions Taken',
                'attr' => [
                    'class' => 'form-control'
                ],
                'label_attr' => [
                    'class' => 'input-group-text'
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'attr' => [
                    'class' => 'form-select'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ],
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
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->getForm()
        ;

        $assignTechnicianForm = $this->createFormBuilder()
            ->add('assignatechnician', ChoiceType::class, [
                'choices' => $validTechnicians,
                'data' => $repairEntity->getTechnicianId()
            ])
            ->add('assigntechnicianbutton', SubmitType::class, [
                'label' => 'Assign',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->add('unassigntechnicianbutton', SubmitType::class, [
                'label' => 'Unassign Technician',
                'attr' => [
                    'class' => 'btn btn-secondary',
                    'disabled' => $repairEntity->getTechnicianId() === null
                ],
            ])
            ->getForm()
        ;

        // Assign a technician
        $assignTechnicianForm->handleRequest($request);
        if ($assignTechnicianForm->isSubmitted() && $assignTechnicianForm->isValid()) {
            if ($assignTechnicianForm->get('unassigntechnicianbutton')->isClicked()) {
                $this->repairService->unassignTechnician($id);
            } else {
                $this->repairService->assignTechnician($id, (int)$assignTechnicianForm->get('assignatechnician')->getViewData());
            }

            return $this->redirect($request->headers->get('referer'));
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ( !$canRepair) exit;

            $data = $form->getData();

            // blah blah blah
        }


        return $this->render('repair/show.html.twig', [
            'repair' => $repairEntity,
            'submittedBy' => $submittedBy,
            'assignedTechnician' => $assignedTechnician,
            'form' => $form->createView(),
            'assignTechnicianForm' => $assignTechnicianForm->createView(),
            'canRepair' => $canRepair,
            'repairTechnicians' => $this->getValidTechnicians(),
        ]);
    }

    private function setResolved(int $id): Response
    {
        $this->repairRepository->findOneBy(['id' => $id])->setStatus(self::STATUS_RESOLVED);
        return $this->redirectToRoute('app_repair_index');
    }

    private function getValidTechnicians(): array
    {
        $returnArray = [];
        foreach ($this->userRepository->findAll() as $user) {
            if ($this->userService->hasRole(['ROLE_REPAIR_TECHNICIAN'], $user->getRoles())) {
                $returnArray[] = $user;
            }
        }
        return $returnArray;
    }
}
