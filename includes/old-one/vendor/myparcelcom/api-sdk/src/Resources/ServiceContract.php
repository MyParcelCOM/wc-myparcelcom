<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceGroupInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInsuranceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionPriceInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class ServiceContract implements ServiceContractInterface
{
    use JsonSerializable;

    const RELATIONSHIP_SERVICE = 'service';
    const RELATIONSHIP_CARRIER_CONTRACT = 'carrier_contract';
    const RELATIONSHIP_SERVICE_GROUPS = 'service_groups';
    const RELATIONSHIP_SERVICE_OPTION_PRICES = 'service_option_prices';
    const RELATIONSHIP_SERVICE_INSURANCES = 'service_insurances';

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_SERVICE_CONTRACT;

    /** @var array */
    private $relationships = [
        self::RELATIONSHIP_SERVICE               => [
            'data' => null,
        ],
        self::RELATIONSHIP_CARRIER_CONTRACT      => [
            'data' => null,
        ],
        self::RELATIONSHIP_SERVICE_GROUPS        => [
            'data' => [],
        ],
        self::RELATIONSHIP_SERVICE_OPTION_PRICES => [
            'data' => [],
        ],
        self::RELATIONSHIP_SERVICE_INSURANCES    => [
            'data' => [],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param ServiceInterface $service
     * @return $this
     */
    public function setService(ServiceInterface $service)
    {
        $this->relationships[self::RELATIONSHIP_SERVICE]['data'] = $service;

        return $this;
    }

    /**
     * @return ServiceInterface
     */
    public function getService()
    {
        return $this->relationships[self::RELATIONSHIP_SERVICE]['data'];
    }

    /**
     * @param CarrierContractInterface $carrierContract
     * @return $this
     */
    public function setCarrierContract(CarrierContractInterface $carrierContract)
    {
        $this->relationships[self::RELATIONSHIP_CARRIER_CONTRACT]['data'] = $carrierContract;

        return $this;
    }

    /**
     * @return CarrierContractInterface
     */
    public function getCarrierContract()
    {
        return $this->relationships[self::RELATIONSHIP_CARRIER_CONTRACT]['data'];
    }

    /**
     * @param ServiceGroupInterface[] $groups
     * @return $this
     */
    public function setServiceGroups(array $groups)
    {
        $this->relationships[self::RELATIONSHIP_SERVICE_GROUPS]['data'] = [];

        array_walk($groups, function (ServiceGroupInterface $group) {
            $this->relationships[self::RELATIONSHIP_SERVICE_GROUPS]['data'][] = $group;
        });

        return $this;
    }

    /**
     * @param ServiceGroupInterface $group
     * @return $this
     */
    public function addServiceGroup(ServiceGroupInterface $group)
    {
        $this->relationships[self::RELATIONSHIP_SERVICE_GROUPS]['data'][] = $group;

        return $this;
    }

    /**
     * @return ServiceGroupInterface[]
     */
    public function getServiceGroups()
    {
        return $this->relationships[self::RELATIONSHIP_SERVICE_GROUPS]['data'];
    }

    /**
     * @param ServiceOptionInterface[] $options
     * @return $this
     */
    public function setServiceOptionPrices(array $options)
    {
        $this->relationships[self::RELATIONSHIP_SERVICE_OPTION_PRICES]['data'] = [];

        array_walk($options, function (ServiceOptionPriceInterface $option) {
            $this->relationships[self::RELATIONSHIP_SERVICE_OPTION_PRICES]['data'][] = $option;
        });

        return $this;
    }

    /**
     * @param ServiceOptionPriceInterface $option
     * @return $this
     */
    public function addServiceOptionPrice(ServiceOptionPriceInterface $option)
    {
        $this->relationships[self::RELATIONSHIP_SERVICE_OPTION_PRICES]['data'][] = $option;

        return $this;
    }

    /**
     * @return ServiceOptionInterface[]
     */
    public function getServiceOptionPrices()
    {
        return $this->relationships[self::RELATIONSHIP_SERVICE_OPTION_PRICES]['data'];
    }

    /**
     * @param ServiceInsuranceInterface[] $insurances
     * @return $this
     */
    public function setServiceInsurances(array $insurances)
    {
        $this->relationships[self::RELATIONSHIP_SERVICE_INSURANCES]['data'] = [];

        array_walk($insurances, function (ServiceInsuranceInterface $insurance) {
            $this->relationships[self::RELATIONSHIP_SERVICE_INSURANCES]['data'][] = $insurance;
        });

        return $this;
    }

    /**
     * @param ServiceInsuranceInterface $insurance
     * @return $this
     */
    public function addServiceInsurance(ServiceInsuranceInterface $insurance)
    {
        $this->relationships[self::RELATIONSHIP_SERVICE_INSURANCES]['data'][] = $insurance;

        return $this;
    }

    /**
     * @return ServiceInsuranceInterface[]
     */
    public function getServiceInsurances()
    {
        return $this->relationships[self::RELATIONSHIP_SERVICE_INSURANCES]['data'];
    }
}
