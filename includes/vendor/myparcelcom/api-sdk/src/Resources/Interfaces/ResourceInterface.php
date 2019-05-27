<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

/**
 * Interface that should be implemented by each class that represents a resource
 * af the API.
 */
interface ResourceInterface extends \JsonSerializable
{
    const TYPE_CARRIER = 'carriers';
    const TYPE_CONTRACT = 'contracts';
    const TYPE_FILE = 'files';
    const TYPE_PUDO_LOCATION = 'pickup-dropoff-locations';
    const TYPE_REGION = 'regions';
    const TYPE_SERVICE = 'services';
    const TYPE_SERVICE_OPTION = 'service-options';
    const TYPE_SERVICE_RATE = 'service-rates';
    const TYPE_SHIPMENT = 'shipments';
    const TYPE_SHIPMENT_STATUS = 'shipment-statuses';
    const TYPE_SHOP = 'shops';
    const TYPE_STATUS = 'statuses';

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getType();
}
