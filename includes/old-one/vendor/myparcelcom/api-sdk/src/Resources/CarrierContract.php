<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class CarrierContract implements CarrierContractInterface
{
    use JsonSerializable;

    const ATTRIBUTE_CURRENCY = 'currency';

    const RELATIONSHIP_CARRIER = 'carrier';
    const RELATIONSHIP_SERVICE_CONTRACTS = 'service_contracts';

    /** @var callable */
    private $serviceContractsCallback;

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_CARRIER_CONTRACT;

    /** @var array */
    private $attributes = [
        self::ATTRIBUTE_CURRENCY => null,
    ];

    /** @var array */
    private $relationships = [
        self::RELATIONSHIP_CARRIER           => [
            'data' => null,
        ],
        self::RELATIONSHIP_SERVICE_CONTRACTS => [
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
     * {@inheritdoc}
     */
    public function setCurrency($currency)
    {
        $this->attributes[self::ATTRIBUTE_CURRENCY] = $currency;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->attributes[self::ATTRIBUTE_CURRENCY];
    }

    /**
     * {@inheritdoc}
     */
    public function setCarrier(CarrierInterface $carrier)
    {
        $this->relationships[self::RELATIONSHIP_CARRIER]['data'] = $carrier;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCarrier()
    {
        return $this->relationships[self::RELATIONSHIP_CARRIER]['data'];
    }

    /**
     * Set the callback to use when retrieving the service contracts.
     *
     * @param callable $callback
     * @return $this
     */
    public function setServiceContractsCallback(callable $callback)
    {
        $this->serviceContractsCallback = $callback;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setServiceContracts(array $serviceContracts)
    {
        $this->relationships[self::RELATIONSHIP_SERVICE_CONTRACTS]['data'] = [];

        array_walk($serviceContracts, function (ServiceContractInterface $serviceContract) {
            $this->addServiceContract($serviceContract);
        });

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addServiceContract(ServiceContractInterface $serviceContract)
    {
        $this->relationships[self::RELATIONSHIP_SERVICE_CONTRACTS]['data'][] = $serviceContract;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceContracts()
    {
        if (empty($this->relationships[self::RELATIONSHIP_SERVICE_CONTRACTS]['data']) && isset($this->serviceContractsCallback)) {
            $this->setServiceContracts(call_user_func($this->serviceContractsCallback));
        }

        return $this->relationships[self::RELATIONSHIP_SERVICE_CONTRACTS]['data'];
    }
}
