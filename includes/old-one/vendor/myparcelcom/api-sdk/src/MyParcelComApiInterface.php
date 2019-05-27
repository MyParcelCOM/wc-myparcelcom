<?php

namespace MyParcelCom\ApiSdk;

use GuzzleHttp\Promise\PromiseInterface;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\Collection\CollectionInterface;
use MyParcelCom\ApiSdk\Exceptions\MyParcelComException;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShopInterface;

interface MyParcelComApiInterface
{
    const PATH_CARRIERS = '/v1/carriers';
    const PATH_FILES_ID = '/v1/files/{file_id}';
    const PATH_PUDO_LOCATIONS = '/v1/carriers/{carrier_id}/pickup-dropoff-locations/{country_code}/{postal_code}';
    const PATH_REGIONS = '/v1/regions';
    const PATH_SERVICES = '/v1/services';
    const PATH_SERVICE_CONTRACTS = '/v1/services/{service_id}/contracts';
    const PATH_CARRIER_SERVICE_CONTRACTS = '/v1/service-contracts/?filter[carrier_contract]={carrier_contract_id}';
    const PATH_SHIPMENTS = '/v1/shipments';
    const PATH_SHIPMENT_STATUSES = '/v1/shipments/{shipment_id}/statuses';
    const PATH_SHOPS = '/v1/shops';

    const TTL_10MIN = 600;
    const TTL_WEEK = 604800;
    const TTL_MONTH = 2592000;

    /**
     * Authenticate to the API using the given authenticator.
     *
     * @param AuthenticatorInterface $authenticator
     * @throws MyParcelComException
     * @return $this
     */
    public function authenticate(AuthenticatorInterface $authenticator);

    /**
     * Get an array with all the regions, optionally regions can be filtered by
     * country code and region code.
     *
     * @param string|null $countryCode
     * @param string|null $regionCode
     * @return CollectionInterface
     */
    public function getRegions($countryCode = null, $regionCode = null);

    /**
     * Get all the carriers from the API.
     *
     * @throws MyParcelComException
     * @return CollectionInterface
     */
    public function getCarriers();

    /**
     * Get the pick up/drop off locations around a given location. If no carrier
     * is given, the default carrier is used.
     *
     * @param string                $countryCode
     * @param string                $postalCode
     * @param string|null           $streetName
     * @param string|null           $streetNumber
     * @param CarrierInterface|null $specificCarrier
     * @return CollectionInterface
     */
    public function getPickUpDropOffLocations(
        $countryCode,
        $postalCode,
        $streetName = null,
        $streetNumber = null,
        CarrierInterface $specificCarrier = null
    );

    /**
     * Get the shops from the API.
     *
     * @throws MyParcelComException
     * @return CollectionInterface
     */
    public function getShops();

    /**
     * Get the default shop that will be used when interacting with the API and
     * no specific shop has been set.
     *
     * @throws MyParcelComException
     * @return ShopInterface
     */
    public function getDefaultShop();

    /**
     * Get all services that can be used for given shipment. If no shipment is
     * provided, all available services are returned.
     *
     * @param ShipmentInterface|null $shipment
     * @throws MyParcelComException
     * @return CollectionInterface
     */
    public function getServices(ShipmentInterface $shipment = null);

    /**
     * Get all the services that are available for the given carrier.
     *
     * @param CarrierInterface $carrier
     * @throws MyParcelComException
     * @return CollectionInterface
     */
    public function getServicesForCarrier(CarrierInterface $carrier);

    /**
     * Get shipments for a given shop. If no shop is given the default shop is
     * used.
     *
     * @param ShopInterface|null $shop
     * @throws MyParcelComException
     * @return CollectionInterface
     */
    public function getShipments(ShopInterface $shop = null);

    /**
     * Get a specific shipment from the API.
     *
     * @param string $id
     * @throws MyParcelComException
     * @return ShipmentInterface
     */
    public function getShipment($id);

    /**
     * Creates a given shipment or updates it depending on if the id is already set.
     * It returns the just created or updated version of the shipment.
     * When certain properties for a new shipment are not set, defaults should be
     * used. When no default value is available, an exception should be thrown.
     *
     * @param ShipmentInterface $shipment
     * @throws MyParcelComException
     * @return ShipmentInterface
     */
    public function saveShipment(ShipmentInterface $shipment);

    /**
     * Update the given shipment and returns the updated version of the shipment.
     *
     * @param ShipmentInterface $shipment
     * @throws MyParcelComException
     * @return ShipmentInterface
     */
    public function updateShipment(ShipmentInterface $shipment);

    /**
     * Creates a given shipment and returns the created version of the shipment.
     * When certain properties on the shipment are not set, defaults should be
     * used. When no default value is available, an exception should be thrown.
     *
     * @param ShipmentInterface $shipment
     * @throws MyParcelComException
     * @return ShipmentInterface
     */
    public function createShipment(ShipmentInterface $shipment);

    /**
     * Get the resource of given type with given id.
     *
     * @param string $resourceType
     * @param string $id
     * @throws MyParcelComException
     * @return ResourceInterface
     */
    public function getResourceById($resourceType, $id);

    /**
     * Get an array of all the resources from given uri.
     *
     * @param string $uri
     * @return ResourceInterface[]
     */
    public function getResourcesFromUri($uri);

    /**
     * Do an async request to given uri on the API.
     *
     * @param string $uri
     * @param string $method
     * @param array  $body
     * @param array  $headers
     * @param int    $ttl
     * @return PromiseInterface
     */
    public function doRequest($uri, $method = 'get', array $body = [], array $headers = [], $ttl = self::TTL_10MIN);
}
