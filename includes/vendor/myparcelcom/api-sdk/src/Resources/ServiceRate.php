<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Resources\Interfaces\ContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceRateInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class ServiceRate implements ServiceRateInterface
{
    use JsonSerializable;

    const ATTRIBUTE_PRICE = 'price';
    const ATTRIBUTE_CURRENCY = 'currency';
    const ATTRIBUTE_AMOUNT = 'amount';
    const ATTRIBUTE_STEP_PRICE = 'step_price';
    const ATTRIBUTE_STEP_SIZE = 'step_size';
    const ATTRIBUTE_WEIGHT_MIN = 'weight_min';
    const ATTRIBUTE_WEIGHT_MAX = 'weight_max';
    const ATTRIBUTE_WIDTH_MAX = 'width_max';
    const ATTRIBUTE_LENGTH_MAX = 'length_max';
    const ATTRIBUTE_HEIGHT_MAX = 'height_max';
    const ATTRIBUTE_VOLUME_MAX = 'volume_max';

    const RELATIONSHIP_SERVICE = 'service';
    const RELATIONSHIP_CONTRACT = 'contract';
    const RELATIONSHIP_SERVICE_OPTIONS = 'service_options';

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_SERVICE_RATE;

    private $attributes = [
        self::ATTRIBUTE_PRICE      => [
            self::ATTRIBUTE_CURRENCY => null,
            self::ATTRIBUTE_AMOUNT   => null,
        ],
        self::ATTRIBUTE_STEP_PRICE => [
            self::ATTRIBUTE_CURRENCY => null,
            self::ATTRIBUTE_AMOUNT   => null,
        ],
        self::ATTRIBUTE_STEP_SIZE  => null,
        self::ATTRIBUTE_WEIGHT_MIN => null,
        self::ATTRIBUTE_WEIGHT_MAX => null,
        self::ATTRIBUTE_WIDTH_MAX  => null,
        self::ATTRIBUTE_LENGTH_MAX => null,
        self::ATTRIBUTE_HEIGHT_MAX => null,
        self::ATTRIBUTE_VOLUME_MAX => null,
    ];

    private $relationships = [
        self::RELATIONSHIP_SERVICE         => [
            'data' => null,
        ],
        self::RELATIONSHIP_CONTRACT        => [
            'data' => null,
        ],
        self::RELATIONSHIP_SERVICE_OPTIONS => [
            'data' => [],
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
    public function setType($type)
    {
        $this->type = $type;

        return $this;
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
    public function setWeightMin($weightMin)
    {
        $this->attributes[self::ATTRIBUTE_WEIGHT_MIN] = $weightMin;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWeightMin()
    {
        return $this->attributes[self::ATTRIBUTE_WEIGHT_MIN];
    }

    /**
     * {@inheritdoc}
     */
    public function setWeightMax($weightMax)
    {
        $this->attributes[self::ATTRIBUTE_WEIGHT_MAX] = $weightMax;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWeightMax()
    {
        return $this->attributes[self::ATTRIBUTE_WEIGHT_MAX];
    }

    /**
     * {@inheritdoc}
     */
    public function setLengthMax($lengthMax)
    {
        $this->attributes[self::ATTRIBUTE_LENGTH_MAX] = $lengthMax;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLengthMax()
    {
        return $this->attributes[self::ATTRIBUTE_LENGTH_MAX];
    }

    /**
     * {@inheritdoc}
     */
    public function setHeightMax($heightMax)
    {
        $this->attributes[self::ATTRIBUTE_HEIGHT_MAX] = $heightMax;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeightMax()
    {
        return $this->attributes[self::ATTRIBUTE_HEIGHT_MAX];
    }

    /**
     * {@inheritdoc}
     */
    public function setWidthMax($widthMax)
    {
        $this->attributes[self::ATTRIBUTE_WIDTH_MAX] = $widthMax;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidthMax()
    {
        return $this->attributes[self::ATTRIBUTE_WIDTH_MAX];
    }

    /**
     * {@inheritdoc}
     */
    public function setVolumeMax($volumeMax)
    {
        $this->attributes[self::ATTRIBUTE_VOLUME_MAX] = $volumeMax;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVolumeMax()
    {
        return $this->attributes[self::ATTRIBUTE_VOLUME_MAX];
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
        return $this->attributes[self::ATTRIBUTE_PRICE][self::ATTRIBUTE_CURRENCY];
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
        return $this->attributes[self::ATTRIBUTE_PRICE][self::ATTRIBUTE_AMOUNT];
    }

    /**
     * {@inheritdoc}
     */
    public function setStepPrice($stepPrice)
    {
        $this->attributes[self::ATTRIBUTE_STEP_PRICE][self::ATTRIBUTE_AMOUNT] = $stepPrice;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStepPrice()
    {
        return $this->attributes[self::ATTRIBUTE_STEP_PRICE][self::ATTRIBUTE_AMOUNT];
    }

    /**
     * {@inheritdoc}
     */
    public function setStepSize($stepSize)
    {
        $this->attributes[self::ATTRIBUTE_STEP_SIZE] = $stepSize;

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
    public function setService(ServiceInterface $service)
    {
        $this->relationships[self::RELATIONSHIP_SERVICE]['data'] = $service;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getService()
    {
        return $this->relationships[self::RELATIONSHIP_SERVICE]['data'];
    }

    /**
     * {@inheritdoc}
     */
    public function setContract(ContractInterface $contract)
    {
        $this->relationships[self::RELATIONSHIP_CONTRACT]['data'] = $contract;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContract()
    {
        return $this->relationships[self::RELATIONSHIP_CONTRACT]['data'];
    }

    /**
     * {@inheritdoc}
     */
    public function setServiceOptions($serviceOptions)
    {
        $this->relationships[self::RELATIONSHIP_SERVICE_OPTIONS]['data'] = [];

        foreach ($serviceOptions as $serviceOption) {
            $this->addServiceOption($serviceOption);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addServiceOption(ServiceOptionInterface $serviceOption)
    {
        $this->relationships[self::RELATIONSHIP_SERVICE_OPTIONS]['data'][] = $serviceOption;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceOptions()
    {
        return $this->relationships[self::RELATIONSHIP_SERVICE_OPTIONS]['data'];
    }
}
