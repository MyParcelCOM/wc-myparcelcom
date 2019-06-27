<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class ServiceOption implements ServiceOptionInterface
{
    use JsonSerializable;

    const ATTRIBUTE_NAME = 'name';
    const ATTRIBUTE_CODE = 'code';
    const ATTRIBUTE_CATEGORY = 'category';

    const META_PRICE = 'price';
    const META_PRICE_AMOUNT = 'amount';
    const META_PRICE_CURRENCY = 'currency';
    const META_INCLUDED = 'included';

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_SERVICE_OPTION;

    /** @var array */
    private $attributes = [
        self::ATTRIBUTE_NAME     => null,
        self::ATTRIBUTE_CODE     => null,
        self::ATTRIBUTE_CATEGORY => null,
    ];

    /** @var array */
    private $meta = [
        self::META_PRICE    => [
            self::META_PRICE_AMOUNT   => null,
            self::META_PRICE_CURRENCY => null,
        ],
        self::META_INCLUDED => null,
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
    public function setName($name)
    {
        $this->attributes[self::ATTRIBUTE_NAME] = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->attributes[self::ATTRIBUTE_NAME];
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->attributes[self::ATTRIBUTE_CODE] = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->attributes[self::ATTRIBUTE_CODE];
    }

    /**
     * {@inheritdoc}
     */
    public function setCategory($category)
    {
        $this->attributes[self::ATTRIBUTE_CATEGORY] = $category;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategory()
    {
        return $this->attributes[self::ATTRIBUTE_CATEGORY];
    }

    /**
     * {@inheritdoc}
     */
    public function setPrice($price)
    {
        $this->meta[self::META_PRICE][self::META_PRICE_AMOUNT] = $price !== null ? (int)$price : null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return $this->meta[self::META_PRICE][self::META_PRICE_AMOUNT];
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrency($currency)
    {
        $this->meta[self::META_PRICE][self::META_PRICE_CURRENCY] = $currency;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrency()
    {
        return $this->meta[self::META_PRICE][self::META_PRICE_CURRENCY];
    }

    /**
     * {@inheritdoc}
     */
    public function setIncluded($included)
    {
        $this->meta[self::META_INCLUDED] = $included;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isIncluded()
    {
        return $this->meta[self::META_INCLUDED];
    }
}
