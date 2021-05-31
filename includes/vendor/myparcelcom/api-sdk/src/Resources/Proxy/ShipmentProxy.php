<?php

namespace MyParcelCom\ApiSdk\Resources\Proxy;

use DateTime;
use MyParcelCom\ApiSdk\Resources\Interfaces\AddressInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CustomsInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\FileInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\PhysicalPropertiesInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceProxyInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentItemInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentStatusInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShopInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;
use MyParcelCom\ApiSdk\Resources\Traits\ProxiesResource;

class ShipmentProxy implements ShipmentInterface, ResourceProxyInterface
{
    use JsonSerializable;
    use ProxiesResource;

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_SHIPMENT;

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
     * @return array
     */
    public function getMeta()
    {
        return $this->getResource()->getMeta();
    }

    /**
     * @param AddressInterface $recipientAddress
     * @return $this
     */
    public function setRecipientAddress(AddressInterface $recipientAddress)
    {
        $this->getResource()->setRecipientAddress($recipientAddress);

        return $this;
    }

    /**
     * @return AddressInterface
     */
    public function getRecipientAddress()
    {
        return $this->getResource()->getRecipientAddress();
    }

    /**
     * @param string $recipientTaxNumber
     * @return $this
     */
    public function setRecipientTaxNumber($recipientTaxNumber)
    {
        $this->getResource()->setRecipientTaxNumber($recipientTaxNumber);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRecipientTaxNumber()
    {
        return $this->getResource()->getRecipientTaxNumber();
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
     * @param string|null $senderTaxNumber
     * @return $this
     */
    public function setSenderTaxNumber($senderTaxNumber)
    {
        $this->getResource()->setSenderTaxNumber($senderTaxNumber);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSenderTaxNumber()
    {
        return $this->getResource()->getSenderTaxNumber();
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
     * @param string $pickupLocationCode
     * @return $this
     */
    public function setPickupLocationCode($pickupLocationCode)
    {
        $this->getResource()->setPickupLocationCode($pickupLocationCode);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPickupLocationCode()
    {
        return $this->getResource()->getPickupLocationCode();
    }

    /**
     * @param AddressInterface $pickupLocationAddress
     * @return $this
     */
    public function setPickupLocationAddress(AddressInterface $pickupLocationAddress)
    {
        $this->getResource()->setPickupLocationAddress($pickupLocationAddress);

        return $this;
    }

    /**
     * @return AddressInterface|null
     */
    public function getPickupLocationAddress()
    {
        return $this->getResource()->getPickupLocationAddress();
    }

    /**
     * @param string $channel
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->getResource()->setChannel($channel);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getChannel()
    {
        return $this->getResource()->getChannel();
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->getResource()->setDescription($description);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->getResource()->getDescription();
    }

    /**
     * @param string $customerReference
     * @return $this
     */
    public function setCustomerReference($customerReference)
    {
        $this->getResource()->setCustomerReference($customerReference);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCustomerReference()
    {
        return $this->getResource()->getCustomerReference();
    }

    /**
     * @param int $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->getResource()->setPrice($price);

        return $this;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->getResource()->getPrice();
    }

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->getResource()->setCurrency($currency);

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->getResource()->getCurrency();
    }

    /**
     * @param string $barcode
     * @return $this
     */
    public function setBarcode($barcode)
    {
        $this->getResource()->setBarcode($barcode);

        return $this;
    }

    /**
     * @return string
     */
    public function getBarcode()
    {
        return $this->getResource()->getBarcode();
    }

    /**
     * @param string $trackingCode
     * @return $this
     */
    public function setTrackingCode($trackingCode)
    {
        $this->getResource()->setTrackingCode($trackingCode);

        return $this;
    }

    /**
     * @return string
     */
    public function getTrackingCode()
    {
        return $this->getResource()->getTrackingCode();
    }

    /**
     * @param string $trackingUrl
     * @return $this
     */
    public function setTrackingUrl($trackingUrl)
    {
        $this->getResource()->setTrackingUrl($trackingUrl);

        return $this;
    }

    /**
     * @return string
     */
    public function getTrackingUrl()
    {
        return $this->getResource()->getTrackingUrl();
    }

    /**
     * @param int    $weight
     * @param string $unit
     * @return $this
     */
    public function setWeight($weight, $unit = PhysicalPropertiesInterface::WEIGHT_GRAM)
    {
        $this->getResource()->setWeight($weight, $unit);

        return $this;
    }

    /**
     * @param string $unit
     * @return int
     */
    public function getWeight($unit = PhysicalPropertiesInterface::WEIGHT_GRAM)
    {
        return $this->getResource()->getWeight($unit);
    }

    /**
     * @param ShopInterface $shop
     * @return $this
     */
    public function setShop(ShopInterface $shop)
    {
        $this->getResource()->setShop($shop);

        return $this;
    }

    /**
     * @return ShopInterface
     */
    public function getShop()
    {
        return $this->getResource()->getShop();
    }

    /**
     * @param ServiceOptionInterface[] $options
     * @return $this
     */
    public function setServiceOptions(array $options)
    {
        $this->getResource()->setServiceOptions($options);

        return $this;
    }

    /**
     * @param ServiceOptionInterface $option
     * @return $this
     */
    public function addServiceOption(ServiceOptionInterface $option)
    {
        $this->getResource()->addServiceOption($option);

        return $this;
    }

    /**
     * @return ServiceOptionInterface[]
     */
    public function getServiceOptions()
    {
        return $this->getResource()->getServiceOptions();
    }

    /**
     * @param PhysicalPropertiesInterface $physicalProperties
     * @return $this
     */
    public function setPhysicalProperties(PhysicalPropertiesInterface $physicalProperties)
    {
        $this->getResource()->setPhysicalProperties($physicalProperties);

        return $this;
    }

    /**
     * @return PhysicalPropertiesInterface|null
     */
    public function getPhysicalProperties()
    {
        return $this->getResource()->getPhysicalProperties();
    }

    /**
     * @param int $volumetricWeight
     * @return $this
     */
    public function setVolumetricWeight($volumetricWeight)
    {
        $this->getResource()->getPhysicalProperties()->setVolumetricWeight($volumetricWeight);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getVolumetricWeight()
    {
        return $this->getResource()->getPhysicalProperties()->getVolumetricWeight();
    }

    /**
     * @param FileInterface[] $files
     * @return $this
     */
    public function setFiles(array $files)
    {
        $this->getResource()->setFiles($files);

        return $this;
    }

    /**
     * @param FileInterface $file
     * @return $this
     */
    public function addFile(FileInterface $file)
    {
        $this->getResource()->addFile($file);

        return $this;
    }

    /**
     * @param string|null $type
     * @return FileInterface[]
     */
    public function getFiles($type = null)
    {
        return $this->getResource()->getFiles($type);
    }

    /**
     * @param ShipmentStatusInterface $status
     * @return $this
     */
    public function setShipmentStatus(ShipmentStatusInterface $status)
    {
        $this->getResource()->setShipmentStatus($status);

        return $this;
    }

    /**
     * @return ShipmentStatusInterface
     */
    public function getShipmentStatus()
    {
        return $this->getResource()->getShipmentStatus();
    }

    /**
     * @param CustomsInterface $customs
     * @return $this
     */
    public function setCustoms(CustomsInterface $customs)
    {
        $this->getResource()->setCustoms($customs);

        return $this;
    }

    /**
     * @return CustomsInterface
     */
    public function getCustoms()
    {
        return $this->getResource()->getCustoms();
    }

    /**
     * @param ShipmentStatusInterface[] $statuses
     * @return $this
     */
    public function setStatusHistory(array $statuses)
    {
        $this->getResource()->setStatusHistory($statuses);

        return $this;
    }

    /**
     * @return ShipmentStatusInterface[]
     */
    public function getStatusHistory()
    {
        return $this->getResource()->getStatusHistory();
    }

    /**
     * @param ShipmentItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items)
    {
        $this->getResource()->setItems($items);

        return $this;
    }

    /**
     * @param ShipmentItemInterface $item
     * @return $this
     */
    public function addItem(ShipmentItemInterface $item)
    {
        $this->getResource()->addItem($item);

        return $this;
    }

    /**
     * @return ShipmentItemInterface[]
     */
    public function getItems()
    {
        return $this->getResource()->getItems();
    }

    /**
     * @param DateTime|int|string $registerAt
     * @return $this
     */
    public function setRegisterAt($registerAt)
    {
        $this->getResource()->setRegisterAt($registerAt);

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getRegisterAt()
    {
        return $this->getResource()->getRegisterAt();
    }

    /**
     * {@inheritdoc}
     */
    public function setService(ServiceInterface $service)
    {
        $this->getResource()->setService($service);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getService()
    {
        return $this->getResource()->getService();
    }

    /**
     * {@inheritdoc}
     */
    public function setContract(ContractInterface $contract)
    {
        $this->getResource()->setContract($contract);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContract()
    {
        return $this->getResource()->getContract();
    }

    /**
     * {@inheritDoc}
     */
    public function setTotalValueAmount($totalValueAmount)
    {
        $this->getResource()->setTotalValueAmount($totalValueAmount);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalValueAmount()
    {
        return $this->getResource()->getTotalValueAmount();
    }

    /**
     * {@inheritDoc}
     */
    public function setTotalValueCurrency($totalValueCurrency)
    {
        $this->getResource()->setTotalValueCurrency($totalValueCurrency);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalValueCurrency()
    {
        return $this->getResource()->getTotalValueCurrency();
    }

    /**
     * {@inheritDoc}
     */
    public function setServiceCode($serviceCode)
    {
        $this->getResource()->setServiceCode($serviceCode);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getServiceCode()
    {
        return $this->getResource()->getServiceCode();
    }

    /**
     * {@inheritDoc}
     */
    public function setTags(array $tags)
    {
        return $this->getResource()->setTags($tags);
    }

    /**
     * {@inheritDoc}
     */
    public function addTag($tag)
    {
        return $this->getResource()->addTag($tag);
    }

    /**
     * {@inheritDoc}
     */
    public function getTags()
    {
        return $this->getResource()->getTags();
    }

    /**
     * {@inheritDoc}
     */
    public function clearTags()
    {
        return $this->getResource()->clearTags();
    }

    /**
     * {@inheritDoc}
     */
    public function setLabelMimeType($labelMimeType)
    {
        return $this->getResource()->setLabelMimeType($labelMimeType);
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
