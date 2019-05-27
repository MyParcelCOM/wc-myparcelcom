<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

interface ServiceGroupInterface extends ResourceInterface
{
    /**
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * @param int $min
     * @return $this
     */
    public function setWeightMin($min);

    /**
     * @return int
     */
    public function getWeightMin();

    /**
     * @param int $max
     * @return $this
     */
    public function setWeightMax($max);

    /**
     * @return int
     */
    public function getWeightMax();

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
     * @param int $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * @return int
     */
    public function getPrice();

    /**
     * @param int $size
     * @return $this
     */
    public function setStepSize($size);

    /**
     * @return int
     */
    public function getStepSize();

    /**
     * @param int $price
     * @return $this
     */
    public function setStepPrice($price);

    /**
     * @return int
     */
    public function getStepPrice();
}
