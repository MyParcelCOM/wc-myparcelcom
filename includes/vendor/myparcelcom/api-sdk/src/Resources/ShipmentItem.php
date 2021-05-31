<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Exceptions\MyParcelComException;
use MyParcelCom\ApiSdk\Resources\Interfaces\PhysicalPropertiesInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentItemInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class ShipmentItem implements ShipmentItemInterface
{
    use JsonSerializable;

    const AMOUNT = 'amount';
    const CURRENCY = 'currency';

    /** @var string|null */
    private $sku;

    /** @var string */
    private $description;

    /** @var string|null */
    private $imageUrl;

    /** @var string|null */
    private $hsCode;

    /** @var int */
    private $quantity;

    /** @var array */
    private $itemValue = [
        self::AMOUNT   => null,
        self::CURRENCY => null,
    ];

    /** @var string|null */
    private $originCountryCode;

    /** @var int|null */
    private $itemWeight;

    /**
     * @param string|null $sku
     * @return $this
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * @return string|null
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
     * @param string|null $imageUrl
     * @return $this
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param string|null $hsCode
     * @return $this
     */
    public function setHsCode($hsCode)
    {
        $this->hsCode = $hsCode;

        return $this;
    }

    /**
     * @return string|null
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
        $this->quantity = (int) $quantity;

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
        $this->itemValue[self::AMOUNT] = (int) $value;

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
     * @param string|null $countryCode
     * @return $this
     */
    public function setOriginCountryCode($countryCode)
    {
        $this->originCountryCode = $countryCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOriginCountryCode()
    {
        return $this->originCountryCode;
    }

    /**
     * @param int|null $weight
     * @param string   $unit
     * @return $this
     */
    public function setItemWeight($weight, $unit = PhysicalPropertiesInterface::WEIGHT_GRAM)
    {
        if (!isset(PhysicalProperties::$unitConversion[$unit])) {
            throw new MyParcelComException('invalid unit: ' . $unit);
        }

        $this->itemWeight = (int) round($weight * PhysicalProperties::$unitConversion[$unit]);

        return $this;
    }

    /**
     * @param string $unit
     * @return int|null
     */
    public function getItemWeight($unit = PhysicalPropertiesInterface::WEIGHT_GRAM)
    {
        if (!isset(PhysicalProperties::$unitConversion[$unit])) {
            throw new MyParcelComException('invalid unit: ' . $unit);
        }

        return (int) round($this->itemWeight / PhysicalProperties::$unitConversion[$unit]);
    }
}
