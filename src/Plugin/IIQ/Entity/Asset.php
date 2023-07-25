<?php

namespace App\Plugin\IIQ\Entity;
use App\Plugin\AssetInterface;

class Asset implements AssetInterface
{
    /**
     * @var string|null
     */
    private ?string $assetId = null;

    /**
     * @var string|null
     */
    private ?string $siteId = null;

    /**
     * @var string|null
     */
    private ?string $productId = null;

    /**
     * @var string|null
     */
    private ?string $createdDate = null;

    /**
     * @var string|null
     */
    private ?string $modifiedDate = null;

    /**
     * @var string|null
     */
    private ?string $assetTypeId = null;

    /**
     * @var string|null
     */
    private ?string $assetTypeName = null;

    /**
     * @var bool|null
     */
    private ?bool $isDeleted = null;

    /**
     * @var string|null
     */
    private ?string $statusTypeId = null;

    /**
     * @var string|null
     */
    private ?string $assetTag = null;

    /**
     * @var string|null
     */
    private ?string $serialNumber = null;

    /**
     * @var string|null
     */
    private ?string $externalId = null;

    /**
     * @var string|null
     */
    private ?string $modelName = null;

    /**
     * @var bool|null
     */
    private ?bool $canOwnerManage = null;

    /**
     * @var bool|null
     */
    private ?bool $canSubmitTicket = null;

    /**
     * @var bool|null
     */
    private ?bool $isFavorite = null;

    /**
     * @var string|null
     */
    private ?string $modelId = null;

    /**
     * @var string|null
     */
    private ?string $ownerId = null;

    /**
     * @var string|null
     */
    private ?string $locationId = null;

    /**
     * @var bool|null
     */
    private ?bool $hasOpenTickets = null;

    /**
     * @var int|null
     */
    private ? int $openTicketsCount = null;

    /**
     * @var bool|null
     */
    private ?bool $isReadOnly = null;

    /**
     * @var bool|null
     */
    private ?bool $isExternallyManaged = null;

    /**
     * @var int|null
     */
    private ?int $assetAuditPolicyStatusSortOrder = null;

    /**
     * @var bool|null
     */
    private ?bool $lastVerificationSuccessful = null;

    private ?string $previousOwnerId = null;

    /**
     * Get the value of assetId
     */ 
    public function getAssetId(): ?string
    {
        return $this->assetId;
    }

    /**
     * Set the value of assetId
     *
     * @return  self
     */ 
    public function setAssetId($assetId): self
    {
        $this->assetId = $assetId;

        return $this;
    }

    /**
     * Get the value of createdDate
     */ 
    public function getCreatedDate(): ?string
    {
        return $this->createdDate;
    }

    /**
     * Set the value of createdDate
     *
     * @return  self
     */ 
    public function setCreatedDate($createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * Get the value of siteId
     */ 
    public function getSiteId(): ?string
    {
        return $this->siteId;
    }

    /**
     * Set the value of siteId
     *
     * @return  self
     */ 
    public function setSiteId($siteId): self
    {
        $this->siteId = $siteId;

        return $this;
    }

    /**
     * Get the value of productId
     */ 
    public function getProductId(): ?string
    {
        return $this->productId;
    }

    /**
     * Set the value of productId
     *
     * @return  self
     */ 
    public function setProductId($productId): self
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Get the value of modifiedDate
     */ 
    public function getModifiedDate(): ?string
    {
        return $this->modifiedDate;
    }

    /**
     * Set the value of modifiedDate
     *
     * @return  self
     */ 
    public function setModifiedDate($modifiedDate): self
    {
        $this->modifiedDate = $modifiedDate;

        return $this;
    }

    /**
     * Get the value of assetTypeId
     */ 
    public function getAssetTypeId(): ?string
    {
        return $this->assetTypeId;
    }

    /**
     * Set the value of assetTypeId
     *
     * @return  self
     */ 
    public function setAssetTypeId($assetTypeId): self
    {
        $this->assetTypeId = $assetTypeId;

        return $this;
    }

    /**
     * Get the value of assetTypeName
     */ 
    public function getAssetTypeName(): ?string
    {
        return $this->assetTypeName;
    }

    /**
     * Set the value of assetTypeName
     *
     * @return  self
     */ 
    public function setAssetTypeName($assetTypeName): self
    {
        $this->assetTypeName = $assetTypeName;

        return $this;
    }

    /**
     * Get the value of isDeleted
     */ 
    public function isDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    /**
     * Set the value of isDeleted
     *
     * @return  self
     */ 
    public function setIsDeleted($isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get the value of statusTypeId
     */ 
    public function getStatusTypeId(): ?string
    {
        return $this->statusTypeId;
    }

    /**
     * Set the value of statusTypeId
     *
     * @return  self
     */ 
    public function setStatusTypeId($statusTypeId): self
    {
        $this->statusTypeId = $statusTypeId;

        return $this;
    }

    /**
     * Get the value of assetTag
     */ 
    public function getAssetTag(): ?string
    {
        return $this->assetTag;
    }

    /**
     * Set the value of assetTag
     *
     * @return  self
     */ 
    public function setAssetTag($assetTag): self
    {
        $this->assetTag = $assetTag;

        return $this;
    }

    /**
     * Get the value of serialNumber
     */ 
    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    /**
     * Set the value of serialNumber
     *
     * @return  self
     */ 
    public function setSerialNumber($serialNumber): self
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    /**
     * Get the value of externalId
     */ 
    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    /**
     * Set the value of externalId
     *
     * @return  self
     */ 
    public function setExternalId($externalId): self
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * Get the value of modelName
     */ 
    public function getModelName(): ?string
    {
        return $this->modelName;
    }

    /**
     * Set the value of modelName
     *
     * @return  self
     */ 
    public function setModelName($modelName): self
    {
        $this->modelName = $modelName;

        return $this;
    }

    /**
     * Get the value of canOwnerManage
     */ 
    public function canOwnerManage(): ?bool
    {
        return $this->canOwnerManage;
    }

    /**
     * Set the value of canOwnerManage
     *
     * @return  self
     */ 
    public function setCanOwnerManage($canOwnerManage): self
    {
        $this->canOwnerManage = $canOwnerManage;

        return $this;
    }

    /**
     * Get the value of canSubmitTicket
     */ 
    public function canSubmitTicket(): ?bool
    {
        return $this->canSubmitTicket;
    }

    /**
     * Set the value of canSubmitTicket
     *
     * @return  self
     */ 
    public function setCanSubmitTicket($canSubmitTicket): self
    {
        $this->canSubmitTicket = $canSubmitTicket;

        return $this;
    }

    /**
     * Get the value of isFavorite
     */ 
    public function isFavorite(): ?bool
    {
        return $this->isFavorite;
    }

    /**
     * Set the value of isFavorite
     *
     * @return  self
     */ 
    public function setIsFavorite($isFavorite): self
    {
        $this->isFavorite = $isFavorite;

        return $this;
    }

    /**
     * Get the value of modelId
     */ 
    public function getModelId(): ?string
    {
        return $this->modelId;
    }

    /**
     * Set the value of modelId
     *
     * @return  self
     */ 
    public function setModelId($modelId): self
    {
        $this->modelId = $modelId;

        return $this;
    }

    /**
     * Get the value of ownerId
     */ 
    public function getOwnerId(): ?string
    {
        return $this->ownerId;
    }

    /**
     * Set the value of ownerId
     *
     * @return  self
     */ 
    public function setOwnerId($ownerId): self
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    /**
     * Get the value of locationId
     */ 
    public function getLocationId(): ?string
    {
        return $this->locationId;
    }

    /**
     * Set the value of locationId
     *
     * @return  self
     */ 
    public function setLocationId($locationId): self
    {
        $this->locationId = $locationId;

        return $this;
    }

    /**
     * Get the value of hasOpenTickets
     */ 
    public function hasOpenTickets(): ?bool
    {
        return $this->hasOpenTickets;
    }

    /**
     * Set the value of hasOpenTickets
     *
     * @return  self
     */ 
    public function setHasOpenTickets($hasOpenTickets): self
    {
        $this->hasOpenTickets = $hasOpenTickets;

        return $this;
    }

    /**
     * Get the value of openTicketsCount
     */ 
    public function getOpenTicketsCount(): ?int
    {
        return $this->openTicketsCount;
    }

    /**
     * Set the value of openTicketsCount
     *
     * @return  self
     */ 
    public function setOpenTicketsCount($openTicketsCount): self
    {
        $this->openTicketsCount = $openTicketsCount;

        return $this;
    }

    /**
     * Get the value of isReadOnly
     */ 
    public function isReadOnly(): ?bool
    {
        return $this->isReadOnly;
    }

    /**
     * Set the value of isReadOnly
     *
     * @return  self
     */ 
    public function setIsReadOnly($isReadOnly): self
    {
        $this->isReadOnly = $isReadOnly;

        return $this;
    }

    /**
     * Get the value of isExternallyManaged
     */ 
    public function isExternallyManaged(): ?bool
    {
        return $this->isExternallyManaged;
    }

    /**
     * Set the value of isExternallyManaged
     *
     * @return  self
     */ 
    public function setIsExternallyManaged($isExternallyManaged): self
    {
        $this->isExternallyManaged = $isExternallyManaged;

        return $this;
    }

    /**
     * Get the value of assetAuditPolicyStatusSortOrder
     */ 
    public function getAssetAuditPolicyStatusSortOrder(): ?int
    {
        return $this->assetAuditPolicyStatusSortOrder;
    }

    /**
     * Set the value of assetAuditPolicyStatusSortOrder
     *
     * @return  self
     */ 
    public function setAssetAuditPolicyStatusSortOrder($assetAuditPolicyStatusSortOrder): self
    {
        $this->assetAuditPolicyStatusSortOrder = $assetAuditPolicyStatusSortOrder;

        return $this;
    }

    /**
     * Get the value of lastVerificationSuccessfull
     */ 
    public function getLastVerificationSuccessful(): ?bool
    {
        return $this->lastVerificationSuccessful;
    }

    /**
     * Set the value of lastVerificationSuccessfull
     *
     * @return  self
     */ 
    public function setLastVerificationSuccessful($lastVerificationSuccessful): self
    {
        $this->lastVerificationSuccessful = $lastVerificationSuccessful;

        return $this;
    }

    /**
     * Get the value of previousOwnerId
     */ 
    public function getPreviousOwnerId()
    {
        return $this->previousOwnerId;
    }

    /**
     * Set the value of previousOwnerId
     *
     * @return  self
     */ 
    public function setPreviousOwnerId($previousOwnerId)
    {
        $this->previousOwnerId = $previousOwnerId;

        return $this;
    }
}