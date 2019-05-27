<?php

namespace MyParcelCom\ApiSdk\Shipments;

use MyParcelCom\ApiSdk\Exceptions\InvalidResourceException;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceRateInterface;
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
        if ($shipment->getPhysicalProperties() === null
            || $shipment->getPhysicalProperties()->getWeight() === null
            || $shipment->getPhysicalProperties()->getWeight() < 0) {
            throw new InvalidResourceException(
                'Cannot match shipment and service without a valid shipment weight.'
            );
        }

        // TODO: Add check for matching regions. We need to implement ancestor regions in the SDK in order to do this.
        return $this->matchesDeliveryMethod($shipment, $service)
            && ($serviceRates = $service->getServiceRates([
                'weight' => $shipment->getPhysicalProperties()->getWeight(),
            ]))
            && $this->getMatchedOptions($shipment, $serviceRates);
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
     * Returns a subset of the given service rates that have all the options that
     * the shipment requires.
     *
     * @param ShipmentInterface      $shipment
     * @param ServiceRateInterface[] $serviceRates
     * @return ServiceRateInterface[]
     */
    public function getMatchedOptions(ShipmentInterface $shipment, array $serviceRates)
    {
        $optionIds = array_map(function (ServiceOptionInterface $option) {
            return $option->getId();
        }, $shipment->getServiceOptions());

        $matches = [];
        foreach ($serviceRates as $serviceRate) {
            $contractOptionIds = array_map(function (ServiceOptionInterface $option) use ($optionIds) {
                return $option->getId();
            }, $serviceRate->getServiceOptions());

            if (!array_diff($optionIds, $contractOptionIds)) {
                $matches[] = $serviceRate;
            }
        }

        return $matches;
    }
}
