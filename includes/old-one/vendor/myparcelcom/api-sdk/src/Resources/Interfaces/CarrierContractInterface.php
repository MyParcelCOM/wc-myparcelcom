<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

interface CarrierContractInterface extends ResourceInterface
{
    /**
     * @param string $id
     * @return $this
     */
    public function setId($id);

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
     * @param CarrierInterface $carrier
     * @return $this
     */
    public function setCarrier(CarrierInterface $carrier);

    /**
     * @return CarrierInterface
     */
    public function getCarrier();

    /**
     * @param ServiceContractInterface[] $serviceContracts
     * @return $this
     */
    public function setServiceContracts(array $serviceContracts);

    /**
     * @param ServiceContractInterface $serviceContract
     * @return $this
     */
    public function addServiceContract(ServiceContractInterface $serviceContract);

    /**
     * @return ServiceContractInterface[]
     */
    public function getServiceContracts();
}
