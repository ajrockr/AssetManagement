<?php

namespace App\Plugin\IIQ\Entity;

class Manufacturer
{
    private ?string $manufacturerId = null;

    private ?string $name;

    private ?string $scope;

    /**
     * Get the value of manufacturerId
     */ 
    public function getManufacturerId()
    {
        return $this->manufacturerId;
    }

    /**
     * Set the value of manufacturerId
     *
     * @return  self
     */ 
    public function setManufacturerId($manufacturerId)
    {
        $this->manufacturerId = $manufacturerId;

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
     * Get the value of scope
     */ 
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set the value of scope
     *
     * @return  self
     */ 
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }
}