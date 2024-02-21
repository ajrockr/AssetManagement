<?php

namespace App\Tests\Service;

use App\Repository\AssetCollectionRepository;
use App\Service\AssetCollectionService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class AssetCollectionServiceTest extends KernelTestCase
{

    public function testAssetIsCollected(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $assetCollectionService = $container->get(AssetCollectionService::class);
        $assetCollectionRepository = $container->get(AssetCollectionRepository::class);

        $checkInTest = $assetCollectionService->checkIn([
            'asset_tag' => '',
            'serial_number' => '',
            'condition' => '',
            'make' => '',
            'model' => '',
            'assigned_to' => '',
            'decommissioned' => false,
            'purchased_date' => '',
            'purchased_from' => '',
            'warranty_start_date' => '',
            'warranty_end_date' => '',
            'location' => '',
            'notes' => '',
            'needs_repair' => false,
            'repairPartsNeeded' => [],
            'check_out' => false,
            'processed' => false,
            'storageId' => 1,
        ], 1);
    }

    public function testCreateOrUpdateAsset()
    {

    }

    public function testCheckIn()
    {

    }
}
