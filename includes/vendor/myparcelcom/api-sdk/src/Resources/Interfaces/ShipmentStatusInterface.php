<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

use DateTime;

interface ShipmentStatusInterface extends ResourceInterface
{
    /**
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * @param CarrierStatusInterface[] $carrierStatuses
     * @return $this
     */
    public function setCarrierStatuses(array $carrierStatuses);

    /**
     * @param CarrierStatusInterface $carrierStatus
     * @return $this
     */
    public function addCarrierStatus(CarrierStatusInterface $carrierStatus);

    /**
     * @return CarrierStatusInterface[]
     */
    public function getCarrierStatuses();

    /**
     * @param ErrorInterface[] $errors
     * @return $this
     */
    public function setErrors(array $errors);

    /**
     * @param ErrorInterface $error
     * @return $this
     */
    public function addError(ErrorInterface $error);

    /**
     * @return ErrorInterface[]
     */
    public function getErrors();

    /**
     * @param DateTime|string|int $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return DateTime
     */
    public function getCreatedAt();

    /**
     * @param ShipmentInterface $shipment
     * @return $this
     */
    public function setShipment(ShipmentInterface $shipment);

    /**
     * @return ShipmentInterface
     */
    public function getShipment();

    /**
     * @param StatusInterface $status
     * @return $this
     */
    public function setStatus(StatusInterface $status);

    /**
     * @return StatusInterface
     */
    public function getStatus();
}
