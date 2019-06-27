<?php

namespace MyParcelCom\ApiSdk;

use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\Collection\CollectionInterface;
use MyParcelCom\ApiSdk\Exceptions\MyParcelComException;
use MyParcelCom\ApiSdk\Http\Exceptions\RequestException;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShopInterface;
use Psr\Http\Message\ResponseInterface;

interface MyParcelComApiInterface
{
    const PATH_CARRIERS = '/carriers';
    const PATH_FILES_ID = '/files/{file_id}';
    const PATH_PUDO_LOCATIONS = '/carriers/{carrier_id}/pickup-dropoff-locations/{country_code}/{postal_code}';
    const PATH_REGIONS = '/regions';
    const PATH_SERVICES = '/services';
    const PATH_SERVICE_RATES = '/service-rates';
    const PATH_SHIPMENTS = '/shipments';
    const PATH_SHIPMENT_STATUSES = '/shipments/{shipment_id}/statuses';
    const PATH_SHOPS = '/shops';

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
     * Get an array with all the regions. An array of
     * filters can be provided to limit the results to
     * a subset. Available filters:
     * - country_code
     * - region_code
     * - postal_code
     *
     * @see https://docs.myparcel.com/api/resources/regions/#parameters
     *
     * @param array $filters
     * @return CollectionInterface
     */
    public function getRegions($filters = []);

    /**
     * Get all the carriers from the API.
     *
     * @throws MyParcelComException
     * @return CollectionInterface
     */
    public function getCarriers();

    /**
     * Get the pick up/drop off locations around a given location. If no carrier
     * is given, pick up locations for all available carriers will be used.
     *
     * @param string                $countryCode
     * @param string                $postalCode
     * @param string|null           $streetName
     * @param string|null           $streetNumber
     * @param CarrierInterface|null $specificCarrier
     * @param bool                  $onlyActiveContracts
     * @return CollectionInterface
     */
    public function getPickUpDropOffLocations(
        $countryCode,
        $postalCode,
        $streetName = null,
        $streetNumber = null,
        CarrierInterface $specificCarrier = null,
        $onlyActiveContracts = true
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
     * Retrieves service rates based on the set filters.
     * Available filters are: service, contract and weight.
     *
     * @param array $filters
     * @return CollectionInterface
     */
    public function getServiceRates(array $filters = []);

    /**
     * Retrieves service rates based on the shipment.
     * The shipment needs to have a recipient/sender_address and a weight set.
     *
     * @param ShipmentInterface $shipment
     * @return CollectionInterface
     */
    public function getServiceRatesForShipment(ShipmentInterface $shipment);

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
     * @return ResponseInterface
     * @throws RequestException
     */
    public function doRequest($uri, $method = 'get', array $body = [], array $headers = [], $ttl = self::TTL_10MIN);
}
