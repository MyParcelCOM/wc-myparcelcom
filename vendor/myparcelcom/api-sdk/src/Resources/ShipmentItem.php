<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Enums\WeightUnitEnum;
use MyParcelCom\ApiSdk\Exceptions\MyParcelComException;
use MyParcelCom\ApiSdk\Helpers\WeightConverter;
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

    /** @var int|null */
    private $vatPercentage;

    /** @var string|null */
    private $originCountryCode;

    /** @var int|null */
    private $itemWeight;

    /** @var string */
    private $itemWeightUnit = WeightUnitEnum::GRAM;

    /** @var bool */
    private $isPreferentialOrigin = false;

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
     * @param int|null $percentage Value between 0 and 100
     * @return $this
     */
    public function setVatPercentage($percentage)
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new MyParcelComException('Invalid percentage: ' . $percentage . ' should be between 0 and 100.');
        }

        $this->vatPercentage = (int) $percentage;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getVatPercentage()
    {
        return $this->vatPercentage;
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
     * This method can also set the weight unit, so you do not have to call setItemWeightUnit() separately.
     *
     * @param int|null    $weight
     * @param string|null $unit
     * @return $this
     */
    public function setItemWeight($weight, $unit = null)
    {
        // Because we supported unit conversion in the SDK before we did this in the API, `grams` and `g` are supported.
        switch ($unit) {
            case null:
                // We do not want this function to alter $this->itemWeightUnit when it's called without a parameter.
                // In that case `grams` are implied, which is already the default item weight unit set on this class.
                break;
            case WeightUnitEnum::MILLIGRAM:
                $this->itemWeightUnit = WeightUnitEnum::MILLIGRAM;
                break;
            case PhysicalPropertiesInterface::WEIGHT_GRAM:
            case WeightUnitEnum::GRAM:
                $this->itemWeightUnit = WeightUnitEnum::GRAM;
                break;
            case PhysicalPropertiesInterface::WEIGHT_KILOGRAM:
            case WeightUnitEnum::KILOGRAM:
                $this->itemWeightUnit = WeightUnitEnum::KILOGRAM;
                break;
            case PhysicalPropertiesInterface::WEIGHT_OUNCE:
            case WeightUnitEnum::OUNCE:
                $this->itemWeightUnit = WeightUnitEnum::OUNCE;
                break;
            case PhysicalPropertiesInterface::WEIGHT_POUND:
            case WeightUnitEnum::POUND:
                $this->itemWeightUnit = WeightUnitEnum::POUND;
                break;
            case PhysicalPropertiesInterface::WEIGHT_STONE:
                // Our API does not support stones. If anyone is using stones, we will work with the old implementation.
                $this->itemWeight = (int) ceil($weight * PhysicalProperties::$unitConversion[$unit]);
                return $this;
            default:
                throw new MyParcelComException('invalid unit: ' . $unit);
        }

        $this->itemWeight = (int) ceil($weight);

        return $this;
    }

    /**
     * @param string $unit
     * @return int|null
     */
    public function getItemWeight($unit = null)
    {
        if ($this->itemWeight === null) {
            return $this->itemWeight;
        }

        // Convert old PhysicalPropertiesInterface units to the new WeightUnitEnum used by our API and WeightConverter.
        switch ($unit) {
            case null:
                // We use the default item weight unit set on this class if this function is called without a parameter.
                $unit = $this->itemWeightUnit;
                break;
            case WeightUnitEnum::MILLIGRAM:
                $unit = WeightUnitEnum::MILLIGRAM;
                break;
            case PhysicalPropertiesInterface::WEIGHT_GRAM:
            case WeightUnitEnum::GRAM:
                $unit = WeightUnitEnum::GRAM;
                break;
            case PhysicalPropertiesInterface::WEIGHT_KILOGRAM:
            case WeightUnitEnum::KILOGRAM:
                $unit = WeightUnitEnum::KILOGRAM;
                break;
            case PhysicalPropertiesInterface::WEIGHT_OUNCE:
            case WeightUnitEnum::OUNCE:
                $unit = WeightUnitEnum::OUNCE;
                break;
            case PhysicalPropertiesInterface::WEIGHT_POUND:
            case WeightUnitEnum::POUND:
                $unit = WeightUnitEnum::POUND;
                break;
            case PhysicalPropertiesInterface::WEIGHT_STONE:
                // Our API does not support stones. If anyone is using stones, we will work with the old implementation.
                return (int) ceil($this->itemWeight / PhysicalProperties::$unitConversion[$unit]);
            default:
                throw new MyParcelComException('invalid unit: ' . $unit);
        }

        $convertedWeight = WeightConverter::convert(
            $this->itemWeight,
            $this->itemWeightUnit,
            $unit
        );

        return (int) ceil($convertedWeight);
    }

    /**
     * @param string $weightUnit
     * @return $this
     */
    public function setItemWeightUnit($weightUnit)
    {
        if (!WeightUnitEnum::isValid($weightUnit)) {
            throw new MyParcelComException('$weightUnit should be one of: ' . implode(', ', WeightUnitEnum::toArray()));
        }

        $this->itemWeightUnit = $weightUnit;

        return $this;
    }

    /**
     * @return string
     */
    public function getItemWeightUnit()
    {
        return $this->itemWeightUnit;
    }

    /**
     * @param bool $isPreferentialOrigin
     * @return $this
     */
    public function setIsPreferentialOrigin($isPreferentialOrigin)
    {
        $this->isPreferentialOrigin = $isPreferentialOrigin;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsPreferentialOrigin()
    {
        return $this->isPreferentialOrigin;
    }
}
