<?php

namespace MyParcelCom\ApiSdk\Resources\Proxy;

use MyParcelCom\ApiSdk\Resources\Interfaces\RegionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceProxyInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;
use MyParcelCom\ApiSdk\Resources\Traits\ProxiesResource;

class RegionProxy implements RegionInterface, ResourceProxyInterface
{
    use JsonSerializable;
    use ProxiesResource;

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_REGION;

    /**
     * Set the identifier for this file.
     *
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $countryCode
     * @return $this
     */
    public function setCountryCode($countryCode)
    {
        $this->getResource()->setCountryCode($countryCode);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountryCode()
    {
        return $this->getResource()->getCountryCode();
    }

    /**
     * @param string $regionCode
     * @return $this
     */
    public function setRegionCode($regionCode)
    {
        $this->getResource()->setRegionCode($regionCode);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegionCode()
    {
        return $this->getResource()->getRegionCode();
    }

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->getResource()->setCurrency($currency);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCurrency()
    {
        return $this->getResource()->getCurrency();
    }

    /**
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->getResource()->setName($name);

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getResource()->getName();
    }

    /**
     * @param string $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->getResource()->setCategory($category);

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->getResource()->getCategory();
    }

    /**
     * @param RegionInterface $parentRegion
     * @return $this
     */
    public function setParent(RegionInterface $parentRegion)
    {
        $this->getResource()->setParent($parentRegion);

        return $this;
    }

    /**
     * @return RegionInterface|null
     */
    public function getParent()
    {
        return $this->getResource()->getParent();
    }

    /**
     * This function puts all object properties in an array and returns it.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $values = get_object_vars($this);
        unset($values['resource']);
        unset($values['api']);
        unset($values['uri']);

        return $this->arrayValuesToArray($values);
    }
}
