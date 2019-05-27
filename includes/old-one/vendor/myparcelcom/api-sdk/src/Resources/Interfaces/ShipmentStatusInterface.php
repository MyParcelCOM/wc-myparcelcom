<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

interface ShipmentStatusInterface extends ResourceInterface
{
    /**
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * @param string $carrierStatusCode
     * @return $this
     */
    public function setCarrierStatusCode($carrierStatusCode);

    /**
     * @return string
     */
    public function getCarrierStatusCode();

    /**
     * @param string $carrierStatusDescription
     * @return $this
     */
    public function setCarrierStatusDescription($carrierStatusDescription);

    /**
     * @return string
     */
    public function getCarrierStatusDescription();

    /**
     * @param int|\DateTime $timestamp
     * @return $this
     */
    public function setCarrierTimestamp($timestamp);

    /**
     * @return \DateTime
     */
    public function getCarrierTimestamp();

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
