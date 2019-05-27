<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceGroupInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class ServiceGroup implements ServiceGroupInterface
{
    use JsonSerializable;

    const ATTRIBUTE_CURRENCY = 'currency';
    const ATTRIBUTE_AMOUNT = 'amount';
    const ATTRIBUTE_PRICE = 'price';
    const ATTRIBUTE_STEP_PRICE = 'step_price';
    const ATTRIBUTE_STEP_SIZE = 'step_size';
    const ATTRIBUTE_WEIGHT = 'weight';
    const ATTRIBUTE_WEIGHT_MAX = 'max';
    const ATTRIBUTE_WEIGHT_MIN = 'min';

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_SERVICE_GROUP;

    /** @var array */
    private $attributes = [
        self::ATTRIBUTE_PRICE      => [],
        self::ATTRIBUTE_STEP_PRICE => [],
        self::ATTRIBUTE_STEP_SIZE  => null,
        self::ATTRIBUTE_WEIGHT     => [],
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
    public function setWeightMin($min)
    {

        $this->attributes[self::ATTRIBUTE_WEIGHT][self::ATTRIBUTE_WEIGHT_MIN] = (int)$min;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWeightMin()
    {
        return isset($this->attributes[self::ATTRIBUTE_WEIGHT][self::ATTRIBUTE_WEIGHT_MIN])
            ? $this->attributes[self::ATTRIBUTE_WEIGHT][self::ATTRIBUTE_WEIGHT_MIN]
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setWeightMax($max)
    {
        $this->attributes[self::ATTRIBUTE_WEIGHT][self::ATTRIBUTE_WEIGHT_MAX] = (int)$max;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWeightMax()
    {
        return isset($this->attributes[self::ATTRIBUTE_WEIGHT][self::ATTRIBUTE_WEIGHT_MAX])
            ? $this->attributes[self::ATTRIBUTE_WEIGHT][self::ATTRIBUTE_WEIGHT_MAX]
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrency($currency)
    {
        $this->attributes[self::ATTRIBUTE_PRICE][self::ATTRIBUTE_CURRENCY] = $currency;
        $this->attributes[self::ATTRIBUTE_STEP_PRICE][self::ATTRIBUTE_CURRENCY] = $currency;

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

    /**
     * {@inheritdoc}
     */
    public function setPrice($price)
    {
        $this->attributes[self::ATTRIBUTE_PRICE][self::ATTRIBUTE_AMOUNT] = (int)$price;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return $this->attributes[self::ATTRIBUTE_PRICE][self::ATTRIBUTE_AMOUNT];
    }

    /**
     * {@inheritdoc}
     */
    public function setStepSize($size)
    {
        $this->attributes[self::ATTRIBUTE_STEP_SIZE] = (int)$size;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStepSize()
    {
        return $this->attributes[self::ATTRIBUTE_STEP_SIZE];
    }

    /**
     * {@inheritdoc}
     */
    public function setStepPrice($price)
    {
        $this->attributes[self::ATTRIBUTE_STEP_PRICE][self::ATTRIBUTE_AMOUNT] = (int)$price;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStepPrice()
    {
        return $this->attributes[self::ATTRIBUTE_STEP_PRICE][self::ATTRIBUTE_AMOUNT];
    }
}
