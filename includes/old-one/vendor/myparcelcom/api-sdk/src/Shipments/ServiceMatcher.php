<?php

namespace MyParcelCom\ApiSdk\Shipments;

use MyParcelCom\ApiSdk\Exceptions\ServiceMatchingException;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionPriceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;

class ServiceMatcher
{
    /**
     * Returns true if given service can be used for the given shipment.
     *
     * @param ShipmentInterface $shipment
     * @param ServiceInterface  $service
     * @return bool
     */
    public function matches(ShipmentInterface $shipment, ServiceInterface $service)
    {
        return $this->matchesDeliveryMethod($shipment, $service)
            && ($weightContracts = $this->getMatchedWeightGroups($shipment, $service->getServiceContracts()))
            && ($optionContracts = $this->getMatchedOptions($shipment, $weightContracts))
            && $this->getMatchedInsurances($shipment, $optionContracts);
    }

    /**
     * Returns true if the service has a delivery method that matches the
     * shipment.
     *
     * @param ShipmentInterface $shipment
     * @param ServiceInterface  $service
     * @return bool
     */
    public function matchesDeliveryMethod(ShipmentInterface $shipment, ServiceInterface $service)
    {
        $deliveryMethod = $shipment->getPickupLocationCode()
            ? ServiceInterface::DELIVERY_METHOD_PICKUP
            : ServiceInterface::DELIVERY_METHOD_DELIVERY;

        return $service->getDeliveryMethod() === $deliveryMethod;
    }

    /**
     * Returns a subset of the given contracts that have weight groups that
     * match the weight of the shipment.
     *
     * @param ShipmentInterface          $shipment
     * @param ServiceContractInterface[] $serviceContracts
     * @return ServiceContractInterface[]
     */
    public function getMatchedWeightGroups(ShipmentInterface $shipment, array $serviceContracts)
    {
        if ($shipment->getWeight() < 0) {
            throw new ServiceMatchingException(
                'Cannot match a service to given shipment; negative weight given'
            );
        }

        $matches = [];
        foreach ($serviceContracts as $serviceContract) {
            foreach ($serviceContract->getServiceGroups() as $group) {
                if (($group->getWeightMin() <= $shipment->getWeight()
                        && $group->getWeightMax() >= $shipment->getWeight())
                    // If weight can be added on top of the set weight group,
                    // this group matches.
                    || ($group->getStepPrice() && $group->getStepSize())) {
                    $matches[] = $serviceContract;
                    continue 2;
                }
            }
        }

        return $matches;
    }

    /**
     * Returns a subset of the given contracts that have all the options that
     * the shipment requires.
     *
     * @param ShipmentInterface          $shipment
     * @param ServiceContractInterface[] $serviceContracts
     * @return ServiceContractInterface[]
     */
    public function getMatchedOptions(ShipmentInterface $shipment, array $serviceContracts)
    {
        $optionIds = array_map(function (ServiceOptionInterface $option) {
            return $option->getId();
        }, $shipment->getServiceOptions());

        $matches = [];
        foreach ($serviceContracts as $serviceContract) {
            $contractOptionIds = array_map(function (ServiceOptionPriceInterface $optionPrice) use ($optionIds) {
                return $optionPrice->getServiceOption()->getId();
            }, $serviceContract->getServiceOptionPrices());

            if (!array_diff($optionIds, $contractOptionIds)) {
                $matches[] = $serviceContract;
            }
        }

        return $matches;
    }

    /**
     * Returns a subset of the given contracts that can cover the desired
     * insurance of the shipment.
     *
     * @param ShipmentInterface          $shipment
     * @param ServiceContractInterface[] $serviceContracts
     * @return ServiceContractInterface[]
     */
    public function getMatchedInsurances(ShipmentInterface $shipment, array $serviceContracts)
    {
        if (!$shipment->getInsuranceAmount()) {
            return $serviceContracts;
        }

        if ($shipment->getInsuranceAmount() < 0) {
            throw new ServiceMatchingException(
                'Cannot match a service to given shipment; negative insurance amount given'
            );
        }

        return array_filter($serviceContracts, function (ServiceContractInterface $serviceContract) use ($shipment) {
            foreach ($serviceContract->getServiceInsurances() as $insurance) {
                if ($shipment->getInsuranceAmount() <= $insurance->getCovered()) {
                    return true;
                }
            }

            return false;
        });
    }
}
