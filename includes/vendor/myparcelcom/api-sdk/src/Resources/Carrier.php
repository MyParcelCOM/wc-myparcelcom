<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class Carrier implements CarrierInterface
{
    use JsonSerializable {
        jsonSerialize as private serialize;
    }

    const ATTRIBUTE_NAME = 'name';
    const ATTRIBUTE_CODE = 'code';
    const ATTRIBUTE_CREDENTIALS_FORMAT = 'credentials_format';

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_CARRIER;

    /** @var array */
    private $attributes = [
        self::ATTRIBUTE_NAME               => null,
        self::ATTRIBUTE_CODE               => null,
        self::ATTRIBUTE_CREDENTIALS_FORMAT => [],
    ];

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
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
     * @param array $format
     * @return $this
     */
    public function setCredentialsFormat(array $format)
    {
        $this->attributes[self::ATTRIBUTE_CREDENTIALS_FORMAT] = $format;

        return $this;
    }

    /**
     * @return array
     */
    public function getCredentialsFormat()
    {
        return $this->attributes[self::ATTRIBUTE_CREDENTIALS_FORMAT];
    }

    /**
     * This function puts all object properties in an array and returns it.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        // The 'credentials_format' can have camelCased properties, which get
        // changed to snake_case by the jsonSerialize() method. So ro prevent
        // that, we unset it and then reset it after serialization is done.
        $credentialsFormat = $this->attributes[self::ATTRIBUTE_CREDENTIALS_FORMAT];
        unset($this->attributes[self::ATTRIBUTE_CREDENTIALS_FORMAT]);

        $json = $this->serialize();

        if (!empty($credentialsFormat)) {
            $json['attributes'][self::ATTRIBUTE_CREDENTIALS_FORMAT] = $credentialsFormat;
        }

        return $json;
    }
}
