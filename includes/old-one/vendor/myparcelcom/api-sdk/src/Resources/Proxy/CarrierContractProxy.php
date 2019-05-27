<?php

namespace MyParcelCom\ApiSdk\Resources\Proxy;

use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceProxyInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;
use MyParcelCom\ApiSdk\Resources\Traits\ProxiesResource;

class CarrierContractProxy implements CarrierContractInterface, ResourceProxyInterface
{
    use JsonSerializable;
    use ProxiesResource;

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_CARRIER_CONTRACT;

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
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * @param CarrierInterface $carrier
     * @return $this
     */
    public function setCarrier(CarrierInterface $carrier)
    {
        $this->getResource()->setCarrier($carrier);

        return $this;
    }

    /**
     * @return CarrierInterface
     */
    public function getCarrier()
    {
        return $this->getResource()->getCarrier();
    }

    /**
     * @param ServiceContractInterface[] $serviceContracts
     * @return $this
     */
    public function setServiceContracts(array $serviceContracts)
    {
        $this->getResource()->setServiceContracts($serviceContracts);

        return $this;
    }

    /**
     * @param ServiceContractInterface $serviceContract
     * @return $this
     */
    public function addServiceContract(ServiceContractInterface $serviceContract)
    {
        $this->getResource()->addServiceContract($serviceContract);

        return $this;
    }

    /**
     * @return ServiceContractInterface[]
     */
    public function getServiceContracts()
    {
        return $this->getResource()->getServiceContracts();
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
