<?php

namespace MyParcelCom\ApiSdk\Resources\Proxy;

use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierStatusInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ErrorInterface;
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
     * @param array $carrierStatuses
     * @return $this
     */
    public function setCarrierStatuses(array $carrierStatuses)
    {
        $this->getResource()->setCarrierStatuses($carrierStatuses);

        return $this;
    }

    /**
     * @param CarrierStatusInterface $carrierStatus
     * @return $this
     */
    public function addCarrierStatus(CarrierStatusInterface $carrierStatus)
    {
        $this->getResource()->addCarrierStatus($carrierStatus);

        return $this;
    }

    /**
     * @return CarrierStatusInterface[]
     */
    public function getCarrierStatuses()
    {
        return $this->getResource()->getCarrierStatuses();
    }

    /**
     * @param array $errors
     * @return $this
     */
    public function setErrors(array $errors)
    {
        $this->getResource()->setErrors($errors);

        return $this;
    }

    /**
     * @param ErrorInterface $error
     * @return $this
     */
    public function addError(ErrorInterface $error)
    {
        $this->getResource()->addError($error);

        return $this;
    }

    /**
     * @return ErrorInterface[]
     */
    public function getErrors()
    {
        return $this->getResource()->getErrors();
    }

    /**
     * @param int $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->getResource()->setCreatedAt($createdAt);

        return $this;
    }

    /**
     * @return int
     */
    public function getCreatedAt()
    {
        return $this->getResource()->getCreatedAt();
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
