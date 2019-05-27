<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionPriceInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class ServiceOptionPrice implements ServiceOptionPriceInterface
{
    use JsonSerializable;

    const ATTRIBUTE_CURRENCY = 'currency';
    const ATTRIBUTE_AMOUNT = 'amount';
    const ATTRIBUTE_PRICE = 'price';
    const ATTRIBUTE_REQUIRED = 'required';

    const RELATIONSHIP_SERVICE_CONTRACT = 'service_contract';
    const RELATIONSHIP_SERVICE_OPTION = 'service_option';

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_SERVICE_OPTION_PRICE;

    /** @var array */
    private $attributes = [
        self::ATTRIBUTE_PRICE => [],
    ];

    /** @var array */
    private $relationships = [
        self::RELATIONSHIP_SERVICE_CONTRACT => [
            'data' => null,
        ],
        self::RELATIONSHIP_SERVICE_OPTION   => [
            'data' => null,
        ],
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
    public function setRequired($required)
    {
        $this->attributes[self::ATTRIBUTE_REQUIRED] = $required;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isRequired()
    {
        return isset($this->attributes[self::ATTRIBUTE_REQUIRED])
            ? $this->attributes[self::ATTRIBUTE_REQUIRED]
            : null;
    }

    /**
     * @param ServiceContractInterface $serviceContract
     * @return $this
     */
    public function setServiceContract(ServiceContractInterface $serviceContract)
    {
        $this->relationships[self::RELATIONSHIP_SERVICE_CONTRACT]['data'] = $serviceContract;

        return $this;
    }

    /**
     * @return ServiceContractInterface
     */
    public function getServiceContract()
    {
        return $this->relationships[self::RELATIONSHIP_SERVICE_CONTRACT]['data'];
    }

    /**
     * @param ServiceOptionInterface $serviceOption
     * @return $this
     */
    public function setServiceOption(ServiceOptionInterface $serviceOption)
    {
        $this->relationships[self::RELATIONSHIP_SERVICE_OPTION]['data'] = $serviceOption;

        return $this;
    }

    /**
     * @return ServiceOptionInterface
     */
    public function getServiceOption()
    {
        return $this->relationships[self::RELATIONSHIP_SERVICE_OPTION]['data'];
    }
}
