<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentStatusInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\StatusInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class ShipmentStatus implements ShipmentStatusInterface
{
    use JsonSerializable;

    const ATTRIBUTE_CARRIER_CODE = 'carrier_status_code';
    const ATTRIBUTE_CARRIER_DESCRIPTION = 'carrier_status_description';
    const ATTRIBUTE_CARRIER_TIMESTAMP = 'carrier_timestamp';

    const RELATIONSHIP_STATUS = 'status';
    const RELATIONSHIP_SHIPMENT = 'shipment';

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_SHIPMENT_STATUS;

    /** @var array */
    private $attributes = [
        self::ATTRIBUTE_CARRIER_CODE        => null,
        self::ATTRIBUTE_CARRIER_DESCRIPTION => null,
        self::ATTRIBUTE_CARRIER_TIMESTAMP   => null,
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
     * @param string $carrierStatusCode
     * @return $this
     */
    public function setCarrierStatusCode($carrierStatusCode)
    {
        $this->attributes[self::ATTRIBUTE_CARRIER_CODE] = $carrierStatusCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCarrierStatusCode()
    {
        return $this->attributes[self::ATTRIBUTE_CARRIER_CODE];
    }

    /**
     * @param string $carrierStatusDescription
     * @return $this
     */
    public function setCarrierStatusDescription($carrierStatusDescription)
    {
        $this->attributes[self::ATTRIBUTE_CARRIER_DESCRIPTION] = $carrierStatusDescription;

        return $this;
    }

    /**
     * @return string
     */
    public function getCarrierStatusDescription()
    {
        return $this->attributes[self::ATTRIBUTE_CARRIER_DESCRIPTION];
    }

    /**
     * @param int|\DateTime $timestamp
     * @return $this
     */
    public function setCarrierTimestamp($timestamp)
    {
        if (is_int($timestamp)) {
            $this->attributes[self::ATTRIBUTE_CARRIER_TIMESTAMP] = $timestamp;
        } elseif ($timestamp instanceof \DateTime) {
            $this->attributes[self::ATTRIBUTE_CARRIER_TIMESTAMP] = $timestamp->getTimestamp();
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCarrierTimestamp()
    {
        return (new \DateTime())->setTimestamp($this->attributes[self::ATTRIBUTE_CARRIER_TIMESTAMP]);
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
