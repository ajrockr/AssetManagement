<?php

namespace App\Controller;

use App\Repository\AssetCollectionRepository;
use App\Repository\AssetRepository;
use App\Repository\AssetStorageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use function Doctrine\ORM\{QueryBuilder};

class SearchController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AssetCollectionRepository $assetCollectionRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly AssetRepository $assetRepository,
        private readonly AssetStorageRepository $assetStorageRepository
    ) {}

    /**
     * @throws Exception
     */
    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchQuery = $request->query->get('query');

        $searchResults = array_merge($this->searchForCollectedAsset($searchQuery),
            $this->searchForUser($searchQuery));

        return $this->render('search/results.html.twig', [
            'searchResults' => $searchResults
        ]);
    }

    /**
     * @throws Exception
     */
    private function searchForAsset(string $query): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $results = $queryBuilder->select('a')
            ->from('App\Entity\Asset', 'a')
            ->where($queryBuilder->expr()->like('a.assettag', ':query'))
            ->orWhere($queryBuilder->expr()->like('a.serialnumber', ':query'))
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        $returnArray = [];
        $serializer = new Serializer([new ObjectNormalizer()]);
        foreach ($results as $result) {
            if (null !== $result->getAssignedTo()) {
                $user = $this->userRepository->findOneBy(['id' => $result->getAssignedTo()]);
                $userInfo = [
                    'assignedToUsername' => $user->getUsername(),
                    'assignedToUserFirstname' => $user->getFirstname(),
                    'assignedToUserSurname' => $user->getSurname()
                ];
            } else {
                $userInfo = [];
            }

            try {
                $returnArray[] = array_merge($serializer->normalize($result), $userInfo);
            } catch (ExceptionInterface $e) {
                throw new Exception($e->getMessage());
            }
        }

        return $returnArray;
    }

    /**
     * @throws Exception
     */
    private function searchForUser(mixed $query): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $results = $queryBuilder->select('u.id', 'u.username', 'u.email', 'u.firstname', 'u.surname', 'u.userUniqueId', 'u.roles', 'u.title', 'u.department')
            ->from('App\Entity\User', 'u')
            ->where('u.id = :query')
            ->orWhere($queryBuilder->expr()->like('u.userUniqueId', ':query'))
            ->orWhere($queryBuilder->expr()->like('u.username', ':query'))
            ->orWhere($queryBuilder->expr()->like('u.roles', ':query'))
            ->orWhere($queryBuilder->expr()->like('u.department', ':query'))
            ->orWhere($queryBuilder->expr()->like('u.title', ':query'))
            ->orWhere($queryBuilder->expr()->like('u.firstname', ':query'))
            ->orWhere($queryBuilder->expr()->like('u.surname', ':query'))
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        $serializer = new Serializer([new ObjectNormalizer()]);

        $returnArray = [];
        foreach($results as $result) {
            $collectedAsset = $this->assetCollectionRepository->findOneBy(['CollectedFrom' => $result['id']]);
            $asset = (!$collectedAsset) ?: $this->assetRepository->findOneBy(['id' => $collectedAsset->getDeviceID()]);

            if (null !== $collectedAsset) {
                if ($data = $this->assetStorageRepository->storageDataExists($collectedAsset->getCollectionLocation())) {
                    $url = $this->generateUrl('app_asset_storage_show', [
                        'id' => $data['id'],
                        'slot' => $collectedAsset->getCollectionLocation()
                    ]);
                    $location = '<a href="'. $url . '">' . $collectedAsset->getCollectionLocation() . '</a>';
                } else {
                    $location = $collectedAsset->getCollectionLocation();
                }

                $assetInfo = [
                    'asset collected' => true,
//                    'location' => $collectedAsset->getCollectionLocation(),
                    'location' => $location,
                    'assettag' => $asset->getAssettag(),
                    'serialnumber' => $asset->getSerialnumber()
                ];
            } else {
                $assetInfo = [];
            }

            try {
                $returnArray[] = array_merge($serializer->normalize($result), $assetInfo);
            } catch (ExceptionInterface $e) {
                throw new Exception($e->getMessage());
            }
        }

        return $returnArray;
    }

    /**
     * @throws Exception
     */
    private function searchForCollectedAsset(string $query): array
    {
        $assetSearch = $this->searchForAsset($query);
        for ($i=0;$i<count($assetSearch);$i++) {
            $assetCollected = $this->assetCollectionRepository->findOneBy(['DeviceID' => $assetSearch[$i]['id']]);
            if (null !== $assetCollected) {
                $assetSearch[$i]['collectedStorageSlot'] = $assetCollected->getCollectionLocation();
            }
        }

        return $assetSearch;
    }
}
