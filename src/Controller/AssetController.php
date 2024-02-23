<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Form\AssetType;
use App\Repository\UserRepository;
use App\Repository\AssetRepository;
use App\Repository\RepairRepository;
use App\Repository\SiteConfigRepository;
use App\Service\AssetCollectionService;
use App\Service\LoggerService;
use App\Service\RepairService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AssetStorageRepository;
use App\Repository\AssetCollectionRepository;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/asset')]
// TODO This was just causing a bug when logging in
//#[IsGranted('ROLE_ASSET_READ')]
class AssetController extends AbstractController
{
    public function __construct(
        protected readonly EventDispatcherInterface  $eventDispatcher,
        protected readonly EntityManagerInterface    $entityManager,
        protected readonly LoggerService             $logger,
        protected readonly AssetCollectionRepository $assetCollectionRepository,
        protected readonly AssetStorageRepository    $assetStorageRepository,
        protected readonly AssetRepository           $assetRepository,
        protected readonly SiteConfigRepository      $siteConfigRepository,
        protected readonly RepairRepository          $repairRepository,
        protected readonly RepairService             $repairService,
        protected readonly AssetCollectionService    $assetCollectionService,
    ) {}

    #[Route('/', name: 'app_asset_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $assets = $this->assetRepository->findAll();
        $users = $userRepository->findAll();
        $assetsArray = [];
        foreach ($assets as $asset) {
            $usersName = '';
            if ($asset->getAssignedTo()) {
                // Get user information
                if ($user = $userRepository->findOneBy(['id' => $asset->getAssignedTo()])) {
                    $usersName = $user->getFirstname() . ' ' . $user->getSurname();
                }
            }

            $assetsArray[] = [
                'id' => $asset->getId(),
                'serialnumber' => $asset->getSerialNumber(),
                'assettag' => $asset->getAssetTag(),
                'purchasedate' => $asset->getPurchaseDate(),
                'purchasedfrom' => $asset->getPurchasedFrom(),
                'warrantystartdate' => $asset->getWarrantyStartDate(),
                'warrantyenddate' => $asset->getWarrantyEndDate(),
                'condition' => $asset->getAssetCondition(),
                'make' => $asset->getMake(),
                'model' => $asset->getModel(),
                'assignedTo' => $usersName,
                'decommissioned' => $asset->isDecommissioned()
            ];
        }


        return $this->render('asset/index.html.twig', [
            'assets' => $assetsArray,
            'users' => $users
        ]);
    }

    #[Route('/assign', name: 'app_assign_user_to_device', methods: ['POST'])]
//    #[IsGranted('ROLE_ASSET_MODIFY')]
    public function assignUserToDevice(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = $request->request->all();
        if ($asset = $this->assetRepository->findOneBy(['id' => $data['assetId']])) {
            $asset->setAssignedTo($data['userId']);
            $entityManager->persist($asset);
            $entityManager->flush();
            $this->addFlash('success', 'Assigned user to device.');
        } else {
            $this->addFlash('warning', 'Could not find asset.');
        }

        return $this->redirectToRoute('app_asset_index');
    }

    #[Route('/new', name: 'app_asset_new', methods: ['GET', 'POST'])]
//    #[IsGranted('ROLE_ASSET_MODIFY')]
    public function new(Request $request): Response
    {
        $asset = new Asset();
        $form = $this->createForm(AssetType::class, $asset);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->assetRepository->save($asset, true);

            $this->addFlash('success', 'Added new asset.');

            return $this->redirectToRoute('app_asset_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('asset/new.html.twig', [
            'asset' => $asset,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_asset_edit', methods: ['GET', 'POST'])]
//    #[IsGranted('ROLE_ASSET_MODIFY')]
    public function edit(Request $request, Asset $asset): Response
    {
        $form = $this->createForm(AssetType::class, $asset);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->assetRepository->save($asset, true);

            $this->addFlash('success', 'Edited asset.');

            return $this->redirectToRoute('app_asset_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('asset/edit.html.twig', [
            'asset' => $asset,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_asset_delete')]
//    #[IsGranted('ROLE_ASSET_MODIFY')]
    public function delete(Asset $asset): Response
    {
        try {
            $this->assetRepository->remove($asset, true);
        } catch (Exception) {
            $this->addFlash('warning', 'Failed to delete asset.');
            return $this->redirectToRoute('app_asset_index', [], Response::HTTP_SEE_OTHER);
        }

        $this->addFlash('success', 'Deleted asset.');

        return $this->redirectToRoute('app_asset_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @throws NonUniqueResultException
     */
    #[Route('/collection/collect', name: 'app_asset_collection_collect', methods: 'POST')]
    public function checkInForm(Request $request, AssetStorageRepository $assetStorageRepository, UserRepository $userRepository, AssetCollectionRepository $assetCollectionRepository): Response|array
    {
        // Form 1) Select which cart
        $storages = $assetStorageRepository->findAll();

        $storagesFormArray = [];
        foreach ($storages as $storage) {
            if ( !$storage->getDescription()) {
                $storagesFormArray[$storage->getName()] = $storage->getId();
            } else {
                $storagesFormArray[$storage->getName() . ' (' . $storage->getDescription() . ')'] = $storage->getId();
            }
        }

        // Form 2) Fetch all users
        $users = $userRepository->findAll();

        $usersFormArray = [];
        foreach ($users as $user) {
            if ('Admin' == $user->getUserIdentifier()) continue;
            if ( !($uid = $user->getUserUniqueId())) {
                $usersFormArray[$user->getSurname() . ', ' . $user->getFirstname()] = $user->getId();
            } else {
                $usersFormArray[$user->getSurname() . ', ' . $user->getFirstname() . ' (' . $uid . ')'] = $user->getId();
            }

        }
        array_unshift($usersFormArray, '');

        $collectionForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_asset_collection_collect'))
            ->add('storageId', ChoiceType::class, [
                'choices' => $storagesFormArray
            ])
            ->add('assigned_to', ChoiceType::class, [
                'choices' => $usersFormArray,
                'multiple' => false,
                'attr' => [
                    'class' => 'form-control js-example-basic-single',
                ]
            ])
            ->add('asset_tag', TextType::class, [
                'required' => $this->siteConfigRepository->findOneByName('asset_asset_tag_required') == "true"
            ])
            ->add('asset_serial', TextType::class, [
                'required' => $this->siteConfigRepository->findOneByName('asset_serial_number_required') == "true"
            ])
            ->add('Collect', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->getForm()
        ;

        $collectionForm->handleRequest($request);

        if ($collectionForm->isSubmitted() && $collectionForm->isValid()) {
            $data = $collectionForm->getData();

            $storageEntity = $this->assetStorageRepository->findOneBy(['id' => $data['storageId']]);
            $storageData = $this->assetStorageRepository->getStorageData($storageEntity->getId());

            $assignedAssets = $assetCollectionRepository->getCollectedAssetSlots();

            $openStorageSlots = array_map('intval', array_diff($storageData, $assignedAssets));

            $asset = $this->assetCollectionService->createOrUpdateAsset($data);

            $assetId = $asset->getId();

            if ($assetCollected = $this->assetCollectionService->assetIsCollected($assetId)) {
                $this->addFlash('assetAlreadyCollected', [$assetCollected->getCollectionLocation(), $storageEntity->getName(), true]);
                return $this->redirect($request->headers->get('referer'));
            }

            $order = $this->siteConfigRepository->findOneByName('storage_collection_sort_slots_order');
            if ($order === 'desc') {
                rsort($openStorageSlots);
            } else {
                sort($openStorageSlots);
            }

            $nextOpenSlot = reset($openStorageSlots);

            $data['location'] = $nextOpenSlot;

            // If the storage is full, set location to null and notify user
            if(count($openStorageSlots) == 0) {
                $data['location'] = null;
                $data['collectOtherLocation'] = true;
                $this->addFlash('assetStorageIsFull', $storageEntity->getName());
            } else {
                // TODO Is there a more programmatic way of doing this?
                $this->addFlash('assetCollected', [$storageEntity->getName(), $nextOpenSlot]);
            }

            $this->assetCollectionService->checkIn($data, $this->getUser()->getId());

            return $this->redirect($request->headers->get('referer'));
        }

        return $this->render('asset_collection/collectionForm.html.twig', [
            'collectionForm' => $collectionForm->createView(),
        ]);
    }
}
