<?php

namespace MyParcelCom\ApiSdk\Resources\Proxy;

use DateTime;
use MyParcelCom\ApiSdk\Resources\Interfaces\AddressInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceProxyInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShopInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;
use MyParcelCom\ApiSdk\Resources\Traits\ProxiesResource;

class ShopProxy implements ShopInterface, ResourceProxyInterface
{
    use JsonSerializable;
    use ProxiesResource;

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_SHOP;

    /**
     * Set the identifier for this file.
     *
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->getResource()->setName($name);

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getResource()->getName();
    }

    /**
     * @param string $website
     * @return $this
     */
    public function setWebsite($website)
    {
        $this->getResource()->setWebsite($website);

        return $this;
    }

    /**
     * @return string
     */
    public function getWebsite()
    {
        return $this->getResource()->getWebsite();
    }

    /**
     * @param AddressInterface $billingAddress
     * @return $this
     */
    public function setBillingAddress(AddressInterface $billingAddress)
    {
        $this->getResource()->setBillingAddress($billingAddress);

        return $this;
    }

    /**
     * @return AddressInterface
     */
    public function getBillingAddress()
    {
        return $this->getResource()->getBillingAddress();
    }

    /**
     * @param AddressInterface $senderAddress
     * @return $this
     */
    public function setSenderAddress(AddressInterface $senderAddress)
    {
        $this->getResource()->setSenderAddress($senderAddress);

        return $this;
    }

    /**
     * @return AddressInterface
     */
    public function getSenderAddress()
    {
        return $this->getResource()->getSenderAddress();
    }

    /**
     * @param AddressInterface $returnAddress
     * @return $this
     */
    public function setReturnAddress(AddressInterface $returnAddress)
    {
        $this->getResource()->setReturnAddress($returnAddress);

        return $this;
    }

    /**
     * @return AddressInterface
     */
    public function getReturnAddress()
    {
        return $this->getResource()->getReturnAddress();
    }

    /**
     * @param int|DateTime $time
     * @return $this
     */
    public function setCreatedAt($time)
    {
        $this->getResource()->setCreatedAt($time);

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->getResource()->getCreatedAt();
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

        return $this->arrayValuesToArray($values);
    }
}
