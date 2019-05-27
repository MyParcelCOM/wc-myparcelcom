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
}
