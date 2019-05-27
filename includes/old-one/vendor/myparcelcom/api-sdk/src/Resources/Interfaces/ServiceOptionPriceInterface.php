<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

interface ServiceOptionPriceInterface extends ResourceInterface
{
    /**
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * @param int $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * @return int
     */
    public function getPrice();

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
     * @param bool $required
     * @return $this
     */
    public function setRequired($required);

    /**
     * @return bool
     */
    public function isRequired();

    /**
     * @param ServiceContractInterface $serviceContract
     * @return $this
     */
    public function setServiceContract(ServiceContractInterface $serviceContract);

    /**
     * @return ServiceContractInterface
     */
    public function getServiceContract();

    /**
     * @param ServiceOptionInterface $serviceOption
     * @return $this
     */
    public function setServiceOption(ServiceOptionInterface $serviceOption);

    /**
     * @return ServiceOptionInterface
     */
    public function getServiceOption();
}
