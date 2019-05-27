<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Resources\Interfaces\RegionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class Region implements RegionInterface
{
    use JsonSerializable;

    const ATTRIBUTE_COUNTRY_CODE = 'country_code';
    const ATTRIBUTE_REGION_CODE = 'region_code';
    const ATTRIBUTE_CURRENCY = 'currency';
    const ATTRIBUTE_NAME = 'name';
    const ATTRIBUTE_CATEGORY = 'category';

    const RELATIONSHIP_PARENT = 'parent';

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_REGION;

    /** @var array */
    private $attributes = [
        self::ATTRIBUTE_COUNTRY_CODE => null,
        self::ATTRIBUTE_REGION_CODE  => null,
        self::ATTRIBUTE_CURRENCY     => null,
        self::ATTRIBUTE_NAME         => null,
    ];

    /** @var array */
    private $relationships = [
        self::RELATIONSHIP_PARENT => [
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
    public function setCountryCode($countryCode)
    {
        $this->attributes[self::ATTRIBUTE_COUNTRY_CODE] = $countryCode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryCode()
    {
        return $this->attributes[self::ATTRIBUTE_COUNTRY_CODE];
    }

    /**
     * {@inheritdoc}
     */
    public function setRegionCode($regionCode)
    {
        $this->attributes[self::ATTRIBUTE_REGION_CODE] = $regionCode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRegionCode()
    {
        return $this->attributes[self::ATTRIBUTE_REGION_CODE];
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrency($currency)
    {
        $this->attributes[self::ATTRIBUTE_CURRENCY] = $currency;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrency()
    {
        return $this->attributes[self::ATTRIBUTE_CURRENCY];
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
    public function setParent(RegionInterface $parentRegion)
    {
        $this->relationships[self::RELATIONSHIP_PARENT]['data'] = $parentRegion;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->relationships[self::RELATIONSHIP_PARENT]['data'];
    }
}
