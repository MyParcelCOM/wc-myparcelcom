<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Resources\Interfaces\AddressInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\OpeningHourInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\PickUpDropOffLocationInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\PositionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class PickUpDropOffLocation implements PickUpDropOffLocationInterface
{
    use JsonSerializable;

    const ATTRIBUTE_CODE = 'code';
    const ATTRIBUTE_ADDRESS = 'address';
    const ATTRIBUTE_OPENING_HOURS = 'openingHours';
    const ATTRIBUTE_POSITION = 'position';

    const RELATIONSHIP_CARRIER = 'carrier';

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_PUDO_LOCATION;

    /** @var array */
    private $attributes = [
        self::ATTRIBUTE_CODE          => null,
        self::ATTRIBUTE_ADDRESS       => null,
        self::ATTRIBUTE_OPENING_HOURS => [],
        self::ATTRIBUTE_POSITION      => null,
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
    public function setCode($code)
    {
        $this->attributes[self::ATTRIBUTE_CODE] = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->attributes[self::ATTRIBUTE_CODE];
    }

    /**
     * {@inheritdoc}
     */
    public function setAddress(AddressInterface $address)
    {
        $this->attributes[self::ATTRIBUTE_ADDRESS] = $address;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress()
    {
        return $this->attributes[self::ATTRIBUTE_ADDRESS];
    }

    /**
     * {@inheritdoc}
     */
    public function setOpeningHours(array $openingHours)
    {
        $this->attributes[self::ATTRIBUTE_OPENING_HOURS] = [];

        array_walk($openingHours, function ($openingHour) {
            $this->addOpeningHour($openingHour);
        });

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addOpeningHour(OpeningHourInterface $openingHour)
    {
        $this->attributes[self::ATTRIBUTE_OPENING_HOURS][] = $openingHour;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOpeningHours()
    {
        return $this->attributes[self::ATTRIBUTE_OPENING_HOURS];
    }

    /**
     * {@inheritdoc}
     */
    public function setPosition(PositionInterface $position)
    {
        $this->attributes[self::ATTRIBUTE_POSITION] = $position;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return $this->attributes[self::ATTRIBUTE_POSITION];
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
}
