<?php

namespace MyParcelCom\ApiSdk;

use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Request;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\Collection\ArrayCollection;
use MyParcelCom\ApiSdk\Collection\CollectionInterface;
use MyParcelCom\ApiSdk\Collection\RequestCollection;
use MyParcelCom\ApiSdk\Exceptions\InvalidResourceException;
use MyParcelCom\ApiSdk\Http\Contracts\HttpClient\RequestExceptionInterface;
use MyParcelCom\ApiSdk\Http\Exceptions\RequestException;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\FileInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceFactoryInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceProxyInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShopInterface;
use MyParcelCom\ApiSdk\Resources\ResourceFactory;
use MyParcelCom\ApiSdk\Resources\Service;
use MyParcelCom\ApiSdk\Resources\ServiceRate;
use MyParcelCom\ApiSdk\Resources\Shipment;
use MyParcelCom\ApiSdk\Shipments\ServiceMatcher;
use MyParcelCom\ApiSdk\Utils\UrlBuilder;
use MyParcelCom\ApiSdk\Validators\ShipmentValidator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Cache\Simple\FilesystemCache;

class MyParcelComApi implements MyParcelComApiInterface
{
    /** @var string */
    protected $apiUri;

    /** @var CacheInterface */
    protected $cache;

    /** @var ResourceFactoryInterface */
    protected $resourceFactory;

    /** @var AuthenticatorInterface */
    protected $authenticator;

    /** @var HttpClient */
    private $client;

    /** @var bool */
    private $authRetry = false;

    /** @var MyParcelComApi */
    private static $singleton;

    /**
     * Create an singleton instance of this class, which will be available in
     * subsequent calls to `getSingleton()`.
     *
     * @param AuthenticatorInterface        $authenticator
     * @param string                        $apiUri
     * @param HttpClient|null               $httpClient
     * @param CacheInterface|null           $cache
     * @param ResourceFactoryInterface|null $resourceFactory
     * @return MyParcelComApi
     */
    public static function createSingleton(
        AuthenticatorInterface $authenticator,
        $apiUri = 'https://sandbox-api.myparcel.com',
        HttpClient $httpClient = null,
        CacheInterface $cache = null,
        ResourceFactoryInterface $resourceFactory = null
    ) {
        return self::$singleton = (new self($apiUri, $httpClient, $cache, $resourceFactory))
            ->authenticate($authenticator);
    }

    /**
     * Get the singleton instance created.
     *
     * @return MyParcelComApi
     */
    public static function getSingleton()
    {
        return self::$singleton;
    }

    /**
     * Create an instance for the api with given uri. If no cache is given, the
     * filesystem is used for caching. If no resource factory is given, the
     * default factory is used.
     *
     * @param string                        $apiUri
     * @param HttpClient|null               $httpClient
     * @param CacheInterface|null           $cache
     * @param ResourceFactoryInterface|null $resourceFactory
     */
    public function __construct(
        $apiUri = 'https://sandbox-api.myparcel.com',
        HttpClient $httpClient = null,
        CacheInterface $cache = null,
        ResourceFactoryInterface $resourceFactory = null
    ) {
        if ($httpClient === null) {
            $httpClient = HttpClientDiscovery::find();
        }

        $this
            ->setHttpClient($httpClient)
            ->setApiUri($apiUri);

        // Either use the given cache or instantiate a new one that uses the filesystem temp directory as a cache.
        if (!$cache) {
            // Symfony 5.0.0 removed their PSR-16 cache classes. Their PSR-6 cache classes can be wrapped in Psr16Cache.
            if (class_exists('\Symfony\Component\Cache\Psr16Cache')) {
                $psr6Cache = new FilesystemAdapter('myparcelcom');
                $cache = new Psr16Cache($psr6Cache);
            } else {
                $cache = new FilesystemCache('myparcelcom');
            }
        }
        $this->setCache($cache);

        // Either use the given resource factory or instantiate a new one.
        $this->setResourceFactory($resourceFactory ?: new ResourceFactory());
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(AuthenticatorInterface $authenticator)
    {
        $this->authenticator = $authenticator;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRegions($filters = [], $ttl = self::TTL_10MIN)
    {
        $url = (new UrlBuilder($this->apiUri . self::PATH_REGIONS));

        // This method used to accept a $countryCode as the first argument and
        // a $regionCode as the second argument. We converted it to accept just
        // a $filters argument, but the logic below ensures backward compatibility.
        $functionArguments = func_get_args();
        if (count($functionArguments) > 0 && !is_array($functionArguments[0])) {
            $filters = [];
            $filters['country_code'] = $functionArguments[0];

            if (isset($functionArguments[1])) {
                $filters['region_code'] = $functionArguments[1];
            }
        }

        if (is_array($filters)) {
            foreach ($filters as $key => $value) {
                $url->addQuery(['filter[' . $key . ']' => $value]);
            }
        }

        $regions = $this->getRequestCollection($url->getUrl(), $ttl);

        if ($regions->count() > 0 || !isset($filters['region_code'])) {
            return $regions;
        }

        // Fallback to the country if the specific region is not in the API.
        $url->addQuery(['filter[region_code]' => null]);

        return $this->getRequestCollection($url->getUrl(), $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function getCarriers($ttl = self::TTL_10MIN)
    {
        return $this->getRequestCollection($this->apiUri . self::PATH_CARRIERS, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function getPickUpDropOffLocations(
        $countryCode,
        $postalCode,
        $streetName = null,
        $streetNumber = null,
        CarrierInterface $specificCarrier = null,
        $onlyActiveContracts = true,
        $ttl = self::TTL_10MIN
    ) {
        $carriers = $this->determineCarriersForPudoLocations($onlyActiveContracts, $specificCarrier);

        $uri = new UrlBuilder($this->apiUri
            . str_replace(
                [
                    '{country_code}',
                    '{postal_code}',
                ],
                [
                    $countryCode,
                    $postalCode,
                ],
                self::PATH_PUDO_LOCATIONS
            ));

        if ($streetName) {
            $uri->addQuery(['street' => $streetName]);
        }
        if ($streetNumber) {
            $uri->addQuery(['street_number' => $streetNumber]);
        }

        $pudoLocations = [];

        foreach ($carriers as $carrier) {
            $carrierUri = str_replace('{carrier_id}', $carrier->getId(), $uri->getUrl());

            try {
                $resources = $this->getResourcesArray($carrierUri, $ttl);
            } catch (RequestException $exception) {
                // When we are trying to fetch pudo locations for a specific
                // carrier, we want to be able to distinct between 'no results'
                // or 'something went wrong'. However, when we're not looking
                // for carrier specific pudo locations, we just want to show
                // pudo locations for the failing carrier as not available (null).
                if ($specificCarrier) {
                    throw $exception;
                }

                $resources = [];
            }

            if ($specificCarrier) {
                return new ArrayCollection($resources);
            }

            // When something fails while retrieving the locations
            // for a carrier, the locations of the other carriers should
            // still be returned. The failing carrier returns null.
            $pudoLocations[$carrier->getId()] = !empty($resources) ? new ArrayCollection($resources) : null;
        };

        return $pudoLocations;
    }

    /**
     * {@inheritdoc}
     */
    public function getShops($ttl = self::TTL_10MIN)
    {
        return $this->getRequestCollection($this->apiUri . self::PATH_SHOPS, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultShop($ttl = self::TTL_10MIN)
    {
        $shops = $this->getResourcesArray($this->apiUri . self::PATH_SHOPS, $ttl);

        // For now the oldest shop will be the default shop.
        usort($shops, function (ShopInterface $shopA, ShopInterface $shopB) {
            return $shopA->getCreatedAt()->getTimestamp() - $shopB->getCreatedAt()->getTimestamp();
        });

        return reset($shops);
    }

    /**
     * {@inheritdoc}
     */
    public function getServices(
        ShipmentInterface $shipment = null,
        array $filters = ['has_active_contract' => 'true'],
        $ttl = self::TTL_10MIN
    ) {
        $url = new UrlBuilder($this->apiUri . self::PATH_SERVICES);
        $url->addQuery($this->arrayToFilters($filters));

        if ($shipment === null) {
            return $this->getRequestCollection($url->getUrl(), $ttl);
        }

        if ($shipment->getSenderAddress() === null) {
            $shipment->setSenderAddress($this->getDefaultShop()->getReturnAddress());
        }
        if ($shipment->getRecipientAddress() === null) {
            throw new InvalidResourceException('Missing `recipient_address` on `shipments` resource');
        }
        if ($shipment->getSenderAddress() === null) {
            throw new InvalidResourceException('Missing `sender_address` on `shipments` resource');
        }

        $url->addQuery($this->arrayToFilters([
            'address_from' => array_filter([
                'country_code' => $shipment->getSenderAddress()->getCountryCode(),
                'state_code'   => $shipment->getSenderAddress()->getStateCode(),
                'postal_code'  => $shipment->getSenderAddress()->getPostalCode(),
            ]),
            'address_to'   => array_filter([
                'country_code' => $shipment->getRecipientAddress()->getCountryCode(),
                'state_code'   => $shipment->getRecipientAddress()->getStateCode(),
                'postal_code'  => $shipment->getRecipientAddress()->getPostalCode(),
            ]),
        ]));

        $services = $this->getResourcesArray($url->getUrl(), $ttl);

        $matcher = new ServiceMatcher();
        $services = array_values(array_filter($services, function (ServiceInterface $service) use ($shipment, $matcher) {
            return $matcher->matchesDeliveryMethod($shipment, $service);
        }));

        return new ArrayCollection($services);
    }

    /**
     * {@inheritdoc}
     */
    public function getServicesForCarrier(CarrierInterface $carrier, $ttl = self::TTL_10MIN)
    {
        $url = new UrlBuilder($this->apiUri . self::PATH_SERVICES);
        $url->addQuery($this->arrayToFilters([
            'has_active_contract' => 'true',
            'carrier'             => $carrier->getId(),
        ]));

        return $this->getRequestCollection($url->getUrl(), $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceRates(array $filters = ['has_active_contract' => 'true'], $ttl = self::TTL_10MIN)
    {
        $url = new UrlBuilder($this->apiUri . self::PATH_SERVICE_RATES);
        $url->addQuery($this->arrayToFilters($filters));

        return $this->getRequestCollection($url->getUrl(), $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceRatesForShipment(ShipmentInterface $shipment, $ttl = self::TTL_10MIN)
    {
        $services = $this->getServices($shipment, ['has_active_contract' => 'true'], $ttl);
        $serviceIds = [];
        foreach ($services as $service) {
            $serviceIds[] = $service->getId();
        }

        if (empty($serviceIds)) {
            return new ArrayCollection([]);
        }

        $url = new UrlBuilder($this->apiUri . self::PATH_SERVICE_RATES);
        $url->addQuery($this->arrayToFilters([
            'has_active_contract' => 'true',
            'weight'              => $shipment->getPhysicalProperties()->getWeight(),
            'volumetric_weight'   => $shipment->getPhysicalProperties()->getVolumetricWeight(),
            'service'             => implode(',', $serviceIds),
        ]));
        if ($shipment->getShop()) {
            $url->addQuery($this->arrayToFilters([
                'organization' => $shipment->getShop()->getOrganization()->getId(),
            ]));
        }
        // Include the services to avoid extra http requests when the result is looped with: $serviceRate->getService().
        $url->addQuery(['include' => 'contract,service']);

        /** @var ServiceRate[] $serviceRates */
        $serviceRates = $this->getRequestCollection($url->getUrl(), $ttl);

        $availableServiceRates = [];
        foreach ($serviceRates as $serviceRate) {
            if ($serviceRate->isDynamic()) {
                // Resolve the price and currency for service-rates with a dynamic price.
                try {
                    $serviceRates = $this->resolveDynamicServiceRates($shipment, $serviceRate);

                    if (!empty($serviceRates)) {
                        // Hydrate the service and contract resources to prevent additional API calls to retrieve these.
                        $serviceRates[0]->setService($serviceRate->getService());
                        $serviceRates[0]->setContract($serviceRate->getContract());
                        $availableServiceRates[] = $serviceRates[0];
                    }
                } catch (RequestException $exception) {
                    // If communicating with the carrier does not result in a service rate, this service is unavailable.
                }
            } elseif ($serviceRate->getBracketPrice()) {
                // Resolve the price and currency for service-rates with a bracket price (calculated by the API).
                $serviceRate->setPrice($serviceRate->getBracketPrice());
                $serviceRate->setCurrency($serviceRate->getBracketCurrency());
                $availableServiceRates[] = $serviceRate;
            } else {
                $availableServiceRates[] = $serviceRate;
            }
        }

        $shipmentOptionIds = array_map(function (ServiceOptionInterface $serviceOption) {
            return $serviceOption->getId();
        }, $shipment->getServiceOptions());

        $matchingServiceRates = [];
        foreach ($availableServiceRates as $serviceRate) {
            $serviceRateOptionIds = array_map(function (ServiceOptionInterface $serviceOption) {
                return $serviceOption->getId();
            }, $serviceRate->getServiceOptions());

            if (empty(array_diff($shipmentOptionIds, $serviceRateOptionIds))) {
                $matchingServiceRates[] = $serviceRate;
            }
        }

        return new ArrayCollection($matchingServiceRates);
    }

    /**
     * {@inheritdoc}
     */
    public function resolveDynamicServiceRates($shipmentData, $dynamicServiceRate = null)
    {
        $data = ($shipmentData instanceof ShipmentInterface) ? $shipmentData->jsonSerialize() : $shipmentData;

        if (!isset($data['relationships'])) {
            $data['relationships'] = [];
        }
        if (!isset($data['relationships']['shop'])) {
            $data['relationships']['shop'] = [
                'data' => [
                    'type' => ResourceInterface::TYPE_SHOP,
                    'id'   => $this->getDefaultShop()->getId(),
                ],
            ];
        }

        if ($dynamicServiceRate) {
            $data['relationships']['service'] = [
                'data' => [
                    'type' => ResourceInterface::TYPE_SERVICE,
                    'id'   => $dynamicServiceRate->getService()->getId(),
                ],
            ];
            $data['relationships']['contract'] = [
                'data' => [
                    'type' => ResourceInterface::TYPE_CONTRACT,
                    'id'   => $dynamicServiceRate->getContract()->getId(),
                ],
            ];
        }

        $response = $this->doRequest('/get-dynamic-service-rates', 'post', ['data' => $data], [
            AuthenticatorInterface::HEADER_ACCEPT => AuthenticatorInterface::MIME_TYPE_JSON,
        ]);
        $json = json_decode((string) $response->getBody(), true);
        $included = isset($json['included']) ? $json['included'] : null;

        return $this->jsonToResources($json['data'], $included);
    }

    /**
     * {@inheritdoc}
     */
    public function getShipments(ShopInterface $shop = null, $ttl = self::TTL_NO_CACHE)
    {
        $url = new UrlBuilder($this->apiUri . self::PATH_SHIPMENTS);

        if (isset($shop)) {
            $url->addQuery(['filter[shop]' => $shop->getId()]);
        }

        return $this->getRequestCollection($url->getUrl(), $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function getShipment($id, $ttl = self::TTL_NO_CACHE)
    {
        return $this->getResourceById(ResourceInterface::TYPE_SHIPMENT, $id, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function saveShipment(ShipmentInterface $shipment)
    {
        if ($shipment->getId()) {
            return $this->updateShipment($shipment);
        } else {
            return $this->createShipment($shipment);
        }
    }

    protected function populateShipmentWithDefaultsFromShop(ShipmentInterface $shipment)
    {
        // If no shop is set, use the default shop.
        if ($shipment->getShop() === null) {
            $shipment->setShop($this->getDefaultShop());
        }

        // If no sender address is set, use the sender address of the shop (or the return address of the shipment).
        $shop = $shipment->getShop();
        if ($shipment->getSenderAddress() === null) {
            $shipment->setSenderAddress(
                $shop->getSenderAddress()
                    ?: $shipment->getReturnAddress()
                    ?: $shop->getReturnAddress()
            );
        }
        // If no return address is set, use the return address of the shop (or the sender address of the shipment).
        if ($shipment->getReturnAddress() === null) {
            $shipment->setReturnAddress(
                $shop->getReturnAddress()
                    ?: $shipment->getSenderAddress()
            );
        }
    }

    public function validateShipment(ShipmentInterface $shipment)
    {
        $validator = new ShipmentValidator($shipment);

        if (!$validator->isValid()) {
            $exception = new InvalidResourceException(
                'This shipment contains invalid data. ' . implode('. ', $validator->getErrors()) . '.'
            );
            $exception->setErrors($validator->getErrors());

            throw $exception;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createShipment(ShipmentInterface $shipment, $idempotencyKey = null)
    {
        $this->populateShipmentWithDefaultsFromShop($shipment);
        $this->validateShipment($shipment);

        $headers = [];

        if ($idempotencyKey) {
            $headers[self::HEADER_IDEMPOTENCY_KEY] = $idempotencyKey;
        }

        return $this->postResource($shipment, $shipment->getMeta(), $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function updateShipment(ShipmentInterface $shipment)
    {
        if (!$shipment->getId()) {
            throw new InvalidResourceException(
                'Could not update shipment. This shipment does not have an id, use createShipment() to save it.'
            );
        }

        $this->validateShipment($shipment);

        return $this->patchResource($shipment, $shipment->getMeta());
    }

    /**
     * This function is similar to createShipment() but will immediately communicate the shipment to the carrier.
     * The carrier response is processed before your request is completed, so files and base64 data will be available.
     *
     * This removes the need to `poll` for files, but has some side effects (exceptions instead of registration-failed).
     * @see https://docs.myparcel.com/api/create-a-shipment.html#registering-your-shipment-with-the-carrier
     */
    public function createAndRegisterShipment(ShipmentInterface $shipment, $idempotencyKey = null)
    {
        $this->populateShipmentWithDefaultsFromShop($shipment);
        $this->validateShipment($shipment);

        $headers = [];

        if ($idempotencyKey) {
            $headers[self::HEADER_IDEMPOTENCY_KEY] = $idempotencyKey;
        }

        $response = $this->doRequest(
            $this->apiUri . '/registered-shipments?' . http_build_query(['include' => 'files']),
            'post',
            [
                'data' => $shipment,
                'meta' => $shipment->getMeta(),
            ],
            $this->authenticator->getAuthorizationHeader() + [
                AuthenticatorInterface::HEADER_ACCEPT => AuthenticatorInterface::MIME_TYPE_JSONAPI,
            ] + $headers
        );

        $json = json_decode($response->getBody(), true);

        /** @var Shipment $registeredShipment */
        $registeredShipment = $this->resourceFactory->create('shipments', $json['data']);
        $included = isset($json['included']) ? $json['included'] : [];
        $metaFiles = isset($json['meta']['files']) ? $json['meta']['files'] : [];

        if (!empty($included)) {
            $includedResources = $this->jsonToResources($included);
            $registeredShipment->processIncludedResources($includedResources);

            foreach ($registeredShipment->getFiles() as $file) {
                $format = $file->getFormats()[0];

                foreach ($metaFiles as $metaFile) {
                    if ($metaFile['document_type'] === $file->getDocumentType()
                        && $metaFile['mime_type'] === $format[FileInterface::FORMAT_MIME_TYPE]
                        && $metaFile['extension'] === $format[FileInterface::FORMAT_EXTENSION]
                    ) {
                        $file->setBase64Data($metaFile['contents'], $metaFile['mime_type']);
                    }
                }
            }
        }

        return $registeredShipment;
    }

    /**
     * Set the URI of the MyParcel.com API.
     *
     * @param string $apiUri
     * @return $this
     */
    public function setApiUri($apiUri)
    {
        // Remove trailing whitespace and a trailing slash.
        $this->apiUri = rtrim($apiUri, " \t\n\r\0\x0B/");

        return $this;
    }

    /**
     * Set the factory to use when creating resources.
     *
     * @param ResourceFactoryInterface $resourceFactory
     * @return $this
     */
    public function setResourceFactory(ResourceFactoryInterface $resourceFactory)
    {
        // Let this fetch the resources if the factory allows proxying of resources.
        if ($resourceFactory instanceof ResourceProxyInterface) {
            $resourceFactory->setMyParcelComApi($this);
        }

        $this->resourceFactory = $resourceFactory;

        return $this;
    }

    /**
     * Set the cache which will be used to store resources.
     *
     * @param CacheInterface $cache
     * @return $this
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Clear the cached resources and the authorization cache.
     *
     * @return $this
     */
    public function clearCache()
    {
        $this->cache->clear();
        $this->authenticator->clearCache();

        return $this;
    }

    /**
     * Set the HTTP client to use to connect to the api. Given
     * client must implement the PSR-18 client interface.
     *
     * @param HttpClient $client
     * @return $this
     */
    public function setHttpClient(HttpClient $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get the HTTP client.
     *
     * @return HttpClient
     */
    protected function getHttpClient()
    {
        return $this->client;
    }

    /**
     * Get a promise that will return an array with resources requested from
     * given uri. A time-to-live can be specified for how long this request
     * should be cached (defaults to 10 minutes).
     *
     * @param string $uri
     * @param int    $ttl
     * @return ResourceInterface[]
     * @throws RequestException
     */
    protected function getResourcesArray($uri, $ttl = self::TTL_10MIN)
    {
        $response = $this->doRequest($uri, 'get', [], [], $ttl);
        $json = json_decode((string) $response->getBody(), true);
        $included = isset($json['included']) ? $json['included'] : null;

        $resources = $this->jsonToResources($json['data'], $included);

        // If there is no next link, we don't have to retrieve any more data
        if (!isset($json['links']['next'])) {
            return $resources;
        }

        return array_merge($resources, $this->getResourcesArray($json['links']['next']));
    }

    /**
     * Get a collection of resources requested from the given uri.
     * A time-to-live can be specified for how long this request
     * should be cached (defaults to 10 minutes).
     *
     * @param string $uri
     * @param int    $ttl
     * @return CollectionInterface
     */
    protected function getRequestCollection($uri, $ttl = self::TTL_10MIN)
    {
        return new RequestCollection(function ($pageNumber, $pageSize) use ($uri, $ttl) {
            $url = (new UrlBuilder($uri))->addQuery([
                'page[number]' => $pageNumber,
                'page[size]'   => $pageSize,
            ])->getUrl();

            return $this->doRequest($url, 'get', [], [], $ttl);
        }, function ($data, $included = null) {
            return $this->jsonToResources($data, $included);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function doRequest($uri, $method = 'get', array $body = [], array $headers = [], $ttl = self::TTL_NO_CACHE)
    {
        if (strpos($uri, $this->apiUri) !== 0) {
            $uri = $this->apiUri . $uri;
        }
        $headers += $this->authenticator->getAuthorizationHeader() + [
                AuthenticatorInterface::HEADER_ACCEPT => AuthenticatorInterface::MIME_TYPE_JSONAPI,
            ];

        // Attempt to fetch a response from cache
        $cacheKey = sha1(join($headers) . $uri);
        if (($response = $this->cache->get($cacheKey)) && strtolower($method) === 'get') {
            return Message::parseResponse($response);
        }

        try {
            $request = $this->buildRequest($uri, $method, $body, $headers);
            $response = $this->client->sendRequest($request);

            // Store the response in cache
            if (strtolower($method) === 'get') {
                $this->cache->set($cacheKey, Message::toString($response), $ttl);
            }

            if ($response->getStatusCode() >= 300) {
                throw new RequestException($request, $response);
            }

            return $response;
        } catch (RequestException $requestException) {
            return $this->handleRequestException($requestException);
        }
    }

    /**
     * @param              $uri
     * @param string       $method
     * @param array|string $body
     * @param array        $headers
     * @return RequestInterface
     */
    private function buildRequest($uri, $method = 'GET', $body = '', array $headers = [])
    {
        if (is_array($body)) {
            $body = json_encode($body);
        }

        return new Request($method, $uri, $headers, $body);
    }

    /**
     * Convert the data from a json request to an array of resources.
     *
     * @param array      $json
     * @param array|null $included
     * @return array
     */
    protected function jsonToResources(array $json, $included = null)
    {
        $resources = [];

        if (isset($json['type'])) {
            $json = [$json];
        }

        foreach ($json as $resourceData) {
            $resource = $this->resourceFactory->create($resourceData['type'], $resourceData);

            if (isset($included)) {
                $includedResources = $this->jsonToResources($included);
                $resource->processIncludedResources($includedResources);
            }

            $resources[] = $resource;
        }

        return $resources;
    }

    /**
     * @param RequestExceptionInterface $exception
     * @return ResponseInterface
     * @throws RequestExceptionInterface
     */
    protected function handleRequestException(RequestExceptionInterface $exception)
    {
        $response = $exception->getResponse();
        if ($response->getStatusCode() !== 401 || $this->authRetry) {
            // TODO actually do something
            // echo (string)$exception->getRequest()->getUri();
            // echo (string)$exception->getResponse()->getBody();

            throw $exception;
        }

        $this->authRetry = true;
        $authHeaders = $this->authenticator->getAuthorizationHeader(true);

        $request = $exception->getRequest();

        $body = (string) $request->getBody();
        $jsonBody = $body
            ? json_decode($body, true)
            : [];

        return $this->doRequest(
            $request->getUri(),
            $request->getMethod(),
            $jsonBody,
            $authHeaders + $request->getHeaders()
        );
    }

    /**
     * @param string $resourceType
     * @param string $id
     * @param int    $ttl
     * @return ResourceInterface
     * @throws RequestException
     */
    public function getResourceById($resourceType, $id, $ttl = self::TTL_NO_CACHE)
    {
        $resources = $this->getResourcesArray(
            $this->getResourceUri($resourceType, $id),
            $ttl
        );

        return reset($resources);
    }

    /**
     * {@inheritdoc}
     */
    public function getResourcesFromUri($uri)
    {
        return $this->getResourcesArray($uri);
    }

    /**
     * Patch given resource and return the resource that was returned by the request.
     *
     * @param ResourceInterface $resource
     * @param array             $meta
     * @param array             $headers
     * @return ResourceInterface|null
     * @throws RequestException
     */
    protected function patchResource(ResourceInterface $resource, $meta = [], array $headers = [])
    {
        return $this->sendResource($resource, 'patch', $meta, $headers);
    }

    /**
     * Post given resource and return the resource that was returned by the request.
     *
     * @param ResourceInterface $resource
     * @param array             $meta
     * @param array             $headers
     * @return ResourceInterface|null
     * @throws RequestException
     */
    protected function postResource(ResourceInterface $resource, $meta = [], array $headers = [])
    {
        return $this->sendResource($resource, 'post', $meta, $headers);
    }

    /**
     * Send given resource to the API and return the resource that was returned.
     *
     * @param ResourceInterface $resource
     * @param string            $method
     * @param array             $meta
     * @param array             $headers
     * @return ResourceInterface|null
     * @throws RequestException
     */
    protected function sendResource(ResourceInterface $resource, $method = 'post', $meta = [], array $headers = [])
    {
        $response = $this->doRequest(
            $this->getResourceUri($resource->getType(), $resource->getId()),
            $method,
            array_filter([
                'data' => $resource,
                'meta' => array_filter($meta),
            ]),
            $this->authenticator->getAuthorizationHeader() + [
                AuthenticatorInterface::HEADER_ACCEPT => AuthenticatorInterface::MIME_TYPE_JSONAPI,
            ] + $headers
        );

        $json = json_decode($response->getBody(), true);
        $included = isset($json['included']) ? $json['included'] : null;
        $resources = $this->jsonToResources($json['data'], $included);

        return reset($resources);
    }

    /**
     * @param string      $resourceType
     * @param string|null $id
     * @return string
     */
    protected function getResourceUri($resourceType, $id = null)
    {
        return implode(
            '/',
            array_filter([
                $this->apiUri,
                $resourceType,
                $id,
            ])
        );
    }

    /**
     * Converts given array to a filter array usable as query params.
     *
     * @param array $array
     * @return array
     */
    private function arrayToFilters(array $array)
    {
        $filters = [];

        $this->arrayToFilter($filters, ['filter'], $array);

        return $filters;
    }

    /**
     * Converts given array to a filter string for the query params.
     *
     * @param array $filters
     * @param array $keys
     * @param       $value
     * @return void
     */
    private function arrayToFilter(array &$filters, array $keys, $value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $nextValue) {
                $this->arrayToFilter($filters, array_merge($keys, ['[' . $key . ']']), $nextValue);
            }
        } else {
            $filters[implode('', $keys)] = $value;
        }
    }

    /**
     * Determines which carriers to look pudo locations up for.
     * The specificCarrier parameter indicates a specific carrier to look up pudo locations for. Otherwise,
     * all carriers will be used.
     * The onlyActiveContracts parameter indicates whether only carriers for which the user has an active contract
     * for services with delivery method pickup should be used for pudo location retrieval.
     *
     * @param bool                  $onlyActiveContracts
     * @param null|CarrierInterface $specificCarrier
     * @return array
     */
    private function determineCarriersForPudoLocations($onlyActiveContracts, $specificCarrier = null)
    {
        // If we're looking for a specific carrier but it doesn't
        // matter if it has active contracts, just return it immediately.
        if (!$onlyActiveContracts && $specificCarrier) {
            return [$specificCarrier];
        }

        // Return all carriers if we're not filtering for anything
        // specific.
        if (!$onlyActiveContracts) {
            return $this->getCarriers()->get();
        }

        $parameters = [
            'has_active_contract' => 'true',
            'delivery_method'     => 'pick-up',
        ];

        if ($specificCarrier) {
            $parameters['carrier'] = $specificCarrier->getId();
        }

        $pudoServices = $this->getServices(null, $parameters)->get();

        return array_map(function (Service $service) {
            return $service->getCarrier();
        }, $pudoServices);
    }
}
