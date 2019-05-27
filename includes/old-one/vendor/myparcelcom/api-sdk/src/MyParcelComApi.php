<?php

namespace MyParcelCom\ApiSdk;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\RequestOptions;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\Collection\ArrayCollection;
use MyParcelCom\ApiSdk\Collection\CollectionInterface;
use MyParcelCom\ApiSdk\Collection\PromiseCollection;
use MyParcelCom\ApiSdk\Exceptions\InvalidResourceException;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceFactoryInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceProxyInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShopInterface;
use MyParcelCom\ApiSdk\Resources\ResourceFactory;
use MyParcelCom\ApiSdk\Shipments\ContractSelector;
use MyParcelCom\ApiSdk\Shipments\PriceCalculator;
use MyParcelCom\ApiSdk\Shipments\ServiceMatcher;
use MyParcelCom\ApiSdk\Utils\UrlBuilder;
use MyParcelCom\ApiSdk\Validators\ShipmentValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Psr7\parse_response;
use function GuzzleHttp\Psr7\str;

class MyParcelComApi implements MyParcelComApiInterface
{
    const API_VERSION = 'v1';

    /** @var string */
    protected $apiUri;

    /** @var CacheInterface */
    protected $cache;

    /** @var ResourceFactoryInterface */
    protected $resourceFactory;

    /** @var AuthenticatorInterface */
    protected $authenticator;

    /** @var ClientInterface */
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
     * @param CacheInterface|null           $cache
     * @param ResourceFactoryInterface|null $resourceFactory
     * @return MyParcelComApi
     */
    public static function createSingleton(
        AuthenticatorInterface $authenticator,
        $apiUri = 'https://sandbox-api.myparcel.com',
        CacheInterface $cache = null,
        ResourceFactoryInterface $resourceFactory = null
    ) {
        return self::$singleton = (new self($apiUri, $cache, $resourceFactory))
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
     * @param CacheInterface|null           $cache
     * @param ResourceFactoryInterface|null $resourceFactory
     */
    public function __construct(
        $apiUri = 'https://sandbox-api.myparcel.com',
        CacheInterface $cache = null,
        ResourceFactoryInterface $resourceFactory = null
    ) {
        $this->setApiUri($apiUri);

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
    public function getRegions($countryCode = null, $regionCode = null)
    {
        $url = (new UrlBuilder($this->apiUri . self::PATH_REGIONS));

        if ($countryCode) {
            $url->addQuery(['filter[country_code]' => $countryCode]);
        }
        if ($regionCode) {
            $url->addQuery(['filter[region_code]' => $regionCode]);
        }

        // These resources can be stored for a week.
        $regions = $this->getResourceCollection($url->getUrl(), self::TTL_WEEK);

        if ($regions->count() > 0 || $regionCode === null) {
            return $regions;
        }

        // Fallback to the country if the specific region is not in the API.
        $url->addQuery(['filter[region_code]' => null]);

        return $this->getResourceCollection($url->getUrl(), self::TTL_WEEK);
    }

    /**
     * {@inheritdoc}
     */
    public function getCarriers()
    {
        // These resources can be stored for a week.
        return $this->getResourceCollection($this->apiUri . self::PATH_CARRIERS, self::TTL_WEEK);
    }

    /**
     * {@inheritdoc}
     */
    public function getPickUpDropOffLocations(
        $countryCode,
        $postalCode,
        $streetName = null,
        $streetNumber = null,
        CarrierInterface $specificCarrier = null
    ) {
        $carriers = $specificCarrier ? [$specificCarrier] : $this->getCarriers();

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
            $promise = $this->getResourcesPromise($carrierUri, self::TTL_WEEK);
            if ($specificCarrier) {
                return new ArrayCollection($promise->wait());
            }

            // When something fails while retrieving the locations
            // for a carrier, the locations of the other carriers should
            // still be returned. The failing carrier returns null.
            $pudoLocations[$carrier->getId()] = $promise
                ->then(function ($response) {
                    return new ArrayCollection($response);
                })
                ->otherwise(function () {
                    return null;
                })
                ->wait();
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
        return $this->getResourceCollection($this->apiUri . self::PATH_SHOPS, self::TTL_WEEK);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultShop()
    {
        $shops = $this->getResourcesPromise($this->apiUri . self::PATH_SHOPS, self::TTL_WEEK)
            ->wait();

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
            return $this->getResourceCollection($url->getUrl(), self::TTL_WEEK);
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
        $services = $this->getResourcesPromise($url->getUrl(), self::TTL_WEEK)
            ->wait();

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

        return $this->getResourceCollection($url->getUrl(), self::TTL_WEEK);
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

        return $this->getResourceCollection($url->getUrl(), self::TTL_WEEK);
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

        // If no contract is set, select the cheapest one.
        if ($shipment->getServiceContract() === null) {
            $this->determineContract($shipment);
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
     * Determine what service contract to use for given shipment and update the
     * shipment.
     *
     * @param ShipmentInterface $shipment
     * @return $this
     */
    protected function determineContract(ShipmentInterface $shipment)
    {
        $selector = new ContractSelector();
        $calculator = new PriceCalculator();

        $allServices = [];
        $collection = $this->getServices($shipment);
        for ($offset = 0; $offset < $collection->count(); $offset += 30) {
            $allServices = array_merge($allServices, $collection->offset($offset)->get());
        }

        $contracts = array_map(
            function (ServiceInterface $service) use ($selector, $calculator, $shipment) {
                $contract = $selector->selectCheapest($shipment, $service->getServiceContracts());

                return [
                    'price'    => $calculator->calculate($shipment, $contract),
                    'contract' => $contract,
                    'service'  => $service,
                ];
            },
            $allServices
        );

        usort($contracts, function ($a, $b) {
            return $a['price'] - $b['price'];
        });

        $cheapest = reset($contracts);
        $shipment->setServiceContract($cheapest['contract']);

        return $this;
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
     * Set the Guzzle client to use to connect to the api.
     *
     * @param ClientInterface $client
     * @return $this
     */
    public function setHttpClient(ClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get the guzzle client.
     *
     * @return ClientInterface
     */
    protected function getHttpClient()
    {
        if (!isset($this->client)) {
            $this->client = new Client([
                'base_uri'              => $this->apiUri,
                RequestOptions::HEADERS => [
                    'Content-Type' => 'application/vnd.api+json',
                ],
            ]);
        }

        return $this->client;
    }

    /**
     * Get a promise that will return an array with resources requested from
     * given uri. A time-to-live can be specified for how long this request
     * should be cached (defaults to 10 minutes).
     *
     * @param string $uri
     * @param int    $ttl
     * @return PromiseInterface
     * @internal param string $path
     */
    protected function getResourcesPromise($uri, $ttl = self::TTL_10MIN)
    {
        return $this->doRequest($uri, 'get', [], [], $ttl)
            ->then(function (ResponseInterface $response) {
                $json = \GuzzleHttp\json_decode((string)$response->getBody(), true);

                $resources = $this->jsonToResources($json['data']);

                // If there is no next link, we don't have to retrieve any more data
                if (!isset($json['links']['next'])) {
                    return $resources;
                }

                return $this->getResourcesPromise($json['links']['next'])
                    ->then(function ($nextResources) use ($resources) {
                        return array_merge($resources, $nextResources);
                    });
            });
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
    protected function getResourceCollection($uri, $ttl = self::TTL_10MIN)
    {
        return new PromiseCollection(function ($pageNumber, $pageSize) use ($uri, $ttl) {
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

        $cacheKey = sha1($method . join($headers) . $uri);

        if (($response = $this->cache->get($cacheKey))) {
            return promise_for(parse_response($response));
        }

        return $this->getHttpClient()->requestAsync(
            $method,
            $uri,
            [
                RequestOptions::JSON    => $body,
                RequestOptions::HEADERS => $headers,
            ]
        )->then(function (ResponseInterface $response) use ($cacheKey, $ttl) {
            $this->cache->set($cacheKey, str($response), $ttl);

            return $response;
        }, function (RequestException $reason) {
            return $this->handleRequestException($reason);
        });
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
            $attributes = $this->flattenResourceData($resourceData);

            $resources[] = $this->resourceFactory->create($resourceData['type'], $attributes);
        }

        return $resources;
    }

    /**
     * Flattens the data of the resource into a single array, effectively
     * removing the `attributes` and `relationships` arrays.
     *
     * @param array $resourceData
     * @return array
     */
    private function flattenResourceData(array $resourceData)
    {
        $data = [
            'id'   => $resourceData['id'],
            'type' => $resourceData['type'],
        ];

        if (isset($resourceData['attributes'])) {
            $data += $resourceData['attributes'];
        }

        if (isset($resourceData['relationships'])) {
            $data += array_map(
                [$this, 'flattenRelationship'],
                $resourceData['relationships']
            );
        }

        if (isset($resourceData['links'])) {
            $data['links'] = $resourceData['links'];
        }

        return $data;
    }

    /**
     * @param array $relationship
     * @return array
     */
    private function flattenRelationship(array $relationship)
    {
        $data = isset($relationship['data'])
            ? $relationship['data']
            : [];
        $links = isset($relationship['links'])
            ? $relationship['links']
            : [];

        return $data + $links;
    }

    /**
     * @param RequestException $exception
     * @return PromiseInterface
     */
    protected function handleRequestException(RequestException $exception)
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
            ? \GuzzleHttp\json_decode($body, true)
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
     */
    public function getResourceById($resourceType, $id)
    {
        $resources = $this->getResourcesPromise(
            $this->getResourceUri($resourceType, $id)
        )->wait();

        return reset($resources);
    }

    /**
     * {@inheritdoc}
     */
    public function getResourcesFromUri($uri)
    {
        return $this->getResourcesPromise($uri)->wait();
    }

    /**
     * Patch given resource and return the resource that was returned by the request.
     *
     * @param ResourceInterface $resource
     * @return ResourceInterface|null
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
     */
    protected function sendResource(ResourceInterface $resource, $method = 'post')
    {
        $promise = $this->getHttpClient()->requestAsync(
            $method,
            $this->getResourceUri($resource->getType(), $resource->getId()),
            [
                RequestOptions::HEADERS => $this->authenticator->getAuthorizationHeader() + [
                        AuthenticatorInterface::HEADER_ACCEPT => AuthenticatorInterface::MIME_TYPE_JSONAPI,
                    ],
                RequestOptions::JSON    => ['data' => $resource],
            ]
        )->then(function (ResponseInterface $response) {
            $json = \GuzzleHttp\json_decode($response->getBody(), true);

            return $this->jsonToResources($json['data']);
        }, function (RequestException $reason) {
            return $this->handleRequestException($reason);
        });

        $resources = $promise->wait();

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
                self::API_VERSION,
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
            /**
             * @deprecated This condition is to keep the SDK backwards
             * compatible until the API has been updated to use the new filter.
             */
            if ($name === 'has_active_contract') {
                $filters['filter[has_contract]'] = $value;
            }

            $filters["filter[$name]"] = $value;
        }

        return $filters;
    }
}
