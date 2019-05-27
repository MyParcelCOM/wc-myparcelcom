<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\StatusInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class Status implements StatusInterface
{
    use JsonSerializable;

    const ATTRIBUTE_CODE = 'code';
    const ATTRIBUTE_RESOURCE_TYPE = 'resource_type';
    const ATTRIBUTE_LEVEL = 'level';
    const ATTRIBUTE_NAME = 'name';
    const ATTRIBUTE_DESCRIPTION = 'description';

    const META_CODE = 'carrier_status_code';
    const META_DESCRIPTION = 'carrier_status_description';
    const META_TIMESTAMP = 'carrier_timestamp';
    const META_RESOURCE_DATA = 'resource_data';

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_STATUS;

    /** @var array */
    private $attributes = [
        self::ATTRIBUTE_CODE          => null,
        self::ATTRIBUTE_RESOURCE_TYPE => null,
        self::ATTRIBUTE_LEVEL         => null,
        self::ATTRIBUTE_NAME          => null,
        self::ATTRIBUTE_DESCRIPTION   => null,
    ];

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->attributes[self::ATTRIBUTE_CODE] = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->attributes[self::ATTRIBUTE_CODE];
    }

    /**
     * @param string $resourceType
     * @return $this
     */
    public function setResourceType($resourceType)
    {
        $this->attributes[self::ATTRIBUTE_RESOURCE_TYPE] = $resourceType;

        return $this;
    }

    /**
     * @return string
     */
    public function getResourceType()
    {
        return $this->attributes[self::ATTRIBUTE_RESOURCE_TYPE];
    }

    /**
     * @param string $level
     * @return $this
     */
    public function setLevel($level)
    {
        $this->attributes[self::ATTRIBUTE_LEVEL] = $level;

        return $this;
    }

    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->attributes[self::ATTRIBUTE_LEVEL];
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->attributes[self::ATTRIBUTE_NAME] = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->attributes[self::ATTRIBUTE_NAME];
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->attributes[self::ATTRIBUTE_DESCRIPTION] = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->attributes[self::ATTRIBUTE_DESCRIPTION];
    }
}
