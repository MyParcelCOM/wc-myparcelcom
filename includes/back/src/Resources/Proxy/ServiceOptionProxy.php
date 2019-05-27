<?php

namespace MyParcelCom\ApiSdk\Resources\Proxy;

use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceProxyInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;
use MyParcelCom\ApiSdk\Resources\Traits\ProxiesResource;

class ServiceOptionProxy implements ServiceOptionInterface, ResourceProxyInterface
{
    use JsonSerializable;
    use ProxiesResource;

    const META_PRICE = 'price';
    const META_PRICE_AMOUNT = 'amount';
    const META_PRICE_CURRENCY = 'currency';
    const META_INCLUDED = 'included';

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_SERVICE_OPTION;

    /** @var array */
    private $meta = [
        self::META_PRICE    => [
            self::META_PRICE_AMOUNT   => null,
            self::META_PRICE_CURRENCY => null,
        ],
        self::META_INCLUDED => null,
    ];

    /**
     * Set the identifier for this file.
     *
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
        $this->getResource()->setName($name);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getResource()->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->getResource()->setCode($code);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getResource()->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function setCategory($category)
    {
        $this->getResource()->setCategory($category);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategory()
    {
        return $this->getResource()->getCategory();
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

    /**
     * This function puts all object properties in an array and returns it.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $values = get_object_vars($this);
        unset($values['resource']);
        unset($values['api']);
        unset($values['uri']);

        $json = $this->arrayValuesToArray($values);

        if (isset($json['meta']) && $this->isEmpty($json['meta'])) {
            unset($json['meta']);
        }

        return $json;
    }
}
