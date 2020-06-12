<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class Contract implements ContractInterface
{
    use JsonSerializable;

    const ATTRIBUTE_CURRENCY = 'currency';
    const ATTRIBUTE_STATUS = 'status';

    const RELATIONSHIP_CARRIER = 'carrier';

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_CONTRACT;

    /** @var array */
    private $attributes = [
        self::ATTRIBUTE_CURRENCY => null,
        self::ATTRIBUTE_STATUS   => null,
    ];

    /** @var array */
    private $relationships = [
        self::RELATIONSHIP_CARRIER => [
            'data' => null,
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        $this->attributes[self::ATTRIBUTE_STATUS] = $status;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->attributes[self::ATTRIBUTE_STATUS];
    }
}
