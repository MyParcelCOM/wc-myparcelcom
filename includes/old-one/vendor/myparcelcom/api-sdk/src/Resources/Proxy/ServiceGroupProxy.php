<?php

namespace MyParcelCom\ApiSdk\Resources\Proxy;

use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceProxyInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceGroupInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;
use MyParcelCom\ApiSdk\Resources\Traits\ProxiesResource;

class ServiceGroupProxy implements ServiceGroupInterface, ResourceProxyInterface
{
    use JsonSerializable;
    use ProxiesResource;

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_SERVICE_GROUP;

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
     * @param int $min
     * @return $this
     */
    public function setWeightMin($min)
    {
        $this->getResource()->setWeightMin($min);

        return $this;
    }

    /**
     * @return int
     */
    public function getWeightMin()
    {
        return $this->getResource()->getWeightMin();
    }

    /**
     * @param int $max
     * @return $this
     */
    public function setWeightMax($max)
    {
        $this->getResource()->setWeightMax($max);

        return $this;
    }

    /**
     * @return int
     */
    public function getWeightMax()
    {
        return $this->getResource()->getWeightMax();
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
     * @return string
     */
    public function getCurrency()
    {
        return $this->getResource()->getCurrency();
    }

    /**
     * @param int $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->getResource()->setPrice($price);

        return $this;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->getResource()->getPrice();
    }

    /**
     * @param int $size
     * @return $this
     */
    public function setStepSize($size)
    {
        $this->getResource()->setStepSize($size);

        return $this;
    }

    /**
     * @return int
     */
    public function getStepSize()
    {
        return $this->getResource()->getStepSize();
    }

    /**
     * @param int $price
     * @return $this
     */
    public function setStepPrice($price)
    {
        $this->getResource()->setStepPrice($price);

        return $this;
    }

    /**
     * @return int
     */
    public function getStepPrice()
    {
        return $this->getResource()->getStepPrice();
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
