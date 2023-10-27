<?php

namespace App\Controller;

use App\Repository\AssetRepository;
use Symfony\UX\Chartjs\Model\Chart;
use App\Repository\AssetStorageRepository;
use App\Repository\AssetCollectionRepository;
use App\Repository\RepairRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * HomeController
 */
class HomeController extends AbstractController
{
    private array $colorScheme = [
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

    /**
     * __construct
     *
     * @return void
     */
    public function __construct(
        private readonly AssetRepository $assetRepository,
        private readonly AssetStorageRepository $assetStorageRepository,
        private readonly AssetCollectionRepository $assetCollectionRepository,
        private readonly RepairRepository $repairRepository,
        private readonly ChartBuilderInterface $chartBuilder,
    )
    {
        // Shuffle the colors for charts
        shuffle($this->colorScheme);
    }

    /**
     * index
     *
     * @param  mixed $chartBuilder
     * @return Response
     */
    #[Route('/', name: 'app_home')]
    public function index(ChartBuilderInterface $chartBuilder): Response
    {
        return $this->render('home/index.html.twig', [
            'assetsTotalChart' => $this->generateAssetTotalChart(),
            'storageSizeChart' => $this->generateStorageSizesChart(),
            'repairsPerMonthChart' => $this->generateReparirsPerMonthChart(),
        ]);
    }

    /**
     * generateAssetTotalChart
     *
     * @return Chart
     */
    private function generateAssetTotalChart(): Chart
    {
        $totalAssetsCount = $this->getTotalAssetsCount();
        $totalCollectedAssetsCount = $this->getCollectedAssetsCount();
        $totalDecommissionedAssetsCount = $this->getDecommissionedAssetsCount();

        $assetsTotalChart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $assetsTotalChart->setData([
            'labels' => ['Total Assets', 'Assets Collected', 'Assets Decommissioned'],
            'datasets' => [
                [
                    'label' => 'Asset Counts',
                    'data' => [$totalAssetsCount, $totalCollectedAssetsCount, $totalDecommissionedAssetsCount],
                    'backgroundColor' => $this->colorScheme,
                    'borderColor' => $this->colorScheme
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

        return $assetsTotalChart;
    }

    /**
     * generateStorageSizesChart
     *
     * @return Chart
     */
    private function generateStorageSizesChart(): Chart
    {
        $storageSizes = $this->getStorageSizes();
        $storageSizeChart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $storageSizeChart->setData([
            'labels' => array_keys($storageSizes),
            'datasets' => [
                [
                    'label' => 'Storage Sizes',
                    'data' => array_values($storageSizes),
                    'backgroundColor' => $this->colorScheme,
                    'borderColor' => $this->colorScheme,
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

        return $storageSizeChart;
    }

    /**
     * generateReparirsPerMonthChart
     *
     * @return Chart
     */
    private function generateReparirsPerMonthChart(): Chart
    {
        $repairsPerMonth = $this->getRepairsPerMonth();
        $repairsPerMonthChart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $repairsPerMonthChart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            'datasets' => [
                [
                    'backgroundColor' => $this->colorScheme,
                    'borderColor' => $this->colorScheme,
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

        return $repairsPerMonthChart;
    }

    /**
     * getStorageCount
     *
     * @return int
     */
    private function getStorageCount(): int
    {
        return count($this->assetStorageRepository->findAll());
    }

    /**
     * getRepairsPerMonth
     *
     * @return array
     */
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

    /**
     * getStorageSizes
     *
     * @return array
     */
    private function getStorageSizes(): array
    {
        $storages = $this->assetStorageRepository->findAll();
        $returnArray = [];
        foreach ($storages as $storage) {
            $returnArray[$storage->getName()] = sizeof($storage->getStorageData(), 1);
        }

        return $returnArray;
    }

    /**
     * getTotalAssetsCount
     *
     * @return int
     */
    private function getTotalAssetsCount(): int
    {
        return count($this->assetRepository->findAll());
    }

    /**
     * getCollectedAssetsCount
     *
     * @return int
     */
    private function getCollectedAssetsCount(): int
    {
        return count($this->assetCollectionRepository->findAll());
    }

    /**
     * getDecommissionedAssetsCount
     *
     * @return int
     */
    private function getDecommissionedAssetsCount(): int
    {
        return count($this->assetRepository->findBy(['decomisioned' => true]));
    }
}
