<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

interface ServiceInsuranceInterface extends ResourceInterface
{
    /**
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * @param int $covered
     * @return $this
     */
    public function setCovered($covered);

    /**
     * @return int
     */
    public function getCovered();

    /**
     * @param int $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * @return int
     */
    public function getPrice();

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency);

    /**
     * @return string
     */
    public function getCurrency();
}
