<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

interface ServiceRateInterface extends ResourceInterface
{
    /**
     * @param int $weightMin
     * @return $this
     */
    public function setWeightMin($weightMin);

    /**
     * @return int
     */
    public function getWeightMin();

    /**
     * @param int $weightMax
     * @return $this
     */
    public function setWeightMax($weightMax);

    /**
     * @return int
     */
    public function getWeightMax();

    /**
     * @param array $weightBracket
     */
    public function setWeightBracket($weightBracket);

    /**
     * @return array
     */
    public function getWeightBracket();

    /**
     * @param int $weight
     * @return int|null
     */
    public function calculateBracketPrice($weight);

    /**
     * @param int $lengthMax
     * @return $this
     */
    public function setLengthMax($lengthMax);

    /**
     * @return int
     */
    public function getLengthMax();

    /**
     * @param int $heightMax
     * @return $this
     */
    public function setHeightMax($heightMax);

    /**
     * @return int
     */
    public function getHeightMax();

    /**
     * @param int $widthMax
     * @return $this
     */
    public function setWidthMax($widthMax);

    /**
     * @return int
     */
    public function getWidthMax();

    /**
     * @param int $volumeMax
     * @return $this
     */
    public function setVolumeMax($volumeMax);

    /**
     * @return int
     */
    public function getVolumeMax();

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency);

    /**
     * @return string
     */
    public function getCurrency();

    /**
     * @param int $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * @return null|int
     */
    public function getPrice();

    /**
     * @param int $amount
     * @return $this
     */
    public function setFuelSurchargeAmount($amount);

    /**
     * @return null|int
     */
    public function getFuelSurchargeAmount();

    /**
     * @param string $currency
     * @return $this
     */
    public function setFuelSurchargeCurrency($currency);

    /**
     * @return string
     */
    public function getFuelSurchargeCurrency();

    /**
     * @param ServiceInterface $service
     * @return $this
     */
    public function setService(ServiceInterface $service);

    /**
     * @return ServiceInterface
     */
    public function getService();

    /**
     * @param ContractInterface $contract
     * @return $this
     */
    public function setContract(ContractInterface $contract);

    /**
     * @return ContractInterface
     */
    public function getContract();

    /**
     * @param ServiceOptionInterface[] $serviceOptions
     * @return $this
     */
    public function setServiceOptions($serviceOptions);

    /**
     * @param ServiceOptionInterface $serviceOption
     * @return $this
     */
    public function addServiceOption(ServiceOptionInterface $serviceOption);

    /**
     * @return ServiceOptionInterface[]
     */
    public function getServiceOptions();

    /**
     * @param bool $isDynamic
     * @return $this
     */
    public function setIsDynamic($isDynamic);

    /**
     * @return bool
     */
    public function isDynamic();

    /**
     * @return ServiceRateInterface
     */
    public function resolveDynamicRateForShipment(ShipmentInterface $shipment);
}
