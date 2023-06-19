<?php

namespace App\Controller;

use App\Entity\StorageLock;
use App\Repository\AssetCollectionRepository;
use App\Repository\AssetStorageRepository;
use App\Repository\StorageLockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StorageModerationController extends AbstractController
{
    public function __construct(
        private readonly AssetStorageRepository $assetStorageRepository,
        private readonly AssetCollectionRepository $assetCollectionRepository,
        private readonly StorageLockRepository $storageLockRepository
    ) {}

    #[Route('/storage/moderation', name: 'app_storage_moderation')]
    public function index() {}

    public function clearStorage() {}

    public function renderModerationButton(int $id): Response
    {
        $isLocked = $this->isLocked($id);

        $html = '<div class="dropdown">
                    <button type="button" class="btn btn-secondary dropdown-toggle" id="moderationMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Moderation
                    </button>
                    <div class="dropdown-menu" aria-labelledby="moderationMenuButton">';

        // Lock/unlock storage
        if ($isLocked) {
            $html .= '<a href="#" class="dropdown-item"><i class="fa fa-lock-open mr-2"></i>Unlock Storage</a>';
        } else {
            $html .= '<a href="#" class="dropdown-item"><i class="fa fa-lock mr-2"></i>Lock Storage</a>';
        }

        $html .= '<a href="#" class="dropdown-item"><i class="fa fa-toilet-paper-slash mr-2"></i>Clear Collected Items</a>';

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
    public function lockStorage(int $id): void
    {
        if (!$this->storageLockRepository->findOneBy(['storageId' => $id])) {
            $lock = new StorageLock();
            $lock->setLocked(true);
            $lock->setStorageId($id);
            $this->storageLockRepository->save($lock, true);
        }
    }

    /**
     * Remove a lock on a storage
     *
     * @param int $id
     * @return void
     */
    public function unlockStorage(int $id): void
    {
        if ($lock = $this->storageLockRepository->findOneBy(['storageId' => $id])) {
            $this->storageLockRepository->remove($lock, true);
        }
    }
}
