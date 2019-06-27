<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentItemInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class ShipmentItem implements ShipmentItemInterface
{
    use JsonSerializable;

    const AMOUNT = 'amount';
    const CURRENCY = 'currency';

    /** @var string */
    private $sku;

    /** @var string */
    private $description;

    /** @var string */
    private $hsCode;

    /** @var int */
    private $quantity;

    /** @var array */
    private $itemValue = [
        self::AMOUNT   => null,
        self::CURRENCY => null,
    ];

    /** @var string */
    private $originCountryCode;

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $hsCode
     * @return $this
     */
    public function setHsCode($hsCode)
    {
        $this->hsCode = $hsCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getHsCode()
    {
        return $this->hsCode;
    }

    /**
     * @param int $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = (int)$quantity;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setItemValue($value)
    {
        $this->itemValue[self::AMOUNT] = (int)$value;

        return $this;
    }

    /**
     * @return int
     */
    public function getItemValue()
    {
        return $this->itemValue[self::AMOUNT];
    }

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->itemValue[self::CURRENCY] = $currency;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->itemValue[self::CURRENCY];
    }

    /**
     * @param string $countryCode
     * @return $this
     */
    public function setOriginCountryCode($countryCode)
    {
        $this->originCountryCode = $countryCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getOriginCountryCode()
    {
        return $this->originCountryCode;
    }
}
