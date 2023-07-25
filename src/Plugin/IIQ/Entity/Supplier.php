<?php

namespace App\Plugin\IIQ\Entity;

class Supplier
{
    private ?string $partSupplierId = null;

    private ?string $name = null;

    /**
     * Get the value of partSupplierId
     */ 
    public function getPartSupplierId()
    {
        return $this->partSupplierId;
    }

    /**
     * Set the value of partSupplierId
     *
     * @return  self
     */ 
    public function setPartSupplierId($partSupplierId)
    {
        $this->partSupplierId = $partSupplierId;

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
}