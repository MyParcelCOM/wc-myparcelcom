<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

interface ShipmentItemInterface extends \JsonSerializable
{
    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * @return string
     */
    public function getSku();

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $hsCode
     * @return $this
     */
    public function setHsCode($hsCode);

    /**
     * @return string
     */
    public function getHsCode();

    /**
     * @param int $quantity
     * @return $this
     */
    public function setQuantity($quantity);

    /**
     * @return int
     */
    public function getQuantity();

    /**
     * @param int $value
     * @return $this
     */
    public function setItemValue($value);

    /**
     * @return int
     */
    public function getItemValue();

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
     * @param string $countryCode
     * @return $this
     */
    public function setOriginCountryCode($countryCode);

    /**
     * @return string
     */
    public function getOriginCountryCode();
}
