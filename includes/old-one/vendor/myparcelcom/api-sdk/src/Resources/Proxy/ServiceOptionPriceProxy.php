<?php

namespace MyParcelCom\ApiSdk\Resources\Proxy;

use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceProxyInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionPriceInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;
use MyParcelCom\ApiSdk\Resources\Traits\ProxiesResource;

class ServiceOptionPriceProxy implements ServiceOptionPriceInterface, ResourceProxyInterface
{
    use JsonSerializable;
    use ProxiesResource;

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_SERVICE_OPTION_PRICE;

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
     * @param bool $required
     * @return $this
     */
    public function setRequired($required)
    {
        $this->getResource()->setRequired($required);

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->getResource()->isRequired();
    }

    /**
     * @param ServiceContractInterface $serviceContract
     * @return $this
     */
    public function setServiceContract(ServiceContractInterface $serviceContract)
    {
        $this->getResource()->setServiceContract($serviceContract);

        return $this;
    }

    /**
     * @return ServiceContractInterface
     */
    public function getServiceContract()
    {
        return $this->getResource()->getServiceContract();
    }

    /**
     * @param ServiceOptionInterface $serviceOption
     * @return $this
     */
    public function setServiceOption(ServiceOptionInterface $serviceOption)
    {
        $this->getResource()->setServiceOption($serviceOption);

        return $this;
    }

    /**
     * @return ServiceOptionInterface
     */
    public function getServiceOption()
    {
        return $this->getResource()->getServiceOption();
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
