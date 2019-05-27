<?php

namespace MyParcelCom\ApiSdk\Resources\Proxy;

use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceProxyInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentStatusInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\StatusInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;
use MyParcelCom\ApiSdk\Resources\Traits\ProxiesResource;

class ShipmentStatusProxy implements ShipmentStatusInterface, ResourceProxyInterface
{
    use JsonSerializable;
    use ProxiesResource;

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_SHIPMENT_STATUS;

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
     * @param string $carrierStatusCode
     * @return $this
     */
    public function setCarrierStatusCode($carrierStatusCode)
    {
        $this->getResource()->setCarrierStatusCode($carrierStatusCode);

        return $this;
    }

    /**
     * @return string
     */
    public function getCarrierStatusCode()
    {
        return $this->getResource()->getCarrierStatusCode();
    }

    /**
     * @param string $carrierStatusDescription
     * @return $this
     */
    public function setCarrierStatusDescription($carrierStatusDescription)
    {
        $this->getResource()->setCarrierStatusDescription($carrierStatusDescription);

        return $this;
    }

    /**
     * @return string
     */
    public function getCarrierStatusDescription()
    {
        return $this->getResource()->getCarrierStatusDescription();
    }

    /**
     * @param int|\DateTime $timestamp
     * @return $this
     */
    public function setCarrierTimestamp($timestamp)
    {
        $this->getResource()->setCarrierTimestamp($timestamp);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCarrierTimestamp()
    {
        return $this->getResource()->getCarrierTimestamp();
    }

    /**
     * @param ShipmentInterface $shipment
     * @return $this
     */
    public function setShipment(ShipmentInterface $shipment)
    {
        $this->getResource()->setShipment($shipment);

        return $this;
    }

    /**
     * @return ShipmentInterface
     */
    public function getShipment()
    {
        return $this->getResource()->getShipment();
    }

    /**
     * @param StatusInterface $status
     * @return $this
     */
    public function setStatus(StatusInterface $status)
    {
        $this->getResource()->setStatus($status);

        return $this;
    }

    /**
     * @return StatusInterface
     */
    public function getStatus()
    {
        return $this->getResource()->getStatus();
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
