<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Exceptions\ResourceFactoryException;
use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\AddressInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CarrierInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\CustomsInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentItemInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\FileInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\OpeningHourInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\PhysicalPropertiesInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\PickUpDropOffLocationInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\PositionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\RegionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceFactoryInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceProxyInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceGroupInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInsuranceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionPriceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentStatusInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShopInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\StatusInterface;
use MyParcelCom\ApiSdk\Resources\Proxy\CarrierContractProxy;
use MyParcelCom\ApiSdk\Resources\Proxy\CarrierProxy;
use MyParcelCom\ApiSdk\Resources\Proxy\FileProxy;
use MyParcelCom\ApiSdk\Resources\Proxy\FileStreamProxy;
use MyParcelCom\ApiSdk\Resources\Proxy\RegionProxy;
use MyParcelCom\ApiSdk\Resources\Proxy\ServiceContractProxy;
use MyParcelCom\ApiSdk\Resources\Proxy\ServiceGroupProxy;
use MyParcelCom\ApiSdk\Resources\Proxy\ServiceInsuranceProxy;
use MyParcelCom\ApiSdk\Resources\Proxy\ServiceOptionPriceProxy;
use MyParcelCom\ApiSdk\Resources\Proxy\ServiceOptionProxy;
use MyParcelCom\ApiSdk\Resources\Proxy\ServiceProxy;
use MyParcelCom\ApiSdk\Resources\Proxy\ShipmentProxy;
use MyParcelCom\ApiSdk\Resources\Proxy\ShipmentStatusProxy;
use MyParcelCom\ApiSdk\Resources\Proxy\ShopProxy;
use MyParcelCom\ApiSdk\Resources\Proxy\StatusProxy;
use MyParcelCom\ApiSdk\Utils\StringUtils;
use ReflectionParameter;

class ResourceFactory implements ResourceFactoryInterface, ResourceProxyInterface
{
    /** @var array */
    private $typeFactory = [
        ResourceInterface::TYPE_CARRIER        => Carrier::class,
        ResourceInterface::TYPE_PUDO_LOCATION  => PickUpDropOffLocation::class,
        ResourceInterface::TYPE_REGION         => Region::class,
        ResourceInterface::TYPE_SERVICE_OPTION => ServiceOption::class,
        ResourceInterface::TYPE_SHOP           => Shop::class,
        ResourceInterface::TYPE_STATUS         => Status::class,

        AddressInterface::class               => Address::class,
        CarrierInterface::class               => Carrier::class,
        CustomsInterface::class               => Customs::class,
        OpeningHourInterface::class           => OpeningHour::class,
        PhysicalPropertiesInterface::class    => PhysicalProperties::class,
        PickUpDropOffLocationInterface::class => PickUpDropOffLocation::class,
        PositionInterface::class              => Position::class,
        RegionInterface::class                => Region::class,
        ServiceOptionInterface::class         => ServiceOption::class,
        ShipmentInterface::class              => Shipment::class,
        ShopInterface::class                  => Shop::class,
        StatusInterface::class                => Status::class,
    ];

    /** @var MyParcelComApiInterface */
    protected $api;

    public function __construct()
    {
        $carrierContractFactory = [$this, 'carrierContractFactory'];
        $shipmentFactory = [$this, 'shipmentFactory'];
        $shipmentStatusFactory = [$this, 'shipmentStatusFactory'];
        $serviceFactory = [$this, 'serviceFactory'];
        $serviceContractFactory = [$this, 'serviceContractFactory'];
        $serviceGroupFactory = [$this, 'serviceGroupFactory'];
        $serviceOptionPriceFactory = [$this, 'serviceOptionPriceFactory'];
        $serviceInsuranceFactory = [$this, 'serviceInsuranceFactory'];
        $fileFactory = [$this, 'fileFactory'];
        $shipmentItemFactory = [$this, 'shipmentItemFactory'];

        $this->setFactoryForType(ResourceInterface::TYPE_CARRIER_CONTRACT, $carrierContractFactory);
        $this->setFactoryForType(CarrierContractInterface::class, $carrierContractFactory);

        $this->setFactoryForType(ResourceInterface::TYPE_SHIPMENT, $shipmentFactory);
        $this->setFactoryForType(ShipmentInterface::class, $shipmentFactory);

        $this->setFactoryForType(ResourceInterface::TYPE_SHIPMENT_STATUS, $shipmentStatusFactory);
        $this->setFactoryForType(ShipmentStatusInterface::class, $shipmentStatusFactory);

        $this->setFactoryForType(ResourceInterface::TYPE_SERVICE, $serviceFactory);
        $this->setFactoryForType(ServiceInterface::class, $serviceFactory);

        $this->setFactoryForType(ResourceInterface::TYPE_SERVICE_CONTRACT, $serviceContractFactory);
        $this->setFactoryForType(ServiceContractInterface::class, $serviceContractFactory);

        $this->setFactoryForType(ResourceInterface::TYPE_SERVICE_GROUP, $serviceGroupFactory);
        $this->setFactoryForType(ServiceGroupInterface::class, $serviceGroupFactory);

        $this->setFactoryForType(ResourceInterface::TYPE_SERVICE_OPTION_PRICE, $serviceOptionPriceFactory);
        $this->setFactoryForType(ServiceOptionPriceInterface::class, $serviceOptionPriceFactory);

        $this->setFactoryForType(ResourceInterface::TYPE_SERVICE_INSURANCE, $serviceInsuranceFactory);
        $this->setFactoryForType(ServiceInsuranceInterface::class, $serviceInsuranceFactory);

        $this->setFactoryForType(ResourceInterface::TYPE_FILE, $fileFactory);
        $this->setFactoryForType(FileInterface::class, $fileFactory);

        $this->setFactoryForType(ShipmentItemInterface::class, $shipmentItemFactory);
    }

    /**
     * Factory method for creating ServiceContract resources, creates proxies
     * for all relationships.
     *
     * @param string $type
     * @param array  $attributes
     * @return ServiceContract
     */
    protected function serviceContractFactory($type, array &$attributes)
    {
        $serviceContract = new ServiceContract();

        if (isset($attributes['service']['id'])) {
            $serviceContract->setService(
                (new ServiceProxy())->setMyParcelComApi($this->api)->setId($attributes['service']['id'])
            );

            unset($attributes['service']);
        }

        if (isset($attributes['carrier_contract']['id'])) {
            $serviceContract->setCarrierContract(
                (new CarrierContractProxy())->setMyParcelComApi($this->api)->setId($attributes['carrier_contract']['id'])
            );

            unset($attributes['carrier_contract']);
        }


        if (isset($attributes['service_groups'])) {
            array_walk($attributes['service_groups'], function ($group) use ($serviceContract) {
                if (empty($group['id'])) {
                    return;
                }

                $serviceContract->addServicegroup(
                    (new ServicegroupProxy())->setMyParcelComApi($this->api)->setId($group['id'])
                );
            });

            unset($attributes['service_groups']);
        }
        if (isset($attributes['service_insurances'])) {
            array_walk($attributes['service_insurances'], function ($insurance) use ($serviceContract) {
                if (empty($insurance['id'])) {
                    return;
                }

                $serviceContract->addServiceInsurance(
                    (new ServiceInsuranceProxy())->setMyParcelComApi($this->api)->setId($insurance['id'])
                );
            });

            unset($attributes['service_insurances']);
        }
        if (isset($attributes['service_option_prices'])) {
            array_walk($attributes['service_option_prices'], function ($optionPrice) use ($serviceContract) {
                if (empty($optionPrice['id'])) {
                    return;
                }

                $serviceContract->addServiceOptionPrice(
                    (new ServiceOptionPriceProxy())->setMyParcelComApi($this->api)->setId($optionPrice['id'])
                );
            });

            unset($attributes['service_option_prices']);
        }

        return $serviceContract;
    }

    /**
     * Factory method for ServiceOptionPrice resources, creates proxies for all
     * relationships.
     *
     * @param string $type
     * @param array  $attributes
     * @return ServiceOptionPrice
     */
    protected function serviceOptionPriceFactory($type, array &$attributes)
    {
        $serviceOptionPrice = new ServiceOptionPrice();

        if (isset($attributes['service_option']['id'])) {
            $serviceOptionPrice->setServiceOption(
                (new ServiceOptionProxy())->setMyParcelComApi($this->api)->setId($attributes['service_option']['id'])
            );

            unset($attributes['service_option']);
        }

        if (isset($attributes['service_contract']['id'])) {
            $serviceOptionPrice->setServiceContract(
                (new ServiceContractProxy())->setMyParcelComApi($this->api)->setId($attributes['service_contract']['id'])
            );

            unset($attributes['service_contract']);
        }

        if (isset($attributes['price']['amount'])) {
            $serviceOptionPrice->setPrice($attributes['price']['amount']);
            $serviceOptionPrice->setCurrency($attributes['price']['currency']);

            unset($attributes['price']);
        }

        return $serviceOptionPrice;
    }

    /**
     * Factory method for creating CarrierContracts with proxies for all
     * relationships.
     *
     * @param string $type
     * @param array  $attributes
     * @return CarrierContract
     */
    protected function carrierContractFactory($type, array &$attributes)
    {
        $carrierContract = new CarrierContract();

        if (isset($attributes['carrier']['id'])) {
            $carrierContract->setCarrier(
                (new CarrierProxy())->setMyParcelComApi($this->api)->setId($attributes['carrier']['id'])
            );

            unset($attributes['carrier']);
        }

        if (isset($attributes['id'])) {
            $carrierContract->setServiceContractsCallback(function () use ($attributes) {
                return $this->api->getResourcesFromUri(
                    str_replace(
                        '{carrier_contract_id}',
                        $attributes['id'],
                        MyParcelComApiInterface::PATH_CARRIER_SERVICE_CONTRACTS
                    )
                );
            });
        }

        return $carrierContract;
    }

    /**
     * Shipment factory method that creates proxies for all relationships.
     *
     * @param string $type
     * @param array  $attributes
     * @return Shipment
     */
    protected function shipmentFactory($type, array &$attributes)
    {
        $shipment = new Shipment();

        if (isset($attributes['files'])) {
            array_walk($attributes['files'], function ($file) use ($shipment) {
                if (empty($file['id'])) {
                    return;
                }

                $shipment->addFile(
                    (new FileProxy())->setMyParcelComApi($this->api)->setId($file['id'])
                );
            });

            unset($attributes['files']);
        }

        if (isset($attributes['shop']['id'])) {
            $shipment->setShop(
                (new ShopProxy())->setMyParcelComApi($this->api)->setId($attributes['shop']['id'])
            );

            unset($attributes['shop']);
        }

        if (isset($attributes['service_contract']['id'])) {
            $shipment->setServiceContract(
                (new ServiceContractProxy())->setMyParcelComApi($this->api)->setId($attributes['service_contract']['id'])
            );

            unset($attributes['service_contract']);
        }

        if (isset($attributes['shipment_status']['related'])) {

            $shipment->setShipmentStatus(
                (new ShipmentStatusProxy())
                    ->setId($attributes['shipment_status']['id'])
                    ->setMyParcelComApi($this->api)
                    ->setResourceUri($attributes['shipment_status']['related'])
            );

            unset($attributes['shipment_status']);
        }

        if (isset($attributes['price']['amount'])) {
            $shipment->setPrice($attributes['price']['amount']);
            if (isset($attributes['price']['currency'])) {
                $shipment->setCurrency($attributes['price']['currency']);
            }

            unset($attributes['price']);
        }

        if (isset($attributes['insurance']['amount'])) {
            $shipment->setInsuranceAmount($attributes['insurance']['amount']);
            if (!$shipment->getCurrency()) {
                $shipment->setCurrency($attributes['insurance']['currency']);
            }

            unset($attributes['insurance']);
        }

        if (isset($attributes['pickup_location']['code'])) {
            $shipment->setPickupLocationCode($attributes['pickup_location']['code']);
        }

        if (isset($attributes['pickup_location']['address'])) {
            /** @var AddressInterface $pudoAddress */
            $pudoAddress = $this->create(
                AddressInterface::class,
                $attributes['pickup_location']['address']
            );

            $shipment->setPickupLocationAddress($pudoAddress);
        }

        if (isset($attributes['id'])) {
            $shipment->setStatusHistoryCallback(function () use ($attributes) {
                return $this->api->getResourcesFromUri(
                    str_replace(
                        '{shipment_id}',
                        $attributes['id'],
                        MyParcelComApiInterface::PATH_SHIPMENT_STATUSES
                    )
                );
            });
        }

        return $shipment;
    }

    /**
     * ShipmentStatus factory that creates proxies for all relationships.
     *
     * @param string $type
     * @param array  $attributes
     * @return ShipmentStatus
     */
    protected function shipmentStatusFactory($type, array &$attributes)
    {
        $shipmentStatus = new ShipmentStatus();

        if (isset($attributes['status']['id'])) {
            $shipmentStatus->setStatus(
                (new StatusProxy())->setMyParcelComApi($this->api)->setId($attributes['status']['id'])
            );

            unset($attributes['status']);
        }

        if (isset($attributes['shipment']['id'])) {
            $shipmentStatus->setShipment(
                (new ShipmentProxy())->setMyParcelComApi($this->api)->setId($attributes['shipment']['id'])
            );

            unset($attributes['shipment']);
        }

        return $shipmentStatus;
    }

    /**
     * Service factory method that creates proxies for all relationships.
     *
     * @param string $type
     * @param array  $attributes
     * @return Service
     */
    protected function serviceFactory($type, array &$attributes)
    {
        $service = new Service();

        if (isset($attributes['region_from']['id'])) {
            $service->setRegionFrom(
                (new RegionProxy())->setMyParcelComApi($this->api)->setId($attributes['region_from']['id'])
            );

            unset($attributes['region_from']);
        }
        if (isset($attributes['region_to']['id'])) {
            $service->setRegionTo(
                (new RegionProxy())->setMyParcelComApi($this->api)->setId($attributes['region_to']['id'])
            );

            unset($attributes['region_to']);
        }

        if (isset($attributes['links']['contracts'])) {
            $service->setServiceContracts($this->api->getResourcesFromUri($attributes['links']['contracts']));

            unset($attributes['links']['contracts']);
        }

        if (isset($attributes['transit_time']['min'])) {
            $service->setTransitTimeMin($attributes['transit_time']['min']);

            unset($attributes['transit_time']['min']);
        }

        if (isset($attributes['transit_time']['max'])) {
            $service->setTransitTimeMax($attributes['transit_time']['max']);

            unset($attributes['transit_time']['max']);
        }

        if (isset($attributes['id'])) {
            $service->setServiceContractsCallback(function () use ($attributes) {
                return $this->api->getResourcesFromUri(
                    str_replace(
                        '{service_id}',
                        $attributes['id'],
                        MyParcelComApiInterface::PATH_SERVICE_CONTRACTS
                    )
                );
            });
        }

        return $service;
    }

    /**
     * ServiceGroup factory method.
     *
     * @param string $type
     * @param array  $attributes
     * @return ServiceGroup
     */
    protected function serviceGroupFactory($type, &$attributes)
    {
        $serviceGroup = new ServiceGroup();

        if (isset($attributes['price']['amount'])) {
            $serviceGroup->setPrice($attributes['price']['amount']);
            $serviceGroup->setCurrency($attributes['price']['currency']);

            unset($attributes['price']);
        }
        if (isset($attributes['weight']['min'])) {
            $serviceGroup->setWeightMin($attributes['weight']['min']);
            $serviceGroup->setWeightMax($attributes['weight']['max']);

            unset($attributes['weight']);
        }
        if (isset($attributes['step_price']['amount'])) {
            $serviceGroup->setStepPrice($attributes['step_price']['amount']);
            if (!$serviceGroup->getCurrency()) {
                $serviceGroup->setCurrency($attributes['step_price']['currency']);
            }

            unset($attributes['step_price']);
        }

        return $serviceGroup;
    }

    /**
     * ServiceInsurance factory method.
     *
     * @param string $type
     * @param array  $attributes
     * @return ServiceInsurance
     */
    protected function serviceInsuranceFactory($type, &$attributes)
    {
        $serviceInsurance = new ServiceInsurance();

        if (isset($attributes['price']['amount'])) {
            $serviceInsurance->setPrice($attributes['price']['amount']);
            $serviceInsurance->setCurrency($attributes['price']['currency']);

            unset($attributes['price']);
        }

        if (isset($attributes['covered']['amount'])) {
            $serviceInsurance->setCovered($attributes['covered']['amount']);
            if (!$serviceInsurance->getCurrency()) {
                $serviceInsurance->setCurrency($attributes['covered']['currency']);
            }

            unset($attributes['covered']);
        }

        return $serviceInsurance;
    }

    /**
     * Factory method for creating file resources, adds proxy streams to the
     * file for requesting the file data.
     *
     * @param $type
     * @param $attributes
     * @return File
     */
    protected function fileFactory($type, &$attributes)
    {
        $file = new File();

        if (!isset($attributes['formats'])) {
            return $file;
        }

        array_walk($attributes['formats'], function ($format) use ($file, $attributes) {
            $file->setStream(
                new FileStreamProxy($attributes['id'], $format['mime_type'], $this->api),
                $format['mime_type']
            );
        });

        return $file;
    }

    /**
     * Factory for creating a shipment item.
     *
     * @param $type
     * @param $attributes
     * @return ShipmentItem
     */
    protected function shipmentItemFactory($type, &$attributes)
    {
        $item = new ShipmentItem();

        if (isset($attributes['item_value']['amount'])) {
            $item->setItemValue($attributes['item_value']['amount']);
            $item->setCurrency($attributes['item_value']['currency']);

            unset($attributes['item_value']);
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function create($type, array $attributes = [])
    {
        return $this->hydrate(
            $this->createResource($type, $attributes),
            $attributes
        );
    }

    /**
     * Set a factory method or class string for given resource type.
     *
     * @param string          $type
     * @param callable|string $factory
     */
    public function setFactoryForType($type, $factory)
    {
        if (!is_callable($factory) && !class_exists($factory)) {
            throw new ResourceFactoryException(sprintf(
                'Cannot assign factory for type `%s`, given factory was not a valid callable or class',
                $type
            ));
        }

        $this->typeFactory[$type] = $factory;
    }

    /**
     * Checks if the given type has a factory associated with it.
     *
     * @param string $type
     * @return bool
     */
    protected function typeHasFactory($type)
    {
        return array_key_exists($type, $this->typeFactory);
    }

    /**
     * Create a resource for type using its factory or the class associated with it.
     *
     * @param string $type
     * @param array  $attributes
     * @throws ResourceFactoryException
     * @return object
     */
    protected function createResource($type, array &$attributes = [])
    {
        if (!$this->typeHasFactory($type)) {
            throw new ResourceFactoryException(sprintf(
                'Could not create resource of type `%s`, no class or factory specified',
                $type
            ));
        }

        $factory = $this->typeFactory[$type];

        if (is_callable($factory)) {
            return $factory($type, $attributes);
        } elseif (class_exists($factory)) {
            return new $factory();
        }

        throw new ResourceFactoryException(sprintf(
            'Could not determine how to create a resource of type `%s`, no factory method or class defined',
            $type
        ));
    }

    /**
     * Hydrates resource with given attributes. Uses reflection do determine if
     * other resources need to be created and tries to instantiate them where
     * possible.
     *
     * @todo Refactor this huge moth.
     *
     * @param object $resource
     * @param array  $attributes
     * @return object
     */
    protected function hydrate($resource, array $attributes)
    {
        array_walk($attributes, function ($value, $key) use ($resource) {
            $setter = 'set' . StringUtils::snakeToPascalCase($key);

            // Can't use setter if it doesn't exist.
            if (!method_exists($resource, $setter)) {
                return;
            }

            $param = $this->getFillableParam($resource, $setter);
            // Can't use the setter if we cannot determine the param to fill.
            if ($param === null) {
                return;
            }

            if ($param->isArray()) {
                // Can't use setter if the types don't match.
                if (!is_array($value)) {
                    return;
                }

                $adder = trim('add' . StringUtils::snakeToPascalCase($key), 's');

                if (($adderParam = $this->getFillableParam($resource, $adder)) !== null) {
                    $adderParamClass = $adderParam->getClass();


                    if ($adderParamClass !== null) {
                        $className = $adderParamClass->getName();

                        foreach ($value as $entry) {
                            if (is_array($entry) && $this->typeHasFactory($className)) {
                                $resource->$adder($this->create($className, $entry));

                                continue;
                            }
                            if ($entry instanceof $className) {
                                $resource->$adder($entry);

                                continue;
                            }
                        }

                        return;
                    }
                }
            }

            $paramClass = $param->getClass();
            if ($paramClass !== null) {
                $className = $paramClass->getName();

                if (is_array($value) && $this->typeHasFactory($className)) {
                    $resource->$setter($this->create($className, $value));

                    return;
                }

                if (!$value instanceof $className) {
                    return;
                }
            }

            $resource->$setter($value);
        });

        return $resource;
    }

    /**
     * @param mixed  $resource
     * @param string $method
     * @return ReflectionParameter|null
     */
    private function getFillableParam($resource, $method)
    {
        $params = (new \ReflectionMethod($resource, $method))
            ->getParameters();


        // Can't use setter if it requires no params.
        if (count($params) === 0) {
            return null;
        }

        // Check if all parameters after the 1st are optional. If not, we
        // cannot use the setter.
        foreach (array_slice($params, 1) as $param) {
            if (!$param->isOptional()) {
                return null;
            }
        }

        return reset($params);
    }

    /**
     * @param MyParcelComApiInterface $api
     * @return $this
     */
    public function setMyParcelComApi(MyParcelComApiInterface $api)
    {
        $this->api = $api;

        return $this;
    }
}
