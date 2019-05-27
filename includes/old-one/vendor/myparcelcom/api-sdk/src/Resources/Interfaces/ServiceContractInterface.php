<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

interface ServiceContractInterface extends ResourceInterface
{
    /**
     * @param string $id
     * @return $this
     */
    public function setId($id);

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
     * @param CarrierContractInterface $carrierContract
     * @return $this
     */
    public function setCarrierContract(CarrierContractInterface $carrierContract);

    /**
     * @return CarrierContractInterface
     */
    public function getCarrierContract();

    /**
     * @param ServiceGroupInterface[] $groups
     * @return $this
     */
    public function setServiceGroups(array $groups);

    /**
     * @param ServiceGroupInterface $group
     * @return $this
     */
    public function addServiceGroup(ServiceGroupInterface $group);

    /**
     * @return ServiceGroupInterface[]
     */
    public function getServiceGroups();

    /**
     * @param ServiceOptionPriceInterface[] $options
     * @return $this
     */
    public function setServiceOptionPrices(array $options);

    /**
     * @param ServiceOptionPriceInterface $option
     * @return $this
     */
    public function addServiceOptionPrice(ServiceOptionPriceInterface $option);

    /**
     * @return ServiceOptionPriceInterface[]
     */
    public function getServiceOptionPrices();

    /**
     * @param ServiceInsuranceInterface[] $insurances
     * @return $this
     */
    public function setServiceInsurances(array $insurances);

    /**
     * @param ServiceInsuranceInterface $insurance
     * @return $this
     */
    public function addServiceInsurance(ServiceInsuranceInterface $insurance);

    /**
     * @return ServiceInsuranceInterface[]
     */
    public function getServiceInsurances();
}
