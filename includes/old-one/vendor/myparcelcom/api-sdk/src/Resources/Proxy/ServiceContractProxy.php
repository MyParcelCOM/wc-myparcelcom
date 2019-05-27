<?php

namespace MyParcelCom\ApiSdk\Resources\Proxy;

use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceProxyInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceGroupInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInsuranceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionPriceInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;
use MyParcelCom\ApiSdk\Resources\Traits\ProxiesResource;

class ServiceContractProxy implements ServiceContractInterface, ResourceProxyInterface
{
    use JsonSerializable;
    use ProxiesResource;

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_SERVICE_CONTRACT;

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
     * @param ServiceInterface $service
     * @return $this
     */
    public function setService(ServiceInterface $service)
    {
        $this->getResource()->setService($service);

        return $this;
    }

    /**
     * @return ServiceInterface
     */
    public function getService()
    {
        return $this->getResource()->getService();
    }

    /**
     * @param CarrierContractInterface $carrierContract
     * @return $this
     */
    public function setCarrierContract(CarrierContractInterface $carrierContract)
    {
        $this->getResource()->setCarrierContract($carrierContract);

        return $this;
    }

    /**
     * @return CarrierContractInterface
     */
    public function getCarrierContract()
    {
        return $this->getResource()->getCarrierContract();
    }

    /**
     * @param ServiceGroupInterface[] $groups
     * @return $this
     */
    public function setServiceGroups(array $groups)
    {
        $this->getResource()->setServiceGroups($groups);

        return $this;
    }

    /**
     * @param ServiceGroupInterface $group
     * @return $this
     */
    public function addServiceGroup(ServiceGroupInterface $group)
    {
        $this->getResource()->addServiceGroup($group);

        return $this;
    }

    /**
     * @return ServiceGroupInterface[]
     */
    public function getServiceGroups()
    {
        return $this->getResource()->getServiceGroups();
    }

    /**
     * @param ServiceOptionPriceInterface[] $options
     * @return $this
     */
    public function setServiceOptionPrices(array $options)
    {
        $this->getResource()->setServiceOptionPrices($options);

        return $this;
    }

    /**
     * @param ServiceOptionPriceInterface $option
     * @return $this
     */
    public function addServiceOptionPrice(ServiceOptionPriceInterface $option)
    {
        $this->getResource()->addServiceOptionPrice($option);

        return $this;
    }

    /**
     * @return ServiceOptionPriceInterface[]
     */
    public function getServiceOptionPrices()
    {
        return $this->getResource()->getServiceOptionPrices();
    }

    /**
     * @param ServiceInsuranceInterface[] $insurances
     * @return $this
     */
    public function setServiceInsurances(array $insurances)
    {
        $this->getResource()->setServiceInsurances($insurances);

        return $this;
    }

    /**
     * @param ServiceInsuranceInterface $insurance
     * @return $this
     */
    public function addServiceInsurance(ServiceInsuranceInterface $insurance)
    {
        $this->getResource()->addServiceInsurance($insurance);

        return $this;
    }

    /**
     * @return ServiceInsuranceInterface[]
     */
    public function getServiceInsurances()
    {
        return $this->getResource()->getServiceInsurances();
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
