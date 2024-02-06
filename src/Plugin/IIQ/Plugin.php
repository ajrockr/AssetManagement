<?php

namespace App\Plugin\IIQ;

use App\Plugin\ApiRequest;
use App\Plugin\IIQ\Entity\Part;
use App\Plugin\IIQ\Entity\User;
use App\Plugin\IIQ\Entity\Asset;
use App\Plugin\IIQ\Entity\Model;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\Yaml\Yaml;
use App\Plugin\IIQ\Entity\Supplier;
use App\Plugin\IIQ\Entity\Manufacturer;
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
        bool $searchOnlineSystemsOnly = false): ?array
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
            $responses = $request->getResponse();
            $assets = [];

            foreach($responses as $response) {
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

                if (isset($response['OwnerId'])) {
                    $asset->setOwnerId($response['OwnerId']);
                } elseif (isset($response['PreviousOwnerId'])) {
                    $asset->setPreviousOwnerId($response['PreviousOwnerId']);
                }

                $assets[] = $asset;
            }

            return $assets;
        }

        return null;
    }

    public function getModels(): ?array
    {
        $request = $this->setRequestUrl('/assets/models/all/sites')
            ->sendRequest();

        if ($request) {
            $responses = $request->getResponse();
            $models = [];
            foreach ($responses as $response) {
                $model = new Model;
                $model->setManufacturer($response['Manufacturer']['Name'])
                    ->setModelId($response['ModelId'])
                    ->setName($response['ModelName'])
                    ->setType($response['AssetTypeName'])
                    ->setTypeId($response['AssetTypeId'])
                    ->setManufacturerId($response['ManufacturerId'])
                    ->setCategoryId($response['CategoryId'])
                    ->setCategoryName($response['Category']['Name'])
                ;
                $models[] = $model;
            }

            return $models;
        }

        return null;
    }

    public function getManufacturers(): ?array
    {
        $request = $this->setRequestUrl('/assets/manufacturers')
            ->sendRequest();

        if ($request) {
            $responses = $request->getResponse();
            $manufacturers = [];

            foreach ($responses as $response) {
                $manufacturer = new Manufacturer;
                $manufacturer->setManufacturerId($response['ManufacturerId'])
                    ->setName($response['Name'])
                    ->setScope($response['Scope'])
                ;

                $manufacturers[] = $manufacturer;
            }

            return $manufacturers;
        }

        return null;
    }
    public function generateAssetTag(): ?string
    {
        $request = $this->setRequestUrl('/assets/generate-asset-tag')
            ->sendRequest();
        return ($request) ? $request->getResponse()['AssetTag'] : null;
    }

    public function assetsChanged(): ?array
    {
        $request = $this->setRequestUrl('/assets/changed')
            ->sendRequest();
        return ($request) ? $request->getResponse() : null;
    }

    public function getParts(): ?array
    {
        $request = $this->setRequestUrl('parts')
            ->sendRequest();

        if ($request) {
            $responses = $request->getResponse();
            $parts = [];

            foreach ($responses as $response) {
                $part = new Part;
                $part->setPartId($response['PartId'])
                    ->setName($response['Name'])
                    ->setProductId($response['ProductId'])
                    ->setPrice($response['StandardCostEach'])
                    ->setQuanityOnHand($response['QuantityOnHand'])
                    ->setStandardSupplierId($response['StandardSupplierId'])
                ;

                if (isset($response['StandardSupplier']['Name'])) {
                    $part->setSupplierName($response['StandardSupplier']['Name']);
                }

                $parts[] = $part;
            }

            return $parts;
        }

        return null;
    }

    public function getSuppliers(): ?array
    {
        $request = $this->setRequestUrl('parts/suppliers')
            ->sendRequest();

        if ($request) {
            $responses = $request->getResponse();
            $suppliers = [];

            foreach($responses as $response) {
                $supplier = new Supplier;
                $supplier->setName($response['Name'])
                    ->setPartSupplierId($response['PartSupplierId'])
                ;

                $suppliers[] = $supplier;
            }

            return $suppliers;
        }

        return null;
    }

    public function getUsers(): ?array
    {
        $request = $this->setRequestUrl('/users')
            ->setRequestBodyAsJson([
                'Paging' => [
                    'PageSize' => 50,
                ],
            ])
            ->sendRequest();

        if ($request) {
            $responses = $request->getResponse();
            $users = [];

            foreach ($responses as $response) {
                $user = new User;
                $user->setUserId($response['UserId'])
                    ->setIsDeleted($response['IsDeleted'])
                    ->setCreatedDate($response['CreatedDate'])
                    ->setModifiedDate($response['ModifiedDate'])
                    ->setLocationName($response['LocationName'])
                    ->setName($response['Name'])
                    ->setFirstName($response['FirstName'])
                    ->setLastName($response['LastName'])
                    ->setEmail($response['Email'])
                    ->setUsername($response['Username'])
                    ->setRoleId($response['RoleId'])
                    ->setRole($response['Role']['Name'])
                    ->setIsEmailVerified($response['IsEmailVerified'])
                ;

                if (isset($response['SchoolIdNumber'])) {
                    $user->setSchoolIdNumber($response['SchoolIdNumber']);
                }

                $users[] = $user;
            }

            return $users;
        }

        return null;
    }

    public function test(): ?array
    {
        $currentMaxExecutionTime = ini_get('max_execution_time');
        $currentMemoryLimit = ini_get('memory_limit');
        ini_set('max_execution_time', $currentMaxExecutionTime * 2);
        ini_set('memory_limit', '2048M');

        $request = $this->setRequestUrl('assets/of/2a1561e5-34ff-4fcf-87de-2a146f0e1c01?$s=10000&$o=SerialNumber&$d=Ascending')
            ->setRequestMethod(parent::HTTP_METHOD_POST)
            ->sendRequest();

        ini_set('max_execution_time', $currentMaxExecutionTime);
        ini_set('memory_limit', $currentMemoryLimit);

        dd($request->getExecutionTime());
        return ($request) ? $request->getResponse() : null;
    }
}


