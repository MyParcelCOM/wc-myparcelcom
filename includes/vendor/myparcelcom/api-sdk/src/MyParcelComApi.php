<?php

namespace MyParcelCom\ApiSdk;

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
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceFactoryInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceProxyInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShopInterface;
use MyParcelCom\ApiSdk\Resources\ResourceFactory;
use MyParcelCom\ApiSdk\Resources\Service;
use MyParcelCom\ApiSdk\Shipments\ServiceMatcher;
use MyParcelCom\ApiSdk\Utils\UrlBuilder;
use MyParcelCom\ApiSdk\Validators\ShipmentValidator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;
use function GuzzleHttp\Psr7\parse_response;
use function GuzzleHttp\Psr7\str;

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

        // Either use the given cache or instantiate a new one that uses the
        // filesystem as cache. By default the `FilesystemCache` will use the
        // system temp directory as a cache.
        $this->setCache($cache ?: new FilesystemCache('myparcelcom'));

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
    public function getRegions($filters = [])
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

        // These resources can be stored for a week.
        $regions = $this->getRequestCollection($url->getUrl(), self::TTL_WEEK);

        if ($regions->count() > 0 || !isset($filters['region_code'])) {
            return $regions;
        }

        // Fallback to the country if the specific region is not in the API.
        $url->addQuery(['filter[region_code]' => null]);

        return $this->getRequestCollection($url->getUrl(), self::TTL_WEEK);
    }

    /**
     * {@inheritdoc}
     */
    public function getCarriers()
    {
        // These resources can be stored for a week.
        return $this->getRequestCollection($this->apiUri . self::PATH_CARRIERS, self::TTL_WEEK);
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
        $onlyActiveContracts = true
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

            // These resources can be stored for a week.
            try {
                $resources = $this->getResourcesArray($carrierUri, self::TTL_WEEK);
            } catch (RequestException $exception) {
                // When we are trying to fetch pudo locations for a specific
                // carrier, we want to be able to distinct between 'no results'
                // or 'something went wrong'. However, when we're not looking
                // for carrier specific pudo locations, we just want to show
                // pudo locations for the failing carrier as not aviilable (null).
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
    public function getShops()
    {
        // These resources can be stored for a week. Or should be removed from
        // cache when updated
        return $this->getRequestCollection($this->apiUri . self::PATH_SHOPS, self::TTL_WEEK);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultShop()
    {
        $shops = $this->getResourcesArray($this->apiUri . self::PATH_SHOPS, self::TTL_WEEK);

        // For now the oldest shop will be the default shop.
        usort($shops, function (ShopInterface $shopA, ShopInterface $shopB) {
            return $shopA->getCreatedAt()->getTimestamp() - $shopB->getCreatedAt()->getTimestamp();
        });

        return reset($shops);
    }

    /**
     * {@inheritdoc}
     */
    public function getServices(ShipmentInterface $shipment = null, array $filters = ['has_active_contract' => 'true'])
    {
        $url = new UrlBuilder($this->apiUri . self::PATH_SERVICES);
        $url->addQuery($this->arrayToFilter($filters));

        if ($shipment === null) {
            return $this->getRequestCollection($url->getUrl(), self::TTL_WEEK);
        }

        if ($shipment->getSenderAddress() === null) {
            $shipment->setSenderAddress($this->getDefaultShop()->getReturnAddress());
        }
        if ($shipment->getRecipientAddress() === null) {
            throw new InvalidResourceException(
                'Missing `recipient_address` on `shipments` resource'
            );
        }
        if ($shipment->getSenderAddress() === null) {
            throw new InvalidResourceException(
                'Missing `sender_address` on `shipments` resource'
            );
        }

        $regionsFrom = $this->getRegions(
            $shipment->getSenderAddress()->getCountryCode(),
            $shipment->getSenderAddress()->getRegionCode()
        )->get();
        $regionsTo = $this->getRegions(
            $shipment->getRecipientAddress()->getCountryCode(),
            $shipment->getRecipientAddress()->getRegionCode()
        )->get();

        $url->addQuery($this->arrayToFilter([
            'region_from' => reset($regionsFrom)->getId(),
            'region_to'   => reset($regionsTo)->getId(),
        ]));

        // Services can be cached for a week.
        $services = $this->getResourcesArray($url->getUrl(), self::TTL_WEEK);

        $matcher = new ServiceMatcher();
        $services = array_values(array_filter($services, function (ServiceInterface $service) use ($shipment, $matcher) {
            return $matcher->matches($shipment, $service);
        }));

        return new ArrayCollection($services);
    }

    /**
     * {@inheritdoc}
     */
    public function getServicesForCarrier(CarrierInterface $carrier)
    {
        $url = new UrlBuilder($this->apiUri . self::PATH_SERVICES);
        $url->addQuery(['filter[carrier]' => $carrier->getId()]);

        return $this->getRequestCollection($url->getUrl(), self::TTL_WEEK);
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceRates(array $filters = [])
    {
        $url = new UrlBuilder($this->apiUri . self::PATH_SERVICE_RATES);
        $url->addQuery($this->arrayToFilter($filters));

        return $this->getRequestCollection($url->getUrl(), self::TTL_WEEK);
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceRatesForShipment(ShipmentInterface $shipment)
    {
        $services = $this->getServices($shipment);
        $serviceIds = [];
        foreach ($services as $service) {
            $serviceIds[] = $service->getId();
        }

        $url = new UrlBuilder($this->apiUri . self::PATH_SERVICE_RATES);
        $url->addQuery([
            'filter[weight]'  => $shipment->getPhysicalProperties()->getWeight(),
            'filter[service]' => implode(',', $serviceIds),
        ]);

        $serviceRates = $this->getRequestCollection($url->getUrl(), self::TTL_WEEK);

        $shipmentOptionIds = array_map(function (ServiceOptionInterface $serviceOption) {
            return $serviceOption->getId();
        }, $shipment->getServiceOptions());

        $matchingServiceRates = [];
        foreach ($serviceRates as $serviceRate) {
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
    public function getShipments(ShopInterface $shop = null)
    {
        $url = new UrlBuilder($this->apiUri . self::PATH_SHIPMENTS);

        if (isset($shop)) {
            $url->addQuery(['filter[shop]' => $shop->getId()]);
        }

        return $this->getRequestCollection($url->getUrl(), self::TTL_WEEK);
    }

    /**
     * {@inheritdoc}
     */
    public function getShipment($id)
    {
        return $this->getResourceById(ResourceInterface::TYPE_SHIPMENT, $id);
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

    /**
     * {@inheritdoc}
     */
    public function createShipment(ShipmentInterface $shipment)
    {
        if ($shipment->getPhysicalProperties() === null || $shipment->getPhysicalProperties()->getWeight() === null) {
            throw new InvalidResourceException(
                'Cannot create shipment without weight'
            );
        }

        // If no shop is set, use the default shop.
        if ($shipment->getShop() === null) {
            $shipment->setShop($this->getDefaultShop());
        }

        // If no sender address is set use one of the addresses in the following
        // order: shop sender > shipment return > shop return
        $shop = $shipment->getShop();
        if ($shipment->getSenderAddress() === null) {
            $shipment->setSenderAddress(
                $shop->getSenderAddress()
                    ?: $shipment->getReturnAddress()
                    ?: $shop->getReturnAddress()
            );
        }
        // If no return address is set use the return address of the shop or the
        // sender address on the shipment.
        if ($shipment->getReturnAddress() === null) {
            $shipment->setReturnAddress(
                $shop->getReturnAddress()
                    ?: $shipment->getSenderAddress()
            );
        }

        $validator = new ShipmentValidator($shipment);

        if (!$validator->isValid()) {
            $exception = new InvalidResourceException(
                'Could not create shipment, shipment was invalid or incomplete'
            );
            $exception->setErrors($validator->getErrors());

            throw $exception;
        }

        return $this->postResource($shipment);
    }

    /**
     * {@inheritdoc}
     */
    public function updateShipment(ShipmentInterface $shipment)
    {
        $validator = new ShipmentValidator($shipment);

        if (!$validator->isValid()) {
            $exception = new InvalidResourceException(
                'Could not update shipment, shipment was invalid or incomplete'
            );
            $exception->setErrors($validator->getErrors());

            throw $exception;
        }

        return $this->patchResource($shipment);
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
        $json = json_decode((string)$response->getBody(), true);

        $resources = $this->jsonToResources($json['data']);

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
        }, function ($data) {
            return $this->jsonToResources($data);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function doRequest($uri, $method = 'get', array $body = [], array $headers = [], $ttl = self::TTL_10MIN)
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
            return parse_response($response);
        }

        try {
            $request = $this->buildRequest($uri, $method, $body, $headers);
            $response = $this->client->sendRequest($request);

            // Store the response in cache
            if (strtolower($method) === 'get') {
                $this->cache->set($cacheKey, str($response), $ttl);
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

        $request = new Request($method, $uri, $headers, $body);

        return $request;
    }

    /**
     * Convert the data from a json request to an array of resources.
     *
     * @param array $json
     * @return array
     */
    protected function jsonToResources(array $json)
    {
        $resources = [];

        if (isset($json['type'])) {
            $json = [$json];
        }

        foreach ($json as $resourceData) {
            $resources[] = $this->resourceFactory->create($resourceData['type'], $resourceData);
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

        $body = (string)$request->getBody();
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
     * @return ResourceInterface
     * @throws RequestException
     */
    public function getResourceById($resourceType, $id)
    {
        $resources = $this->getResourcesArray(
            $this->getResourceUri($resourceType, $id)
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
     * @return ResourceInterface|null
     * @throws RequestException
     */
    protected function patchResource(ResourceInterface $resource)
    {
        return $this->sendResource($resource, 'patch');
    }

    /**
     * Post given resource and return the resource that was returned by the request.
     *
     * @param ResourceInterface $resource
     * @return ResourceInterface|null
     * @throws RequestException
     */
    protected function postResource(ResourceInterface $resource)
    {
        return $this->sendResource($resource);
    }

    /**
     * Send given resource to the API and return the resource that was returned.
     *
     * @param ResourceInterface $resource
     * @param string            $method
     * @return ResourceInterface|null
     * @throws RequestException
     */
    protected function sendResource(ResourceInterface $resource, $method = 'post')
    {
        $response = $this->doRequest(
            $this->getResourceUri($resource->getType(), $resource->getId()),
            $method,
            ['data' => $resource],
            $this->authenticator->getAuthorizationHeader() + [
                AuthenticatorInterface::HEADER_ACCEPT => AuthenticatorInterface::MIME_TYPE_JSONAPI,
            ]
        );

        $json = json_decode($response->getBody(), true);
        $resources = $this->jsonToResources($json['data']);

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
    private function arrayToFilter(array $array)
    {
        $filters = [];
        foreach ($array as $name => $value) {
            $filters["filter[$name]"] = $value;
        }

        return $filters;
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
