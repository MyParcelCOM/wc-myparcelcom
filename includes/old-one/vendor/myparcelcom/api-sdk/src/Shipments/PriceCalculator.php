<?php

namespace MyParcelCom\ApiSdk\Shipments;

use MyParcelCom\ApiSdk\Exceptions\CalculationException;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceGroupInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInsuranceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;

class PriceCalculator
{
    /**
     * Calculate the total price of given shipment. Optionally a contract can be
     * supplied for the price calculations. If no contract is given, the
     * contract on the shipment is used.
     *
     * @param ShipmentInterface             $shipment
     * @param ServiceContractInterface|null $contract
     * @return int
     */
    public function calculate(ShipmentInterface $shipment, ServiceContractInterface $contract = null)
    {
        if ($contract === null) {
            $contract = $shipment->getServiceContract();
        }

        if ($contract === null) {
            throw new CalculationException(
                'Cannot calculate a price for given shipment without a contract'
            );
        }

        return $this->calculateGroupPrice($shipment, $contract)
            + $this->calculateOptionsPrice($shipment, $contract)
            + $this->calculateInsurancePrice($shipment, $contract);
    }

    /**
     * Calculate the price based on the weight group for given shipment.
     * Optionally a contract can be supplied for the price calculations. If no
     * contract is given, the contract on the shipment is used.
     *
     * @param ShipmentInterface             $shipment
     * @param ServiceContractInterface|null $contract
     * @return int
     */
    public function calculateGroupPrice(ShipmentInterface $shipment, ServiceContractInterface $contract = null)
    {
        if ($contract === null) {
            $contract = $shipment->getServiceContract();
        }

        if ($contract === null) {
            throw new CalculationException(
                'Cannot calculate a price for given shipment without a contract'
            );
        }

        if ($shipment->getWeight() < 0) {
            throw new CalculationException(
                'Cannot calculate a price for given shipment; negative weight given'
            );
        }

        $price = 0;

        // Order all the groups to have the highest weight group last.
        $groups = $contract->getServiceGroups();
        @usort($groups, function (ServiceGroupInterface $a, ServiceGroupInterface $b) {
            // Sort based on the min weight;
            $minDiff = $a->getWeightMin() - $b->getWeightMin();
            if ($minDiff !== 0) {
                return $minDiff;
            }

            // If the min weight of both groups are equal, sort by max weight.
            $maxDiff = $a->getWeightMax() - $b->getWeightMax();
            if ($maxDiff !== 0) {
                return $maxDiff;
            }

            // If the max weights are equal, sort by step size.
            $stepDiff = $a->getStepSize() - $b->getStepSize();
            if ($stepDiff !== 0) {
                return $stepDiff;
            }

            // If the step sizes are equal, sort by price.
            return $a->getPrice() - $b->getPrice();
        });

        // Find the weight group this shipment is in.
        foreach ($groups as $group) {
            if ($shipment->getWeight() >= $group->getWeightMin()
                && $shipment->getWeight() <= $group->getWeightMax()) {
                break;
            }
        }

        // Add the group price to the price
        $price += $group->getPrice();

        // Add any weight over the max to the price.
        if ($shipment->getWeight() > $group->getWeightMax()
            && $group->getStepSize()
            && $group->getStepPrice()) {
            $price +=
                ceil(($shipment->getWeight() - $group->getWeightMax()) / $group->getStepSize())
                * $group->getStepPrice();
        } elseif ($shipment->getWeight() > $group->getWeightMax() || $shipment->getWeight() < $group->getWeightMin()) {
            throw new CalculationException(
                'Cannot calculate a price for given shipment; contract did not contain weight prices for: ' . $shipment->getWeight()
            );
        }

        return (int)$price;
    }

    /**
     * Calculate the price based on the selected options for given shipment.
     * Optionally a contract can be supplied for the price calculations. If no
     * contract is given, the contract on the shipment is used.
     *
     * @param ShipmentInterface             $shipment
     * @param ServiceContractInterface|null $contract
     * @return int
     */
    public function calculateOptionsPrice(ShipmentInterface $shipment, ServiceContractInterface $contract = null)
    {
        if ($contract === null) {
            $contract = $shipment->getServiceContract();
        }

        if ($contract === null) {
            throw new CalculationException('Cannot calculate a price for given shipment without a contract');
        }

        $price = 0;

        $optionPrices = [];
        foreach ($contract->getServiceOptionPrices() as $option) {
            $optionPrices[$option->getServiceOption()->getId()] = $option->getPrice();
        }

        foreach ($shipment->getServiceOptions() as $option) {
            if (!isset($optionPrices[$option->getId()])) {
                throw new CalculationException(
                    'Cannot calculate a price for given shipment; invalid option: ' . $option->getId()
                );
            }

            $price += $optionPrices[$option->getId()];
        }

        return (int)$price;
    }

    /**
     * Calculate the price based on the desired insurance for given shipment.
     * Optionally a contract can be supplied for the price calculations. If no
     * contract is given, the contract on the shipment is used.
     *
     * @param ShipmentInterface             $shipment
     * @param ServiceContractInterface|null $contract
     * @return int
     */
    public function calculateInsurancePrice(ShipmentInterface $shipment, ServiceContractInterface $contract = null)
    {
        if ($contract === null) {
            $contract = $shipment->getServiceContract();
        }

        if ($contract === null) {
            throw new CalculationException('Cannot calculate a price for given shipment without a contract');
        }

        if (!$shipment->getInsuranceAmount()) {
            return 0;
        }

        if ($shipment->getInsuranceAmount() < 0) {
            throw new CalculationException(
                'Cannot calculate a price for given shipment; negative insurance set'
            );
        }

        if (!($insurances = $contract->getServiceInsurances())) {
            throw new CalculationException(
                'Cannot calculate a price for given shipment; no insurances are available in contract'
            );
        }

        @usort($insurances, function (ServiceInsuranceInterface $a, ServiceInsuranceInterface $b) {
            return $a->getCovered() - $b->getCovered();
        });

        foreach ($insurances as $insurance) {
            if ($shipment->getInsuranceAmount() <= $insurance->getCovered()) {
                break;
            }
        }

        if ($shipment->getInsuranceAmount() > $insurance->getCovered()) {
            throw new CalculationException(
                'Cannot calculate a price for given shipment; no insurances are available in contract for amount: '
                . $shipment->getInsuranceAmount()
            );
        }

        return $insurance->getPrice();
    }
}
