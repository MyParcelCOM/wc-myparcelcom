<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

interface ContractInterface extends ResourceInterface
{
    /**
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency);

    /**
     * @return string
     */
    public function getCurrency();

    /**
     * @param CarrierInterface $carrier
     * @return $this
     */
    public function setCarrier(CarrierInterface $carrier);

    /**
     * @return CarrierInterface
     */
    public function getCarrier();

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getStatus();
}
