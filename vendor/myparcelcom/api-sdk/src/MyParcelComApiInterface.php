<?php

namespace MyParcelCom\ApiSdk;

use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\Collection\CollectionInterface;
use MyParcelCom\ApiSdk\Exceptions\MyParcelComException;
use MyParcelCom\ApiSdk\Http\Exceptions\RequestException;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceRateInterface;
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

    const HEADER_IDEMPOTENCY_KEY = 'Idempotency-Key';

    const TTL_NO_CACHE = 0;
    const TTL_10MIN = 600;
    const TTL_WEEK = 604800;
    const TTL_MONTH = 2592000;

    /**
     * Authenticate to the API using the given authenticator.
     *
     * @param AuthenticatorInterface $authenticator
     * @return $this
     * @throws MyParcelComException
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
     * @param int   $ttl Cache time to live (in seconds)
     * @return CollectionInterface
     */
    public function getRegions($filters = [], $ttl = self::TTL_10MIN);

    /**
     * Get all the carriers from the API.
     *
     * @param int $ttl Cache time to live (in seconds)
     * @return CollectionInterface
     * @throws MyParcelComException
     */
    public function getCarriers($ttl = self::TTL_10MIN);

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
     * @param int                   $ttl Cache time to live (in seconds)
     * @return CollectionInterface
     */
    public function getPickUpDropOffLocations(
        $countryCode,
        $postalCode,
        $streetName = null,
        $streetNumber = null,
        CarrierInterface $specificCarrier = null,
        $onlyActiveContracts = true,
        $ttl = self::TTL_10MIN
    );

    /**
     * Get the shops from the API.
     *
     * @param int $ttl Cache time to live (in seconds)
     * @return CollectionInterface
     * @throws MyParcelComException
     */
    public function getShops($ttl = self::TTL_10MIN);

    /**
     * Get the default shop that will be used when interacting with the API and
     * no specific shop has been set.
     *
     * @param int $ttl Cache time to live (in seconds)
     * @return ShopInterface
     * @throws MyParcelComException
     */
    public function getDefaultShop($ttl = self::TTL_10MIN);

    /**
     * Get all services that can be used for given shipment. If no shipment is
     * provided, all available services are returned.
     *
     * @param ShipmentInterface|null $shipment
     * @param array                  $filters
     * @param int                    $ttl Cache time to live (in seconds)
     * @return CollectionInterface
     * @throws MyParcelComException
     */
    public function getServices(
        ShipmentInterface $shipment = null,
        array $filters = ['has_active_contract' => 'true'],
        $ttl = self::TTL_10MIN
    );

    /**
     * Get all the services that are available for the given carrier.
     *
     * @param CarrierInterface $carrier
     * @param int              $ttl Cache time to live (in seconds)
     * @return CollectionInterface
     * @throws MyParcelComException
     */
    public function getServicesForCarrier(CarrierInterface $carrier, $ttl = self::TTL_10MIN);

    /**
     * Retrieves service rates based on the set filters. Available filters are: service, contract and weight. Note that
     * this function could return service rates which are dynamic. Their price and availability depends on the shipment
     * data and requires communication with the carrier. This info can be retrieved using resolveDynamicServiceRates().
     *
     * @param array $filters
     * @param int   $ttl Cache time to live (in seconds)
     * @return CollectionInterface
     */
    public function getServiceRates(array $filters = ['has_active_contract' => 'true'], $ttl = self::TTL_10MIN);

    /**
     * Retrieves service rates based on the shipment.
     * The shipment needs to have a recipient/sender_address and a weight set.
     *
     * @param ShipmentInterface $shipment
     * @param int               $ttl Cache time to live (in seconds)
     * @return CollectionInterface
     */
    public function getServiceRatesForShipment(ShipmentInterface $shipment, $ttl = self::TTL_10MIN);

    /**
     * Retrieve dynamic rates (price / options / availability) from the carrier, based on the provided shipment data.
     * The shipment should have a service, contract, addresses, weight and sometimes dimensions are required as well.
     * If you have a ServiceRate which is_dynamic, you can pass it and its service and contract will be used instead.
     *
     * @param ShipmentInterface|array   $shipmentData
     * @param ServiceRateInterface|null $dynamicServiceRate
     * @return ServiceRateInterface[]
     * @throws RequestException
     */
    public function resolveDynamicServiceRates($shipmentData, $dynamicServiceRate = null);

    /**
     * Get shipments for a given shop. If no shop is given the default shop is used.
     *
     * @param ShopInterface|null $shop
     * @param int                $ttl Cache time to live (in seconds)
     * @return CollectionInterface
     * @throws MyParcelComException
     */
    public function getShipments(ShopInterface $shop = null, $ttl = self::TTL_NO_CACHE);

    /**
     * Get a specific shipment from the API.
     *
     * @param string $id
     * @param int    $ttl
     * @return ShipmentInterface
     * @throws MyParcelComException
     */
    public function getShipment($id, $ttl = null);

    /**
     * Creates a given shipment or updates it depending on if the id is already set.
     * It returns the just created or updated version of the shipment.
     * When certain properties for a new shipment are not set, defaults should be
     * used. When no default value is available, an exception should be thrown.
     *
     * @param ShipmentInterface $shipment
     * @return ShipmentInterface
     * @throws MyParcelComException
     */
    public function saveShipment(ShipmentInterface $shipment);

    /**
     * Update the given shipment and returns the updated version of the shipment.
     *
     * @param ShipmentInterface $shipment
     * @return ShipmentInterface
     * @throws MyParcelComException
     */
    public function updateShipment(ShipmentInterface $shipment);

    /**
     * Creates a given shipment and returns the created version of the shipment.
     * When certain properties on the shipment are not set, defaults should be
     * used. When no default value is available, an exception should be thrown.
     *
     * @param ShipmentInterface $shipment
     * @param string|null $idempotencyKey Use idempotency feature
     *                                    (see https://docs.myparcel.com/api/create-a-shipment/idempotency.html)
     * @return ShipmentInterface
     * @throws MyParcelComException
     */
    public function createShipment(ShipmentInterface $shipment, $idempotencyKey = null);

    /**
     * Get the resource of given type with given id.
     *
     * @param string $resourceType
     * @param string $id
     * @param int    $ttl
     * @return ResourceInterface
     * @throws MyParcelComException
     */
    public function getResourceById($resourceType, $id, $ttl = self::TTL_NO_CACHE);

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
    public function doRequest($uri, $method = 'get', array $body = [], array $headers = [], $ttl = self::TTL_NO_CACHE);
}
