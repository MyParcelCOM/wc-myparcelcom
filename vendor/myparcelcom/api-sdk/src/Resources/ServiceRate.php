<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Resources\Interfaces\ContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceRateInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;
use MyParcelCom\ApiSdk\Resources\Traits\ProcessIncludes;

class ServiceRate implements ServiceRateInterface
{
    use JsonSerializable;
    use ProcessIncludes;

    const ATTRIBUTE_PRICE = 'price';
    const ATTRIBUTE_FUEL_SURCHARGE = 'fuel_surcharge';
    const ATTRIBUTE_CURRENCY = 'currency';
    const ATTRIBUTE_AMOUNT = 'amount';
    const ATTRIBUTE_WEIGHT_MIN = 'weight_min';
    const ATTRIBUTE_WEIGHT_MAX = 'weight_max';
    const ATTRIBUTE_WEIGHT_BRACKET = 'weight_bracket';
    const ATTRIBUTE_WIDTH_MAX = 'width_max';
    const ATTRIBUTE_LENGTH_MAX = 'length_max';
    const ATTRIBUTE_HEIGHT_MAX = 'height_max';
    const ATTRIBUTE_VOLUME_MAX = 'volume_max';
    const ATTRIBUTE_VOLUMETRIC_WEIGHT_DIVISOR = 'volumetric_weight_divisor';
    const ATTRIBUTE_IS_DYNAMIC = 'is_dynamic';

    const RELATIONSHIP_SERVICE = 'service';
    const RELATIONSHIP_CONTRACT = 'contract';
    const RELATIONSHIP_SERVICE_OPTIONS = 'service_options';

    const META_BRACKET_PRICE = 'bracket_price';

    const INCLUDES = [
        ResourceInterface::TYPE_CONTRACT => self::RELATIONSHIP_CONTRACT,
        ResourceInterface::TYPE_SERVICE  => self::RELATIONSHIP_SERVICE,
    ];

    const WEIGHT_BRACKET_START = 'start';
    const WEIGHT_BRACKET_START_AMOUNT = 'start_amount';
    const WEIGHT_BRACKET_SIZE = 'size';
    const WEIGHT_BRACKET_SIZE_AMOUNT = 'size_amount';

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_SERVICE_RATE;

    private $attributes = [
        self::ATTRIBUTE_PRICE                     => [
            self::ATTRIBUTE_AMOUNT   => null,
            self::ATTRIBUTE_CURRENCY => null,
        ],
        self::ATTRIBUTE_FUEL_SURCHARGE            => [
            self::ATTRIBUTE_AMOUNT   => null,
            self::ATTRIBUTE_CURRENCY => null,
        ],
        self::ATTRIBUTE_WEIGHT_MIN                => null,
        self::ATTRIBUTE_WEIGHT_MAX                => null,
        self::ATTRIBUTE_WEIGHT_BRACKET            => [
            self::WEIGHT_BRACKET_START        => null,
            self::WEIGHT_BRACKET_START_AMOUNT => null,
            self::WEIGHT_BRACKET_SIZE         => null,
            self::WEIGHT_BRACKET_SIZE_AMOUNT  => null,
        ],
        self::ATTRIBUTE_WIDTH_MAX                 => null,
        self::ATTRIBUTE_LENGTH_MAX                => null,
        self::ATTRIBUTE_HEIGHT_MAX                => null,
        self::ATTRIBUTE_VOLUME_MAX                => null,
        self::ATTRIBUTE_VOLUMETRIC_WEIGHT_DIVISOR => null,
        self::ATTRIBUTE_IS_DYNAMIC                => null,
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

    /** @var array */
    private $meta = [
        self::META_BRACKET_PRICE => [
            self::ATTRIBUTE_AMOUNT   => null,
            self::ATTRIBUTE_CURRENCY => null,
        ],
    ];

    /** @var callable */
    private $resolveDynamicRateForShipmentCallback;

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

    public function setWeightBracket($weightBracket)
    {
        $this->attributes[self::ATTRIBUTE_WEIGHT_BRACKET] = $weightBracket;

        return $this;
    }

    public function getWeightBracket()
    {
        return $this->attributes[self::ATTRIBUTE_WEIGHT_BRACKET];
    }

    /**
     * @param int $bracketPrice
     * @return $this
     */
    public function setBracketPrice($bracketPrice)
    {
        $this->meta[self::META_BRACKET_PRICE][self::ATTRIBUTE_AMOUNT] = $bracketPrice;

        return $this;
    }

    /**
     * This will only return a value when this ServiceRate is retrieved for a shipment with a specific weight.
     * @return int|null
     */
    public function getBracketPrice()
    {
        return $this->meta[self::META_BRACKET_PRICE][self::ATTRIBUTE_AMOUNT];
    }

    /**
     * @param string $currency
     * @return $this
     */
    public function setBracketCurrency($currency)
    {
        $this->attributes[self::META_BRACKET_PRICE][self::ATTRIBUTE_CURRENCY] = $currency;

        return $this;
    }

    /**
     * This will only return a value when this ServiceRate is retrieved for a shipment with a specific weight.
     * @return string|null
     */
    public function getBracketCurrency()
    {
        return $this->attributes[self::META_BRACKET_PRICE][self::ATTRIBUTE_CURRENCY];
    }

    public function calculateBracketPrice($weight)
    {
        $weightBracket = $this->getWeightBracket();
        $price = $weightBracket[self::WEIGHT_BRACKET_START_AMOUNT];

        if ($price === null) {
            return null;
        }

        $remainingWeight = $weight - $weightBracket[self::WEIGHT_BRACKET_START];
        while ($remainingWeight > 0) {
            $price += $weightBracket[self::WEIGHT_BRACKET_SIZE_AMOUNT];
            $remainingWeight -= $weightBracket[self::WEIGHT_BRACKET_SIZE];
        }

        return $price;
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
     * @param int $volumetricWeightDivisor
     * @return $this
     */
    public function setVolumetricWeightDivisor($volumetricWeightDivisor)
    {
        $this->attributes[self::ATTRIBUTE_VOLUMETRIC_WEIGHT_DIVISOR] = $volumetricWeightDivisor;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getVolumetricWeightDivisor()
    {
        return $this->attributes[self::ATTRIBUTE_VOLUMETRIC_WEIGHT_DIVISOR];
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
    public function setFuelSurchargeAmount($amount)
    {
        $this->attributes[self::ATTRIBUTE_FUEL_SURCHARGE][self::ATTRIBUTE_AMOUNT] = $amount;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFuelSurchargeAmount()
    {
        return $this->attributes[self::ATTRIBUTE_FUEL_SURCHARGE][self::ATTRIBUTE_AMOUNT];
    }

    /**
     * {@inheritdoc}
     */
    public function setFuelSurchargeCurrency($currency)
    {
        $this->attributes[self::ATTRIBUTE_FUEL_SURCHARGE][self::ATTRIBUTE_CURRENCY] = $currency;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFuelSurchargeCurrency()
    {
        return $this->attributes[self::ATTRIBUTE_FUEL_SURCHARGE][self::ATTRIBUTE_CURRENCY];
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

    public function setIsDynamic($isDynamic)
    {
        $this->attributes[self::ATTRIBUTE_IS_DYNAMIC] = $isDynamic;

        return $this;
    }

    public function isDynamic()
    {
        return $this->attributes[self::ATTRIBUTE_IS_DYNAMIC];
    }

    public function resolveDynamicRateForShipment(ShipmentInterface $shipment)
    {
        if (isset($this->resolveDynamicRateForShipmentCallback)) {
            return call_user_func_array($this->resolveDynamicRateForShipmentCallback, [$shipment, $this]);
        }

        return $this;
    }

    public function setResolveDynamicRateForShipmentCallback(callable $callback)
    {
        $this->resolveDynamicRateForShipmentCallback = $callback;

        return $this;
    }
}
