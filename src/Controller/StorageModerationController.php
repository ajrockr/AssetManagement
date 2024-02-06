<?php

namespace App\Controller;

use App\Entity\StorageLock;
use App\Repository\StorageLockRepository;
use App\Repository\AssetStorageRepository;
use App\Repository\AssetCollectionRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StorageModerationController extends AbstractController
{
    public function __construct(
        private readonly StorageLockRepository $storageLockRepository
    ) {}

    #[Route('/storage/moderation', name: 'app_storage_moderation')]
    public function index() {}

    #[Route('/asset/storage/moderation/clear/{id}', name: 'app_storage_moderation_clear')]
    public function clearStorage(Request $request, AssetStorageRepository $assetStorageRepository, AssetCollectionRepository $assetCollectionRepository, int $id): Response
    {
        $locations = $assetStorageRepository->getStorageData($id);
        $assetCollectionRepository->removeCollection($locations);

        return $this->redirectToRoute('app_asset_storage_show', [
            'id' => $id
        ]);
    }

    public function clearLocation(Request $request, AssetCollectionRepository $assetCollectionRepository, $location): Response
    {
        $assetCollectionRepository->removeCollection($location);
        return $this->redirect($request->headers->get('referer'));
    }

    public function renderModerationButton(int $id): Response
    {
        $isLocked = $this->isLocked($id);

        $lockUrl = $this->generateUrl('app_storage_moderation_lock', ['id' => $id]);
        $unlockUrl = $this->generateUrl('app_storage_moderation_unlock', ['id' => $id]);
        $clearUrl = $this->generateUrl('app_storage_moderation_clear', ['id' => $id]);

        $html = '<div class="dropdown">
                    <button name="moderationMenu" type="button" class="btn btn-secondary dropdown-toggle" id="moderationMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Moderation
                    </button>
                    <div class="dropdown-menu" aria-labelledby="moderationMenuButton">';

        // Lock/unlock storage
        if ($isLocked) {
            $html .= '<a href="' . $unlockUrl . '" class="dropdown-item"><i class="fa fa-lock-open mr-2"></i>Unlock Storage</a>';
        } else {
            $html .= '<a href="' . $lockUrl . '" class="dropdown-item"><i class="fa fa-lock mr-2"></i>Lock Storage</a>';
        }

        $html .= '<a href="' . $clearUrl . '" class="dropdown-item' . ($this->isLocked($id) ? " disabled" : ""). '"><i class="fa fa-toilet-paper-slash mr-2"></i>Clear Collected Items</a>';

        $html .= '</div>
                </div>';

        return new Response($html);
    }

    /**
     * Check if a lock exists on a storage
     *
     * @param int $id
     * @return bool
     */
    public function isLocked(int $id): bool
    {
        return (bool)$this->storageLockRepository->findOneBy(['storageId' => $id]);
    }

    /**
     * Set a lock on the storage
     *
     * @param int $id
     * @return void
     */
    #[Route('/asset/storage/moderation/lock/{id}', name: 'app_storage_moderation_lock')]
     public function lockStorage(Request $request, int $id): Response
    {
        if (!$this->storageLockRepository->findOneBy(['storageId' => $id])) {
            $lock = new StorageLock();
            $lock->setLocked(true);
            $lock->setStorageId($id);
            $this->storageLockRepository->save($lock, true);
        }

        return $this->redirectToRoute('app_asset_storage_show', [
            'id' => $id
        ]);
    }

    /**
     * Remove a lock on a storage
     *
     * @param int $id
     * @return void
     */
    #[Route('/asset/storage/moderation/unlock/{id}', name: 'app_storage_moderation_unlock')]
    public function unlockStorage(Request $request, int $id): Response
    {
        if ($lock = $this->storageLockRepository->findOneBy(['storageId' => $id])) {
            $this->storageLockRepository->remove($lock, true);
        }

        return $this->redirectToRoute('app_asset_storage_show', [
            'id' => $id
        ]);
    }
}
