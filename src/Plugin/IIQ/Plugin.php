<?php

namespace App\Plugin\IIQ;

use App\Plugin\IIQ\Asset;
use App\Plugin\ApiRequest;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Plugin extends ApiRequest
{
    private array $config = [];

    public function __construct(
        private readonly ContainerBagInterface $params,
        private readonly HttpClientInterface $client
    )
    {
        $this->config = $this->getConfig();
        parent::__construct($this->client, $this->config);
    }
    private function getConfig(): array
    {
        // TODO: Either put this in _ENV or sql db
        $config = Yaml::parseFile($this->params->get('kernel.project_dir') . '/config/plugins/iiq.yaml');
        if (!array_key_exists('plugin', $config)) {
            throw new InvalidConfigurationException('API configuration is invalid.');
        }

        if (!array_key_exists('api_url', $config['plugin'])) {
            throw new InvalidConfigurationException('API configuration is invalid.');
        }

        return $config;
    }

    public function getAssetStatusTypes(): ?array
    {
        $request = $this->setRequestUrl('/assets/status/types')
            ->sendRequest();

        return ($request) ? $request->getResponse() : null;
    }

    public function getAssetActivity(string $assetId): ?array
    {
        $request = $this->setRequestUrl('/assets/' . $assetId . '/activities')
            ->sendRequest();
        return ($request) ? $request->getResponse() : null;
    }

    public function getAssetById(string $assetId): ?array
    {
        $request = $this->setRequestUrl('/assets/' . $assetId)
            ->sendRequest();
        return ($request) ? $request->getResponse() : null;
    }

    public function getAssetByTag(string $assetTag): ?array
    {
        $request = $this->setRequestUrl('/assets/assettag/' . $assetTag)
            ->sendRequest();
            return ($request) ? $request->getResponse() : null;
    }

    public function searchForAsset(
        string $query,
        bool $uniqueByName = false,
        bool $skipCustomFieldLoading = true,
        bool $searchManufacturerName = false,
        bool $searchModelName = false,
        bool $searchAssetTag = true,
        bool $searchSerial = true,
        bool $searchRoom = false,
        bool $searchOwner = false,
        string $searchRoomLocationId = '',
        bool $searchOnlineSystemsOnly = false): ?Asset
    {
        $request = $this->setRequestUrl('/assets/search')
            ->setRequestMethod(parent::HTTP_METHOD_POST)
            ->setRequestBodyAsJson([
                'Query' => $query,
                'UniqueByName' => $uniqueByName,
                'SkipCustomFieldLoading' => $skipCustomFieldLoading,
                'SearchManufacturerName' => $searchManufacturerName,
                'SearchModelName' => $searchModelName,
                'SearchAssetTag' => $searchAssetTag,
                'SearchSerial' => $searchSerial,
                'SearchRoom' => $searchRoom,
                'SearchOwner' => $searchOwner,
                'SearchRoomLocationId' => $searchRoomLocationId,
                'SearchOnlineSystemsOnly' => $searchOnlineSystemsOnly
            ])
            ->sendRequest();

        if ($request) {
            $response = $request->getResponse()[0];
            
            $asset = new Asset;
            $asset->setAssetId($response['AssetId'])
                ->setAssetTag($response['AssetTag'])
                ->setSerialNumber($response['SerialNumber'])
                ->setModelName($response['Name'])
                ->setSiteId($response['SiteId'])
                ->setCreatedDate($response['CreatedDate'])
                ->setModifiedDate($response['ModifiedDate'])
                ->setAssetTypeId($response['AssetTypeId'])
                ->setIsDeleted($response['IsDeleted'])
                ->setOwnerId($response['OwnerId'])
                ->setLocationId($response['LocationId'])
                ->setHasOpenTickets($response['HasOpenTickets'])
                ->setOpenTicketsCount($response['OpenTickets'])
                ->setLastVerificationSuccessful($response['LastVerificationSuccessful'])
                ->setProductId($response['ProductId'])
                ->setAssetTypeName($response['AssetTypeName'])
                ->setAssetTypeId($response['AssetTypeId'])
                ->setStatusTypeId($response['StatusTypeId'])
                ->setExternalId($response['ExternalId'])
                ->setCanOwnerManage($response['CanOwnerManage'])
                ->setCanSubmitTicket($response['CanSubmitTicket'])
                ->setIsFavorite($response['IsFavorite'])
                ->setModelId($response['ModelId'])
                ->setIsReadOnly($response['IsReadOnly'])
                ->setIsExternallyManaged($response['IsExternallyManaged'])
                ->setAssetAuditPolicyStatusSortOrder($response['AssetAuditPolicyStatusSortOrder'])
            ;

            return $asset;
        }

        return null;
    }

    public function test(): ?array
    {
        $request = $this->setRequestUrl('/categories/of/issues')
            ->sendRequest();
        return ($request) ? $request->getResponse() : null;
    }
}


    