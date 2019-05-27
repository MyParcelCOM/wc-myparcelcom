<?php

namespace MyParcelCom\ApiSdk\Shipments;

use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;

class ContractSelector
{
    /**
     * Choose the cheapest contract for given shipment.
     *
     * @param ShipmentInterface          $shipment
     * @param ServiceContractInterface[] $serviceContracts
     * @return ServiceContractInterface
     */
    public function selectCheapest(ShipmentInterface $shipment, array $serviceContracts)
    {
        $prices = [];

        $matcher = new ServiceMatcher();
        $matchingContracts = $matcher->getMatchedOptions(
            $shipment,
            $matcher->getMatchedWeightGroups($shipment, $serviceContracts)
        );

        $calculator = new PriceCalculator();
        foreach ($matchingContracts as $contract) {
            $prices[$calculator->calculate($shipment, $contract)] = $contract;
        }

        ksort($prices);

        return reset($prices);
    }
}
