<?php

namespace App\Controller;

use App\Entity\StorageLock;
use App\Repository\StorageLockRepository;
use App\Repository\AssetStorageRepository;
use App\Repository\AssetCollectionRepository;
use App\Service\LoggerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StorageModerationController extends AbstractController
{
    public function __construct(
        private readonly StorageLockRepository $storageLockRepository,
        private readonly LoggerService         $logger,
    ) {}

    #[Route('/asset/storage/moderation/clear/{id}', name: 'app_storage_moderation_clear')]
    public function clearStorage(Request $request, AssetStorageRepository $assetStorageRepository, AssetCollectionRepository $assetCollectionRepository, int $id): Response
    {
        $locations = $assetStorageRepository->getStorageData($id);
        $assetCollectionRepository->removeCollection($locations);

        $this->addFlash('info', 'Cleared storage of collected assets.');
        $this->logger->adminAction($this->getUser()->getId(), $request->headers->get('referer'), 'clear_storage', "$id");

        return $this->redirectToRoute('app_asset_storage_show', [
            'id' => $id
        ]);
    }

    /**
     * Clears a location entity in given storage(s)
     *
     * @param Request $request
     * @param AssetCollectionRepository $assetCollectionRepository
     * @param int|array $locations
     * @return Response
     */
    public function clearLocation(Request $request, AssetCollectionRepository $assetCollectionRepository, int|array $locations): Response
    {
        $assetCollectionRepository->removeCollection($locations);

        $referer = $request->headers->get('referer');
        $target = is_int($locations) ? (string)$locations : implode(',', $locations);

        $this->addFlash('info', 'Cleared location(s) of collected assets.');
        $this->logger->adminAction($this->getUser()->getId(), $referer, 'clear_location', $target);

        return $this->redirect($referer);
    }

    /**
     * Renders the moderation menu in Twig
     *
     * @param int $id
     * @return Response
     */
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
     * @param Request $request
     * @param int $id
     * @return Response
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

        $this->addFlash('info', 'Locked storage, editing is disabled.');
        $this->logger->adminAction($this->getUser()->getId(), $request->headers->get('referer'), 'lock_storage', "$id");

        return $this->redirectToRoute('app_asset_storage_show', [
            'id' => $id
        ]);
    }

    /**
     * Remove a lock on a storage
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    #[Route('/asset/storage/moderation/unlock/{id}', name: 'app_storage_moderation_unlock')]
    public function unlockStorage(Request $request, int $id): Response
    {
        if ($lock = $this->storageLockRepository->findOneBy(['storageId' => $id])) {
            $this->storageLockRepository->remove($lock, true);
        }

        $this->addFlash('info', 'Unlocked storage, editing is enabled again.');
        $this->logger->adminAction($this->getUser()->getId(), $request->headers->get('referer'), 'unlock_storage', "$id");

        return $this->redirectToRoute('app_asset_storage_show', [
            'id' => $id
        ]);
    }
}
