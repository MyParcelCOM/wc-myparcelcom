<?php

namespace MyParcelCom\ApiSdk\Shipments;

use MyParcelCom\ApiSdk\Exceptions\CalculationException;
use MyParcelCom\ApiSdk\Exceptions\InvalidResourceException;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceRateInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;

class PriceCalculator
{
    /**
     * Calculate the total price of given shipment based off its contract, service and weight.
     *
     * @param ShipmentInterface         $shipment
     * @param ServiceRateInterface|null $serviceRate
     * @return null|int
     */
    public function calculate(ShipmentInterface $shipment, ServiceRateInterface $serviceRate = null)
    {
        if ($serviceRate === null) {
            $serviceRate = $this->determineServiceRateForShipment($shipment);
        }

        if ($shipment->getPhysicalProperties() === null
            || $shipment->getPhysicalProperties()->getWeight() === null
            || $shipment->getPhysicalProperties()->getWeight() < $serviceRate->getWeightMin()
            || $shipment->getPhysicalProperties()->getWeight() > $serviceRate->getWeightMax()) {
            throw new CalculationException(
                'Could not calculate price for the given service rate since it does not support the shipment weight.'
            );
        }

        $serviceRatePrice = $serviceRate->getPrice();
        $serviceOptionPrice = $this->calculateOptionsPrice($shipment, $serviceRate);

        if ($serviceRatePrice === null || $serviceOptionPrice === null) {
            return null;
        }

        return $serviceOptionPrice + $serviceRatePrice;
    }

    /**
     * Calculate the price based on the selected options for given shipment.
     *
     * @param ShipmentInterface         $shipment
     * @param ServiceRateInterface|null $serviceRate
     * @return null|int
     */
    public function calculateOptionsPrice(ShipmentInterface $shipment, ServiceRateInterface $serviceRate = null)
    {
        if ($serviceRate === null) {
            $serviceRate = $this->determineServiceRateForShipment($shipment);
        }

        $price = 0;

        $prices = [];
        foreach ($serviceRate->getServiceOptions() as $option) {
            $prices[$option->getId()] = $option->getPrice();
        }

        foreach ($shipment->getServiceOptions() as $option) {
            if (!array_key_exists($option->getId(), $prices)) {
                throw new CalculationException(
                    'Cannot calculate a price for given shipment; invalid option: ' . $option->getId()
                );
            }

            $optionPrice = $prices[$option->getId()];

            if ($optionPrice === null) {
                return null;
            }

            $price += $optionPrice;
        }

        return (int)$price;
    }

    /**
     * @param ShipmentInterface $shipment
     * @throws InvalidResourceException
     */
    private function validateShipment(ShipmentInterface $shipment)
    {
        if ($shipment->getPhysicalProperties() === null
            || $shipment->getPhysicalProperties()->getWeight() === null
            || $shipment->getPhysicalProperties()->getWeight() < 0) {
            throw new InvalidResourceException(
                'Cannot calculate shipment price without a valid shipment weight.'
            );
        }

        if ($shipment->getContract() === null) {
            throw new InvalidResourceException(
                'Cannot calculate shipment price without a set contract.'
            );
        }
        if ($shipment->getService() === null) {
            throw new InvalidResourceException(
                'Cannot calculate shipment price without a set service.'
            );
        }
    }

    /**
     * @param ShipmentInterface $shipment
     * @return ServiceRateInterface
     */
    private function determineServiceRateForShipment(ShipmentInterface $shipment)
    {
        $this->validateShipment($shipment);

        $serviceRates = $shipment->getService()->getServiceRates([
            'contract' => $shipment->getContract(),
            'weight'   => $shipment->getPhysicalProperties()->getWeight(),
        ]);

        $shipmentOptionIds = array_map(function (ServiceOptionInterface $serviceOption) {
            return $serviceOption->getId();
        }, $shipment->getServiceOptions());

        array_filter($serviceRates, function (ServiceRateInterface $serviceRate) use ($shipmentOptionIds) {
            $serviceRateOptionIds = array_map(function (ServiceOptionInterface $serviceOption) {
                return $serviceOption->getId();
            }, $serviceRate->getServiceOptions());

            return empty(array_diff($shipmentOptionIds, $serviceRateOptionIds));
        });

        $serviceRate = reset($serviceRates);

        if (!$serviceRate) {
            throw new CalculationException(
                'Cannot find a matching service rate for given shipment'
            );
        }

        return $serviceRate;
    }
}
