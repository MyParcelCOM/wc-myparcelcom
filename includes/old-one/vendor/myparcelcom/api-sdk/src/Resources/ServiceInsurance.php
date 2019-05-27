<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInsuranceInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class ServiceInsurance implements ServiceInsuranceInterface
{
    use JsonSerializable;

    const ATTRIBUTE_CURRENCY = 'currency';
    const ATTRIBUTE_AMOUNT = 'amount';
    const ATTRIBUTE_COVERED = 'covered';
    const ATTRIBUTE_PRICE = 'price';

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_SERVICE_INSURANCE;

    /** @var array */
    private $attributes = [
        self::ATTRIBUTE_COVERED => [],
        self::ATTRIBUTE_PRICE   => [],
    ];

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setCovered($covered)
    {
        $this->attributes[self::ATTRIBUTE_COVERED][self::ATTRIBUTE_AMOUNT] = $covered;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCovered()
    {
        return isset($this->attributes[self::ATTRIBUTE_COVERED][self::ATTRIBUTE_AMOUNT])
            ? $this->attributes[self::ATTRIBUTE_COVERED][self::ATTRIBUTE_AMOUNT]
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setPrice($price)
    {
        $this->attributes[self::ATTRIBUTE_PRICE][self::ATTRIBUTE_AMOUNT] = $price;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return isset($this->attributes[self::ATTRIBUTE_PRICE][self::ATTRIBUTE_AMOUNT])
            ? $this->attributes[self::ATTRIBUTE_PRICE][self::ATTRIBUTE_AMOUNT]
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrency($currency)
    {
        $this->attributes[self::ATTRIBUTE_PRICE][self::ATTRIBUTE_CURRENCY] = $currency;
        $this->attributes[self::ATTRIBUTE_COVERED][self::ATTRIBUTE_CURRENCY] = $currency;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrency()
    {
        return isset($this->attributes[self::ATTRIBUTE_PRICE][self::ATTRIBUTE_CURRENCY])
            ? $this->attributes[self::ATTRIBUTE_PRICE][self::ATTRIBUTE_CURRENCY]
            : null;
    }
}
