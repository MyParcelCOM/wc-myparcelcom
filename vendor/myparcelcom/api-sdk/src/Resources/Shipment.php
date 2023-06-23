<?php

namespace MyParcelCom\ApiSdk\Resources;

use DateTime;
use MyParcelCom\ApiSdk\Resources\Interfaces\AddressInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CustomsInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\FileInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\PhysicalPropertiesInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentItemInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentStatusInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShopInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;
use MyParcelCom\ApiSdk\Resources\Traits\ProcessIncludes;
use MyParcelCom\ApiSdk\Utils\DateUtils;

class Shipment implements ShipmentInterface
{
    use JsonSerializable;
    use ProcessIncludes;

    const ATTRIBUTE_BARCODE = 'barcode';
    const ATTRIBUTE_TRACKING_CODE = 'tracking_code';
    const ATTRIBUTE_TRACKING_URL = 'tracking_url';
    const ATTRIBUTE_CHANNEL = 'channel';
    const ATTRIBUTE_DESCRIPTION = 'description';
    const ATTRIBUTE_CUSTOMER_REFERENCE = 'customer_reference';
    const ATTRIBUTE_AMOUNT = 'amount';
    const ATTRIBUTE_PRICE = 'price';
    const ATTRIBUTE_CURRENCY = 'currency';
    const ATTRIBUTE_PHYSICAL_PROPERTIES = 'physical_properties';
    const ATTRIBUTE_RECIPIENT_ADDRESS = 'recipient_address';
    const ATTRIBUTE_RECIPIENT_TAX_NUMBER = 'recipient_tax_number';
    const ATTRIBUTE_RECIPIENT_TAX_IDENTIFICATION_NUMBERS = 'recipient_tax_identification_numbers';
    const ATTRIBUTE_SENDER_ADDRESS = 'sender_address';
    const ATTRIBUTE_SENDER_TAX_NUMBER = 'sender_tax_number';
    const ATTRIBUTE_SENDER_TAX_IDENTIFICATION_NUMBERS = 'sender_tax_identification_numbers';
    const ATTRIBUTE_RETURN_ADDRESS = 'return_address';
    const ATTRIBUTE_PICKUP = 'pickup_location';
    const ATTRIBUTE_PICKUP_CODE = 'code';
    const ATTRIBUTE_PICKUP_ADDRESS = 'address';
    const ATTRIBUTE_CUSTOMS = 'customs';
    const ATTRIBUTE_ITEMS = 'items';
    const ATTRIBUTE_REGISTER_AT = 'register_at';
    const ATTRIBUTE_TOTAL_VALUE = 'total_value';
    const ATTRIBUTE_TAGS = 'tags';

    const RELATIONSHIP_CONTRACT = 'contract';
    const RELATIONSHIP_FILES = 'files';
    const RELATIONSHIP_SERVICE = 'service';
    const RELATIONSHIP_SERVICE_OPTIONS = 'service_options';
    const RELATIONSHIP_STATUS = 'shipment_status';
    const RELATIONSHIP_SHOP = 'shop';

    const META_LABEL_MIME_TYPE = 'label_mime_type';
    const META_SERVICE_CODE = 'service_code';

    const INCLUDES = [
        ResourceInterface::TYPE_CONTRACT        => self::RELATIONSHIP_CONTRACT,
        ResourceInterface::TYPE_FILE            => self::RELATIONSHIP_FILES,
        ResourceInterface::TYPE_SERVICE         => self::RELATIONSHIP_SERVICE,
        ResourceInterface::TYPE_SERVICE_OPTION  => self::RELATIONSHIP_SERVICE_OPTIONS,
        ResourceInterface::TYPE_SHIPMENT_STATUS => self::RELATIONSHIP_STATUS,
        ResourceInterface::TYPE_SHOP            => self::RELATIONSHIP_SHOP,
    ];

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_SHIPMENT;

    /** @var ShipmentStatusInterface[] */
    private $statusHistory;

    /** @var callable */
    private $statusHistoryCallback;

    /** @var array */
    private $attributes = [
        self::ATTRIBUTE_BARCODE                              => null,
        self::ATTRIBUTE_TRACKING_CODE                        => null,
        self::ATTRIBUTE_TRACKING_URL                         => null,
        self::ATTRIBUTE_CHANNEL                              => null,
        self::ATTRIBUTE_DESCRIPTION                          => null,
        self::ATTRIBUTE_CUSTOMER_REFERENCE                   => null,
        self::ATTRIBUTE_PRICE                                => null,
        self::ATTRIBUTE_PHYSICAL_PROPERTIES                  => null,
        self::ATTRIBUTE_RECIPIENT_ADDRESS                    => null,
        self::ATTRIBUTE_RECIPIENT_TAX_NUMBER                 => null,
        self::ATTRIBUTE_RECIPIENT_TAX_IDENTIFICATION_NUMBERS => null,
        self::ATTRIBUTE_SENDER_ADDRESS                       => null,
        self::ATTRIBUTE_SENDER_TAX_NUMBER                    => null,
        self::ATTRIBUTE_SENDER_TAX_IDENTIFICATION_NUMBERS    => null,
        self::ATTRIBUTE_RETURN_ADDRESS                       => null,
        self::ATTRIBUTE_PICKUP                               => null,
        self::ATTRIBUTE_CUSTOMS                              => null,
        self::ATTRIBUTE_ITEMS                                => null,
        self::ATTRIBUTE_REGISTER_AT                          => null,
        self::ATTRIBUTE_TOTAL_VALUE                          => [
            'amount'   => null,
            'currency' => null,
        ],
        self::ATTRIBUTE_TAGS                                 => null,
    ];

    /** @var array */
    private $relationships = [
        self::RELATIONSHIP_SHOP            => [
            'data' => null,
        ],
        self::RELATIONSHIP_STATUS          => [
            'data' => null,
        ],
        self::RELATIONSHIP_SERVICE_OPTIONS => [
            'data' => [],
        ],
        self::RELATIONSHIP_FILES           => [
            'data' => [],
        ],
        self::RELATIONSHIP_SERVICE         => [
            'data' => null,
        ],
        self::RELATIONSHIP_CONTRACT        => [
            'data' => null,
        ],
    ];

    /** @var array */
    private $meta = [
        self::META_LABEL_MIME_TYPE => FileInterface::MIME_TYPE_PDF,
        self::META_SERVICE_CODE    => null,
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
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * {@inheritdoc}
     */
    public function setRecipientAddress(AddressInterface $recipientAddress)
    {
        $this->attributes[self::ATTRIBUTE_RECIPIENT_ADDRESS] = $recipientAddress;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipientAddress()
    {
        return $this->attributes[self::ATTRIBUTE_RECIPIENT_ADDRESS];
    }

    /**
     * {@inheritdoc}
     * @deprecated
     */
    public function setRecipientTaxNumber($recipientTaxNumber)
    {
        $this->attributes[self::ATTRIBUTE_RECIPIENT_TAX_NUMBER] = $recipientTaxNumber;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @deprecated
     */
    public function getRecipientTaxNumber()
    {
        return $this->attributes[self::ATTRIBUTE_RECIPIENT_TAX_NUMBER];
    }

    /**
     * {@inheritdoc}
     */
    public function setRecipientTaxIdentificationNumbers(array $taxIdentificationNumbers)
    {
        $this->attributes[self::ATTRIBUTE_RECIPIENT_TAX_IDENTIFICATION_NUMBERS] = [];

        array_walk($taxIdentificationNumbers, function (TaxIdentificationNumber $taxIdentificationNumber) {
            $this->addRecipientTaxIdentificationNumber($taxIdentificationNumber);
        });

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addRecipientTaxIdentificationNumber(TaxIdentificationNumber $taxIdentificationNumber)
    {
        $this->attributes[self::ATTRIBUTE_RECIPIENT_TAX_IDENTIFICATION_NUMBERS][] = $taxIdentificationNumber;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipientTaxIdentificationNumbers()
    {
        return $this->attributes[self::ATTRIBUTE_RECIPIENT_TAX_IDENTIFICATION_NUMBERS];
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
     * @deprecated
     */
    public function setSenderTaxNumber($senderTaxNumber)
    {
        $this->attributes[self::ATTRIBUTE_SENDER_TAX_NUMBER] = $senderTaxNumber;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @deprecated
     */
    public function getSenderTaxNumber()
    {
        return $this->attributes[self::ATTRIBUTE_SENDER_TAX_NUMBER];
    }

    /**
     * {@inheritdoc}
     */
    public function setSenderTaxIdentificationNumbers(array $taxIdentificationNumbers)
    {
        $this->attributes[self::ATTRIBUTE_SENDER_TAX_IDENTIFICATION_NUMBERS] = [];

        array_walk($taxIdentificationNumbers, function (TaxIdentificationNumber $taxIdentificationNumber) {
            $this->addSenderTaxIdentificationNumber($taxIdentificationNumber);
        });

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addSenderTaxIdentificationNumber(TaxIdentificationNumber $taxIdentificationNumber)
    {
        $this->attributes[self::ATTRIBUTE_SENDER_TAX_IDENTIFICATION_NUMBERS][] = $taxIdentificationNumber;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSenderTaxIdentificationNumbers()
    {
        return $this->attributes[self::ATTRIBUTE_SENDER_TAX_IDENTIFICATION_NUMBERS];
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
    public function setPickupLocationCode($pickupLocationCode)
    {
        $this->attributes[self::ATTRIBUTE_PICKUP][self::ATTRIBUTE_PICKUP_CODE] = $pickupLocationCode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPickupLocationCode()
    {
        return isset($this->attributes[self::ATTRIBUTE_PICKUP][self::ATTRIBUTE_PICKUP_CODE])
            ? $this->attributes[self::ATTRIBUTE_PICKUP][self::ATTRIBUTE_PICKUP_CODE]
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setPickupLocationAddress(AddressInterface $pickupLocationAddress)
    {
        $this->attributes[self::ATTRIBUTE_PICKUP][self::ATTRIBUTE_PICKUP_ADDRESS] = $pickupLocationAddress;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPickupLocationAddress()
    {
        return isset($this->attributes[self::ATTRIBUTE_PICKUP][self::ATTRIBUTE_PICKUP_ADDRESS])
            ? $this->attributes[self::ATTRIBUTE_PICKUP][self::ATTRIBUTE_PICKUP_ADDRESS]
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setChannel($channel)
    {
        $this->attributes[self::ATTRIBUTE_CHANNEL] = $channel;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannel()
    {
        return $this->attributes[self::ATTRIBUTE_CHANNEL];
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->attributes[self::ATTRIBUTE_DESCRIPTION] = $description;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->attributes[self::ATTRIBUTE_DESCRIPTION];
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerReference($customerReference)
    {
        $this->attributes[self::ATTRIBUTE_CUSTOMER_REFERENCE] = $customerReference;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerReference()
    {
        return $this->attributes[self::ATTRIBUTE_CUSTOMER_REFERENCE];
    }

    /**
     * {@inheritdoc}
     */
    public function setPrice($price)
    {
        $this->attributes[self::ATTRIBUTE_PRICE][self::ATTRIBUTE_AMOUNT] = (int) $price;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return isset($this->attributes[self::ATTRIBUTE_PRICE][self::ATTRIBUTE_AMOUNT])
            ? $this->attributes[self::ATTRIBUTE_PRICE][self::ATTRIBUTE_AMOUNT]
            : null;
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
        return isset($this->attributes[self::ATTRIBUTE_PRICE][self::ATTRIBUTE_CURRENCY])
            ? $this->attributes[self::ATTRIBUTE_PRICE][self::ATTRIBUTE_CURRENCY]
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setBarcode($barcode)
    {
        $this->attributes[self::ATTRIBUTE_BARCODE] = $barcode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBarcode()
    {
        return $this->attributes[self::ATTRIBUTE_BARCODE];
    }

    /**
     * {@inheritdoc}
     */
    public function setTrackingCode($trackingCode)
    {
        $this->attributes[self::ATTRIBUTE_TRACKING_CODE] = $trackingCode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTrackingCode()
    {
        return $this->attributes[self::ATTRIBUTE_TRACKING_CODE];
    }

    /**
     * {@inheritdoc}
     */
    public function setTrackingUrl($trackingUrl)
    {
        $this->attributes[self::ATTRIBUTE_TRACKING_URL] = $trackingUrl;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTrackingUrl()
    {
        return $this->attributes[self::ATTRIBUTE_TRACKING_URL];
    }

    /**
     * {@inheritdoc}
     * @deprecated Use Shipment::getPhysicalProperties()->setWeight() instead.
     */
    public function setWeight($weight, $unit = PhysicalPropertiesInterface::WEIGHT_GRAM)
    {
        if ($this->getPhysicalProperties() === null) {
            $this->setPhysicalProperties(new PhysicalProperties());
        }
        $this->getPhysicalProperties()->setWeight($weight, $unit);

        return $this;
    }

    /**
     * {@inheritdoc}
     * @deprecated Use Shipment::getPhysicalProperties()->getWeight() instead.
     */
    public function getWeight($unit = PhysicalPropertiesInterface::WEIGHT_GRAM)
    {
        if ($this->getPhysicalProperties() === null) {
            $this->setPhysicalProperties(new PhysicalProperties());
        }

        return $this->getPhysicalProperties()->getWeight($unit);
    }

    /**
     * {@inheritdoc}
     */
    public function setPhysicalProperties(PhysicalPropertiesInterface $physicalProperties)
    {
        $this->attributes[self::ATTRIBUTE_PHYSICAL_PROPERTIES] = $physicalProperties;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhysicalProperties()
    {
        return $this->attributes[self::ATTRIBUTE_PHYSICAL_PROPERTIES];
    }

    /**
     * {@inheritdoc}
     */
    public function setVolumetricWeight($volumetricWeight)
    {
        if ($this->getPhysicalProperties() === null) {
            $this->setPhysicalProperties(new PhysicalProperties());
        }
        $this->getPhysicalProperties()->setVolumetricWeight($volumetricWeight);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVolumetricWeight()
    {
        if ($this->getPhysicalProperties() === null) {
            $this->setPhysicalProperties(new PhysicalProperties());
        }

        return $this->getPhysicalProperties()->getVolumetricWeight();
    }

    /**
     * {@inheritdoc}
     */
    public function setShop(ShopInterface $shop)
    {
        $this->relationships[self::RELATIONSHIP_SHOP]['data'] = $shop;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShop()
    {
        return $this->relationships[self::RELATIONSHIP_SHOP]['data'];
    }

    /**
     * {@inheritdoc}
     */
    public function setServiceOptions(array $options)
    {
        $this->relationships[self::RELATIONSHIP_SERVICE_OPTIONS]['data'] = [];

        array_walk($options, function ($option) {
            $this->addServiceOption($option);
        });

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addServiceOption(ServiceOptionInterface $option)
    {
        $this->relationships[self::RELATIONSHIP_SERVICE_OPTIONS]['data'][] = $option;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceOptions()
    {
        return $this->relationships[self::RELATIONSHIP_SERVICE_OPTIONS]['data'];
    }

    /**
     * {@inheritdoc}
     */
    public function setFiles(array $files)
    {
        $this->relationships[self::RELATIONSHIP_FILES]['data'] = [];

        array_walk($files, function ($file) {
            $this->addFile($file);
        });

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFile(FileInterface $file)
    {
        $this->relationships[self::RELATIONSHIP_FILES]['data'][] = $file;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFiles($type = null)
    {
        if ($type === null) {
            return $this->relationships[self::RELATIONSHIP_FILES]['data'];
        }

        return array_filter($this->relationships[self::RELATIONSHIP_FILES]['data'], function (FileInterface $file) use ($type) {
            return $file->getDocumentType() === $type;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function setShipmentStatus(ShipmentStatusInterface $status)
    {
        $this->relationships[self::RELATIONSHIP_STATUS]['data'] = $status;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShipmentStatus()
    {
        return $this->relationships[self::RELATIONSHIP_STATUS]['data'];
    }

    /**
     * {@inheritdoc}
     */
    public function setStatusHistory(array $statuses)
    {
        $this->statusHistory = $statuses;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusHistory()
    {
        if (!isset($this->statusHistory) && isset($this->statusHistoryCallback)) {
            $this->setStatusHistory(call_user_func($this->statusHistoryCallback));
        }

        return $this->statusHistory;
    }

    /**
     * Set the callback to use when retrieving the status history.
     *
     * @param callable $callback
     * @return $this
     */
    public function setStatusHistoryCallback(callable $callback)
    {
        $this->statusHistoryCallback = $callback;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCustoms(CustomsInterface $customs)
    {
        $this->attributes[self::ATTRIBUTE_CUSTOMS] = $customs;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustoms()
    {
        return $this->attributes[self::ATTRIBUTE_CUSTOMS];
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        return $this->attributes[self::ATTRIBUTE_ITEMS];
    }

    /**
     * {@inheritdoc}
     */
    public function addItem(ShipmentItemInterface $item)
    {
        $this->attributes[self::ATTRIBUTE_ITEMS][] = $item;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setItems(array $items)
    {
        $this->attributes[self::ATTRIBUTE_ITEMS] = [];

        array_walk($items, function (ShipmentItemInterface $item) {
            $this->addItem($item);
        });

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRegisterAt($registerAt)
    {
        $this->attributes[self::ATTRIBUTE_REGISTER_AT] = DateUtils::toTimestamp($registerAt);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRegisterAt()
    {
        return isset($this->attributes[self::ATTRIBUTE_REGISTER_AT])
            ? (new DateTime())->setTimestamp($this->attributes[self::ATTRIBUTE_REGISTER_AT])
            : null;
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
     * {@inheritDoc}
     */
    public function setTotalValueAmount($totalValueAmount)
    {
        $this->attributes[self::ATTRIBUTE_TOTAL_VALUE]['amount'] = $totalValueAmount;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalValueAmount()
    {
        return $this->attributes[self::ATTRIBUTE_TOTAL_VALUE]['amount'];
    }

    /**
     * {@inheritDoc}
     */
    public function setTotalValueCurrency($totalValueCurrency)
    {
        $this->attributes[self::ATTRIBUTE_TOTAL_VALUE]['currency'] = $totalValueCurrency;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalValueCurrency()
    {
        return $this->attributes[self::ATTRIBUTE_TOTAL_VALUE]['currency'];
    }

    /**
     * {@inheritdoc}
     */
    public function setServiceCode($serviceCode)
    {
        $this->meta[self::META_SERVICE_CODE] = $serviceCode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceCode()
    {
        return $this->meta[self::META_SERVICE_CODE];
    }

    /**
     * {@inheritDoc}
     */
    public function setTags(array $tags)
    {
        $this->attributes[self::ATTRIBUTE_TAGS] = $tags;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addTag($tag)
    {
        $this->attributes[self::ATTRIBUTE_TAGS][] = $tag;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTags()
    {
        return $this->attributes[self::ATTRIBUTE_TAGS];
    }

    /**
     * {@inheritDoc}
     */
    public function clearTags()
    {
        $this->attributes[self::ATTRIBUTE_TAGS] = null;

        return $this;
    }

    /**
     * Supported values are FileInterface::MIME_TYPE_PDF or FileInterface::MIME_TYPE_ZPL
     * @param $labelMimeType
     * @return $this
     */
    public function setLabelMimeType($labelMimeType)
    {
        $this->meta[self::META_LABEL_MIME_TYPE] = $labelMimeType;

        return $this;
    }
}
