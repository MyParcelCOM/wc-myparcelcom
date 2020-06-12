<?php

namespace MyParcelCom\ApiSdk\Resources;

use DateTime;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierStatusInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ErrorInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentStatusInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\StatusInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;
use MyParcelCom\ApiSdk\Utils\DateUtils;

class ShipmentStatus implements ShipmentStatusInterface
{
    use JsonSerializable;

    const ATTRIBUTE_CARRIER_STATUSES = 'carrier_statuses';
    const ATTRIBUTE_ERRORS = 'errors';
    const ATTRIBUTE_CREATED_AT = 'created_at';

    const RELATIONSHIP_STATUS = 'status';
    const RELATIONSHIP_SHIPMENT = 'shipment';

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_SHIPMENT_STATUS;

    /** @var array */
    private $attributes = [
        self::ATTRIBUTE_CARRIER_STATUSES => [],
        self::ATTRIBUTE_ERRORS           => [],
        self::ATTRIBUTE_CREATED_AT       => null,
    ];

    /** @var array */
    private $relationships = [
        self::RELATIONSHIP_STATUS   => [
            'data' => null,
        ],
        self::RELATIONSHIP_SHIPMENT => [
            'data' => null,
        ],
    ];

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
     * @param CarrierStatusInterface[] $carrierStatuses
     * @return $this
     */
    public function setCarrierStatuses(array $carrierStatuses)
    {
        $this->attributes[self::ATTRIBUTE_CARRIER_STATUSES] = [];
        foreach ($carrierStatuses as $carrierStatus) {
            $this->addCarrierStatus($carrierStatus);
        }

        return $this;
    }

    /**
     * @param CarrierStatusInterface $carrierStatus
     * @return $this
     */
    public function addCarrierStatus(CarrierStatusInterface $carrierStatus)
    {
        $this->attributes[self::ATTRIBUTE_CARRIER_STATUSES][] = $carrierStatus;

        return $this;
    }

    /**
     * @return CarrierStatusInterface[]
     */
    public function getCarrierStatuses()
    {
        return $this->attributes[self::ATTRIBUTE_CARRIER_STATUSES];
    }

    /**
     * @param ErrorInterface[] $errors
     * @return $this
     */
    public function setErrors(array $errors)
    {
        $this->attributes[self::ATTRIBUTE_ERRORS] = [];
        foreach ($errors as $error) {
            $this->addError($error);
        }

        return $this;
    }

    /**
     * @param ErrorInterface $error
     * @return $this
     */
    public function addError(ErrorInterface $error)
    {
        $this->attributes[self::ATTRIBUTE_ERRORS][] = $error;

        return $this;
    }

    /**
     * @return ErrorInterface[]
     */
    public function getErrors()
    {
        return $this->attributes[self::ATTRIBUTE_ERRORS];
    }

    /**
     * @param DateTime|string|int $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->attributes[self::ATTRIBUTE_CREATED_AT] = DateUtils::toTimestamp($createdAt);

        return $this;
    }

    /**
     * @return null|DateTime
     */
    public function getCreatedAt()
    {
        return isset($this->attributes[self::ATTRIBUTE_CREATED_AT])
            ? (new DateTime())->setTimestamp($this->attributes[self::ATTRIBUTE_CREATED_AT])
            : null;
    }

    /**
     * @param ShipmentInterface $shipment
     * @return $this
     */
    public function setShipment(ShipmentInterface $shipment)
    {
        $this->relationships[self::RELATIONSHIP_SHIPMENT]['data'] = $shipment;

        return $this;
    }

    /**
     * @return ShipmentInterface
     */
    public function getShipment()
    {
        return $this->relationships[self::RELATIONSHIP_SHIPMENT]['data'];
    }

    /**
     * @param StatusInterface $status
     * @return $this
     */
    public function setStatus(StatusInterface $status)
    {
        $this->relationships[self::RELATIONSHIP_STATUS]['data'] = $status;

        return $this;
    }

    /**
     * @return StatusInterface
     */
    public function getStatus()
    {
        return $this->relationships[self::RELATIONSHIP_STATUS]['data'];
    }
}
