<?php

namespace MyParcelCom\ApiSdk\Resources;

use DateTime;
use MyParcelCom\ApiSdk\Resources\Interfaces\AddressInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShopInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;

class Shop implements ShopInterface
{
    use JsonSerializable;

    const ATTRIBUTE_NAME = 'name';
    const ATTRIBUTE_WEBSITE = 'website';
    const ATTRIBUTE_BILLING_ADDRESS = 'billing_address';
    const ATTRIBUTE_SENDER_ADDRESS = 'sender_address';
    const ATTRIBUTE_RETURN_ADDRESS = 'return_address';
    const ATTRIBUTE_CREATED_AT = 'created_at';

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_SHOP;

    /** @var array */
    private $attributes = [
        self::ATTRIBUTE_NAME            => null,
        self::ATTRIBUTE_WEBSITE         => null,
        self::ATTRIBUTE_BILLING_ADDRESS => null,
        self::ATTRIBUTE_SENDER_ADDRESS  => null,
        self::ATTRIBUTE_RETURN_ADDRESS  => null,
        self::ATTRIBUTE_CREATED_AT      => null,
    ];

    /** @var array */
    private $relationships = [];

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
    public function setWebsite($website)
    {
        $this->attributes[self::ATTRIBUTE_WEBSITE] = $website;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsite()
    {
        return $this->attributes[self::ATTRIBUTE_WEBSITE];
    }

    /**
     * {@inheritdoc}
     */
    public function setBillingAddress(AddressInterface $billingAddress)
    {
        $this->attributes[self::ATTRIBUTE_BILLING_ADDRESS] = $billingAddress;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingAddress()
    {
        return $this->attributes[self::ATTRIBUTE_BILLING_ADDRESS];
    }

    /**
     * {@inheritdoc}
     */
    public function setSenderAddress(AddressInterface $senderAddress)
    {
        $this->attributes[self::ATTRIBUTE_SENDER_ADDRESS] = $senderAddress;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSenderAddress()
    {
        return $this->attributes[self::ATTRIBUTE_SENDER_ADDRESS];
    }

    /**
     * {@inheritdoc}
     */
    public function setReturnAddress(AddressInterface $returnAddress)
    {
        $this->attributes[self::ATTRIBUTE_RETURN_ADDRESS] = $returnAddress;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReturnAddress()
    {
        return $this->attributes[self::ATTRIBUTE_RETURN_ADDRESS];
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($time)
    {
        if (is_int($time)) {
            $this->attributes[self::ATTRIBUTE_CREATED_AT] = $time;
        } elseif ($time instanceof DateTime) {
            $this->attributes[self::ATTRIBUTE_CREATED_AT] = $time->getTimestamp();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return (new DateTime())->setTimestamp($this->attributes[self::ATTRIBUTE_CREATED_AT]);
    }
}
