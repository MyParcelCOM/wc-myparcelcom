<?php

namespace MyParcelCom\ApiSdk\Tests\Traits;

use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceGroupInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInsuranceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionPriceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;

trait MocksContract
{
    protected function getMockedServiceContract(array $groups = [], array $insurances = [], array $options = [])
    {
        $contract = $this->getMockBuilder(ServiceContractInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $contract->method('getServiceGroups')
            ->willReturn($this->getMockedServiceGroups($groups));
        $contract->method('getServiceInsurances')
            ->willReturn($this->getMockedServiceInsurances($insurances));
        $contract->method('getServiceOptionPrices')
            ->willReturn($this->getMockedServiceOptionPrices($options));

        return $contract;
    }

    /**
     * @param int                      $weight
     * @param int                      $insurance
     * @param array                    $options
     * @param ServiceContractInterface $contract
     * @return ShipmentInterface
     */
    protected function getMockedShipment($weight = 5000, $insurance = 0, array $options = [], ServiceContractInterface $contract = null)
    {
        $shipment = $this->getMockBuilder(ShipmentInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $shipment->method('getServiceContract')
            ->willReturn($contract);
        $shipment->method('getWeight')
            ->willReturn($weight);
        $shipment->method('getInsuranceAmount')
            ->willReturn($insurance);

        $optionMocks = array_map(function ($option) {
            $optionMock = $this->getMockBuilder(ServiceOptionInterface::class)
                ->disableOriginalConstructor()
                ->disableOriginalClone()
                ->disableArgumentCloning()
                ->disallowMockingUnknownTypes()
                ->getMock();
            $optionMock->method('getId')
                ->willReturn($option);

            return $optionMock;
        }, $options);

        $shipment->method('getServiceOptions')
            ->willReturn($optionMocks);

        /** @var ShipmentInterface $shipment */
        return $shipment;
    }


    /**
     * @param array $groups
     * @return ServiceGroupInterface[]
     */
    protected function getMockedServiceGroups(array $groups)
    {
        $groupMockBuilder = $this->getMockBuilder(ServiceGroupInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes();

        $groupMocks = [];
        foreach ($groups as $group) {
            $groupMock = $groupMockBuilder->getMock();
            $groupMock->method('getWeightMin')
                ->willReturn($group['weight_min']);
            $groupMock->method('getWeightMax')
                ->willReturn($group['weight_max']);
            $groupMock->method('getCurrency')
                ->willReturn('EUR');
            $groupMock->method('getPrice')
                ->willReturn($group['price']);
            $groupMock->method('getStepSize')
                ->willReturn($group['step_size']);
            $groupMock->method('getStepPrice')
                ->willReturn($group['step_price']);
            $groupMocks[] = $groupMock;
        }

        return $groupMocks;
    }

    /**
     * @param array $insurances
     * @return ServiceInsuranceInterface[]
     */
    protected function getMockedServiceInsurances(array $insurances)
    {
        $insuranceMockBuilder = $this->getMockBuilder(ServiceInsuranceInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes();

        $insuranceMocks = [];
        foreach ($insurances as $insurance) {
            $insuranceMock = $insuranceMockBuilder->getMock();
            $insuranceMock->method('getCovered')
                ->willReturn($insurance['covered']);
            $insuranceMock->method('getCurrency')
                ->willReturn('EUR');
            $insuranceMock->method('getPrice')
                ->willReturn($insurance['price']);
            $insuranceMocks[] = $insuranceMock;
        }

        return $insuranceMocks;
    }

    /**
     * @param array $options
     * @return ServiceOptionInterface[]
     */
    protected function getMockedServiceOptionPrices(array $options)
    {
        $serviceOptionMockBuilder = $this->getMockBuilder(ServiceOptionInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes();
        $serviceOptionPriceMockBuilder = $this->getMockBuilder(ServiceOptionPriceInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes();

        $serviceMocks = [];
        foreach ($options as $option) {
            $serviceOptionPriceMock = $serviceOptionPriceMockBuilder->getMock();
            $serviceOptionMock = $serviceOptionMockBuilder->getMock();

            $serviceOptionMock->method('getId')
                ->willReturn($option['id']);

            $serviceOptionPriceMock->method('getCurrency')
                ->willReturn('EUR');
            $serviceOptionPriceMock->method('getPrice')
                ->willReturn($option['price']);
            $serviceOptionPriceMock->method('getServiceOption')
                ->willReturn($serviceOptionMock);

            $serviceMocks[] = $serviceOptionPriceMock;
        }

        return $serviceMocks;
    }
}
