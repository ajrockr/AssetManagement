<?php

namespace App\Plugin\IIQ\Entity;

class Part
{
    private ?string $partId = null;

    private ?string $productId = null;

    private ?string $name = null;

    private ?int $price = null;

    private ?string $standardSupplierId = null;

    private ?int $quanityOnHand = null;

    private ?string $supplierName = null;

    /**
     * Get the value of partId
     */ 
    public function getPartId()
    {
        return $this->partId;
    }

    /**
     * Set the value of partId
     *
     * @return  self
     */ 
    public function setPartId($partId)
    {
        $this->partId = $partId;

        return $this;
    }

    /**
     * Get the value of productId
     */ 
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set the value of productId
     *
     * @return  self
     */ 
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of price
     */ 
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set the value of price
     *
     * @return  self
     */ 
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get the value of standardSupplierId
     */ 
    public function getStandardSupplierId()
    {
        return $this->standardSupplierId;
    }

    /**
     * Set the value of standardSupplierId
     *
     * @return  self
     */ 
    public function setStandardSupplierId($standardSupplierId)
    {
        $this->standardSupplierId = $standardSupplierId;

        return $this;
    }

    /**
     * Get the value of quanityOnHand
     */ 
    public function getQuanityOnHand()
    {
        return $this->quanityOnHand;
    }

    /**
     * Set the value of quanityOnHand
     *
     * @return  self
     */ 
    public function setQuanityOnHand($quanityOnHand)
    {
        $this->quanityOnHand = $quanityOnHand;

        return $this;
    }

    /**
     * Get the value of supplierName
     */ 
    public function getSupplierName()
    {
        return $this->supplierName;
    }

    /**
     * Set the value of supplierName
     *
     * @return  self
     */ 
    public function setSupplierName($supplierName)
    {
        $this->supplierName = $supplierName;

        return $this;
    }
}