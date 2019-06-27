<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

interface ServiceInterface extends ResourceInterface
{
    const PACKAGE_TYPE_PARCEL = 'parcel';
    const PACKAGE_TYPE_LETTER = 'letter';
    const PACKAGE_TYPE_LETTERBOX = 'letterbox';

    const DELIVERY_METHOD_DELIVERY = 'delivery';
    const DELIVERY_METHOD_PICKUP = 'pick-up';

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $packageType
     * @return $this
     */
    public function setPackageType($packageType);

    /**
     * @return string
     */
    public function getPackageType();

    /**
     * @return int|null
     */
    public function getTransitTimeMin();

    /**
     * @param int|null $transitTimeMin
     * @return $this
     */
    public function setTransitTimeMin($transitTimeMin);

    /**
     * @return int|null
     */
    public function getTransitTimeMax();

    /**
     * @param int|null $transitTimeMax
     * @return $this
     */
    public function setTransitTimeMax($transitTimeMax);

    /**
     * @param CarrierInterface $carrier
     * @return $this
     */
    public function setCarrier(CarrierInterface $carrier);

    /**
     * @return CarrierInterface
     */
    public function getCarrier();

    /**
     * @param RegionInterface $region
     * @return $this
     */
    public function setRegionFrom(RegionInterface $region);

    /**
     * @return RegionInterface
     */
    public function getRegionFrom();

    /**
     * @param RegionInterface $region
     * @return $this
     */
    public function setRegionTo(RegionInterface $region);

    /**
     * @return RegionInterface
     */
    public function getRegionTo();

    /**
     * @param string $handoverMethod
     * @return $this
     */
    public function setHandoverMethod($handoverMethod);

    /**
     * @return string
     */
    public function getHandoverMethod();

    /**
     * @param string[] $deliveryDays
     * @return $this
     */
    public function setDeliveryDays(array $deliveryDays);

    /**
     * @param string $deliveryDay
     * @return $this
     */
    public function addDeliveryDay($deliveryDay);

    /**
     * @return string[]
     */
    public function getDeliveryDays();

    /**
     * @return string
     */
    public function getDeliveryMethod();

    /**
     * @param string $deliveryMethod
     * @return $this
     */
    public function setDeliveryMethod($deliveryMethod);

    /**
     * @param ServiceRateInterface[] $serviceRates
     * @return $this
     */
    public function setServiceRates(array $serviceRates);

    /**
     * @param ServiceRateInterface $serviceRate
     * @return $this
     */
    public function addServiceRate(ServiceRateInterface $serviceRate);

    /**
     * @param array $filters
     * @return ServiceRateInterface[]
     */
    public function getServiceRates(array $filters = []);
}
