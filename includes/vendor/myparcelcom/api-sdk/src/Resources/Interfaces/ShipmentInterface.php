<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

use DateTime;

interface ShipmentInterface extends ResourceInterface
{
    /**
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * @param AddressInterface $recipientAddress
     * @return $this
     */
    public function setRecipientAddress(AddressInterface $recipientAddress);

    /**
     * @return AddressInterface
     */
    public function getRecipientAddress();

    /**
     * @param string|null $recipientTaxNumber
     * @return $this
     */
    public function setRecipientTaxNumber($recipientTaxNumber);

    /**
     * @return string|null
     */
    public function getRecipientTaxNumber();

    /**
     * @param AddressInterface $senderAddress
     * @return $this
     */
    public function setSenderAddress(AddressInterface $senderAddress);

    /**
     * @return AddressInterface
     */
    public function getSenderAddress();

    /**
     * @param string|null $senderTaxNumber
     * @return $this
     */
    public function setSenderTaxNumber($senderTaxNumber);

    /**
     * @return string|null
     */
    public function getSenderTaxNumber();

    /**
     * @param AddressInterface $returnAddress
     * @return $this
     */
    public function setReturnAddress(AddressInterface $returnAddress);

    /**
     * @return AddressInterface
     */
    public function getReturnAddress();

    /**
     * @param string $pickupLocationCode
     * @return $this
     */
    public function setPickupLocationCode($pickupLocationCode);

    /**
     * @return string|null
     */
    public function getPickupLocationCode();

    /**
     * @param AddressInterface $pickupLocationAddress
     * @return $this
     */
    public function setPickupLocationAddress(AddressInterface $pickupLocationAddress);

    /**
     * @return AddressInterface|null
     */
    public function getPickupLocationAddress();

    /**
     * @param string $channel
     * @return $this
     */
    public function setChannel($channel);

    /**
     * @return string|null
     */
    public function getChannel();

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return string|null
     */
    public function getDescription();

    /**
     * @param string $customerReference
     * @return $this
     */
    public function setCustomerReference($customerReference);

    /**
     * @return string|null
     */
    public function getCustomerReference();

    /**
     * @param int $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * @return int
     */
    public function getPrice();

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency);

    /**
     * @return string
     */
    public function getCurrency();

    /**
     * @param string $barcode
     * @return $this
     */
    public function setBarcode($barcode);

    /**
     * @return string
     */
    public function getBarcode();

    /**
     * @param string $trackingCode
     * @return $this
     */
    public function setTrackingCode($trackingCode);

    /**
     * @return string
     */
    public function getTrackingCode();

    /**
     * @param string $trackingUrl
     * @return $this
     */
    public function setTrackingUrl($trackingUrl);

    /**
     * @return string
     */
    public function getTrackingUrl();

    /**
     * @param int    $weight
     * @param string $unit
     * @return $this
     * @deprecated Use Shipment::getPhysicalProperties()->setWeight() instead.
     */
    public function setWeight($weight, $unit = PhysicalPropertiesInterface::WEIGHT_GRAM);

    /**
     * @param string $unit
     * @return int
     * @deprecated Use Shipment::getPhysicalProperties()->getWeight() instead.
     */
    public function getWeight($unit = PhysicalPropertiesInterface::WEIGHT_GRAM);

    /**
     * @param ShopInterface $shop
     * @return $this
     */
    public function setShop(ShopInterface $shop);

    /**
     * @return ShopInterface
     */
    public function getShop();

    /**
     * @param ServiceOptionInterface[] $options
     * @return $this
     */
    public function setServiceOptions(array $options);

    /**
     * @param ServiceOptionInterface $option
     * @return $this
     */
    public function addServiceOption(ServiceOptionInterface $option);

    /**
     * @return ServiceOptionInterface[]
     */
    public function getServiceOptions();

    /**
     * @param PhysicalPropertiesInterface $physicalProperties
     * @return $this
     */
    public function setPhysicalProperties(PhysicalPropertiesInterface $physicalProperties);

    /**
     * @return PhysicalPropertiesInterface|null
     */
    public function getPhysicalProperties();

    /**
     * @return null|int
     */
    public function getVolumetricWeight();

    /**
     * @param int $volumetricWeight
     * @return $this
     */
    public function setVolumetricWeight($volumetricWeight);

    /**
     * @param FileInterface[] $files
     * @return $this
     */
    public function setFiles(array $files);

    /**
     * @param FileInterface $file
     * @return $this
     */
    public function addFile(FileInterface $file);

    /**
     * @param string|null $type
     * @return FileInterface[]
     */
    public function getFiles($type = null);

    /**
     * @param ShipmentStatusInterface $status
     * @return $this
     */
    public function setShipmentStatus(ShipmentStatusInterface $status);

    /**
     * @return ShipmentStatusInterface
     */
    public function getShipmentStatus();

    /**
     * @param ShipmentStatusInterface[] $statuses
     * @return $this
     */
    public function setStatusHistory(array $statuses);

    /**
     * @return ShipmentStatusInterface[]
     */
    public function getStatusHistory();

    /**
     * @param CustomsInterface $customs
     * @return $this
     */
    public function setCustoms(CustomsInterface $customs);

    /**
     * @return CustomsInterface
     */
    public function getCustoms();

    /**
     * @param ShipmentItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

    /**
     * @param ShipmentItemInterface $item
     * @return $this
     */
    public function addItem(ShipmentItemInterface $item);

    /**
     * @return ShipmentItemInterface[]
     */
    public function getItems();

    /**
     * Set the date and time that this shipment should be registered at.
     * This can either be a datetime string as specified by PHP, a unix timestamp
     * integer or a DateTime object.
     *
     * @see http://php.net/manual/en/datetime.formats.php
     *
     * @param DateTime|string|int $registerAt
     * @return $this
     */
    public function setRegisterAt($registerAt);

    /**
     * @return DateTime
     */
    public function getRegisterAt();

    /**
     * @param ServiceInterface $service
     * @return $this
     */
    public function setService(ServiceInterface $service);

    /**
     * @return ServiceInterface|null
     */
    public function getService();

    /**
     * @param ContractInterface $contract
     * @return $this
     */
    public function setContract(ContractInterface $contract);

    /**
     * @return ContractInterface|null
     */
    public function getContract();

    /**
     * @param int|null $totalValueAmount
     * @return $this
     */
    public function setTotalValueAmount($totalValueAmount);

    /**
     * @return int|null
     */
    public function getTotalValueAmount();

    /**
     * @param string|null $totalValueCurrency
     * @return $this
     */
    public function setTotalValueCurrency($totalValueCurrency);

    /**
     * @return string|null
     */
    public function getTotalValueCurrency();

    /**
     * @param string|null $serviceCode
     * @return $this
     */
    public function setServiceCode($serviceCode);

    /**
     * @return string|null
     */
    public function getServiceCode();

    /**
     * @param array $tags
     * @return $this
     */
    public function setTags(array $tags);

    /**
     * @param mixed $tag
     * @return $this
     */
    public function addTag($tag);

    /**
     * @return array|null
     */
    public function getTags();

    /**
     * @return $this
     */
    public function clearTags();
}
