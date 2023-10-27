<?php

namespace App\Controller;

use App\Plugin\IIQ\Plugin;
use App\Repository\AssetRepository;
use Symfony\UX\Chartjs\Model\Chart;
use App\Repository\AssetStorageRepository;
use App\Repository\AssetCollectionRepository;
use App\Repository\RepairRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly AssetRepository $assetRepository,
        private readonly AssetStorageRepository $assetStorageRepository,
        private readonly AssetCollectionRepository $assetCollectionRepository,
        private readonly RepairRepository $repairRepository,
        // private readonly Plugin $iiq
    )
    {
        // dd($this->iiq->getAssetById("ae9b69a2-2e23-442b-90dc-cb5fd0ddb43d"));
        // dd($this->iiq->getAssetByTag("MOBILE-1978LT2"));
        // dd($this->iiq->searchForAsset("1978LT2"));
        // dd($this->iiq->getParts());
        // dd($this->iiq->getSuppliers());
        // dd($this->iiq->getUsers());
        // dd($this->iiq->test());
    }
    #[Route('/', name: 'app_home')]
    public function index(ChartBuilderInterface $chartBuilder): Response
    {
        $colorScheme = [
            "#25CCF7","#FD7272","#54a0ff","#00d2d3",
            "#1abc9c","#2ecc71","#3498db","#9b59b6","#34495e",
            "#16a085","#27ae60","#2980b9","#8e44ad","#2c3e50",
            "#f1c40f","#e67e22","#e74c3c","#ecf0f1","#95a5a6",
            "#f39c12","#d35400","#c0392b","#bdc3c7","#7f8c8d",
            "#55efc4","#81ecec","#74b9ff","#a29bfe","#dfe6e9",
            "#00b894","#00cec9","#0984e3","#6c5ce7","#ffeaa7",
            "#fab1a0","#ff7675","#fd79a8","#fdcb6e","#e17055",
            "#d63031","#feca57","#5f27cd","#54a0ff","#01a3a4"
        ];

        shuffle($colorScheme);

        $totalAssetsCount = $this->getTotalAssetsCount();
        $totalCollectedAssetsCount = $this->getCollectedAssetsCount();
        $totalDecommissionedAssetsCount = $this->getDecommissionedAssetsCount();
        $repairsPerMonth = $this->getRepairsPerMonth();
        $storageSizes = $this->getStorageSizes();

        $assetsTotalChart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $assetsTotalChart->setData([
            'labels' => ['Total Assets', 'Assets Collected', 'Assets Decommissioned'],
            'datasets' => [
                [
                    'label' => 'Asset Counts',
                    'data' => [$totalAssetsCount, $totalCollectedAssetsCount, $totalDecommissionedAssetsCount],
                    'backgroundColor' => $colorScheme,
                    'borderColor' => $colorScheme
                ]
            ]
        ]);
        $assetsTotalChart->setOptions([
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top'
                ],
                'colors' => [
                    'enabled' => true
                ]
            ]
        ]);

        $storageSizeChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $storageSizeChart->setData([
            'labels' => array_keys($storageSizes),
            'datasets' => [
                [
                    'label' => 'Storage Sizes',
                    'data' => array_values($storageSizes),
                    'backgroundColor' => $colorScheme,
                    'borderColor' => $colorScheme,
                ]
            ]
        ]);

        $storageSizeChart->setOptions([
            'responsive' => true,
            'scales' => [
                'y' => [
                    'beginAtZero' => true
                ]
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top'
                ],
                'colors' => [
                    'enabled' => true
                ]
            ]
        ]);

        $repairsPerMonthChart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $repairsPerMonthChart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            'datasets' => [
                [
                    'backgroundColor' => $colorScheme,
                    'borderColor' => $colorScheme,
                    'label' => 'Repairs Per Month',
                    'data' => array_values($repairsPerMonth),
                ]
            ]
        ]);

        $repairsPerMonthChart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 50,
                ],
            ],
        ]);

        return $this->render('home/index2.html.twig', [
            'assetsTotalChart' => $assetsTotalChart,
            'storageSizeChart' => $storageSizeChart,
            'repairsPerMonthChart' => $repairsPerMonthChart,
        ]);
    }

    private function getStorageCount(): int
    {
        return count($this->assetStorageRepository->findAll());
    }

    private function getRepairsPerMonth(): array
    {
        $repairDates = $this->repairRepository->getCountAndCreatedDate();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Dec'];
        $months = array_flip($months);
        array_walk($months, function(&$value){$value=0;});

        foreach ($repairDates as $repairDate) {
            if (array_key_exists($month = $repairDate['createdDate']->format('M'), $months)) {
                $months[$month]++;
            }
        }

        return $months;
    }

    private function getStorageSizes(): array
    {
        $storages = $this->assetStorageRepository->findAll();
        $returnArray = [];
        foreach ($storages as $storage) {
            $returnArray[$storage->getName()] = sizeof($storage->getStorageData(), 1);
        }

        return $returnArray;
    }

    private function getTotalAssetsCount(): int
    {
        return count($this->assetRepository->findAll());
    }

    private function getCollectedAssetsCount(): int
    {
        return count($this->assetCollectionRepository->findAll());
    }

    private function getDecommissionedAssetsCount(): int
    {
        return count($this->assetRepository->findBy(['decomisioned' => true]));
    }
}
