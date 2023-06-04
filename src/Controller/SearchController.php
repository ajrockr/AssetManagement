<?php

namespace App\Controller;

use App\Repository\AssetCollectionRepository;
use App\Repository\AssetRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use function Doctrine\ORM\QueryBuilder;

class SearchController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AssetRepository $assetRepository,
        private readonly AssetCollectionRepository $assetCollectionRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}
    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $searchQuery = $request->query->get('query');
        dd($this->searchForAsset($searchQuery));

        return $this->render('search/results.html.twig');
    }

    private function searchForAsset(string $query)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $results = $queryBuilder->select('a')
            ->from('App\Entity\Asset', 'a')
            ->where($queryBuilder->expr()->like('a.assettag', ':assettag'))
            ->orWhere($queryBuilder->expr()->like('a.serialnumber', ':serialnumber'))
            ->setParameter('assettag', $query)
            ->setParameter('serialnumber', $query)
            ->getQuery()
            ->getResult();

        $returnArray = [];
        $userInfo = [];
        $serializer = new Serializer([new ObjectNormalizer()]);
        foreach ($results as $result) {
            if (null !== $result->getAssignedTo()) {
                $user = $this->userRepository->findOneBy(['id' => $result->getAssignedTo()]);
                $userInfo = [
                    'assignedUsername' => $user->getUsername(),
                    'assignedUserFirstname' => $user->getFirstname(),
                    'assignedUserSurname' => $user->getSurname()
                ];
            }

            $returnArray = $serializer->normalize($result);
            $returnArray = array_merge($returnArray, $userInfo);

//            $returnArray[] = [
//                'serialnumber' => $result->getSerialnumber(),
//                'assettag' => $result->getAssettag(),
//                'purchasedate' => $result->getPurchasedate(),
//                'purchasedfrom' => $result->getPurchasedfrom(),
//                'warrantystartdate' => $result->getWarrantystartdate(),
//                'warrantyenddate' => $result->getWarrantyenddate()
//            ];
        }
            return $returnArray;
    }

    private function searchForUser(EntityManagerInterface $entityManager, mixed $query)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        return $queryBuilder->select('u')
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
    }
}
