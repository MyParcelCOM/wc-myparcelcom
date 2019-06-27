<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Exceptions\CalculationException;
use MyParcelCom\ApiSdk\Exceptions\InvalidResourceException;
use MyParcelCom\ApiSdk\Resources\Interfaces\PhysicalPropertiesInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Shipments\PriceCalculator;
use MyParcelCom\ApiSdk\Tests\Traits\MocksContract;
use PHPUnit\Framework\TestCase;

class PriceCalculatorTest extends TestCase
{
    use MocksContract;

    /** @test */
    public function testItCalculatesTheTotalPriceOfAShipment()
    {
        $serviceOptionMocks = [
            $this->getMockedServiceOption('service-option-id-uno', 250),
            $this->getMockedServiceOption('service-option-id-dos', 850),
        ];
        $serviceRateMock = $this->getMockedServiceRate($serviceOptionMocks, 5000, 0, 5000);
        $serviceMock = $this->getMockedService([$serviceRateMock]);
        $shipment = $this->getMockedShipment(1337, $serviceMock, $serviceOptionMocks);

        $priceCalculator = new PriceCalculator();
        $this->assertEquals(6100, $priceCalculator->calculate($shipment));
    }

    /** @test */
    public function testItCalculatesTheOptionsPriceForAShipment()
    {
        $serviceOptionMocks = [
            $this->getMockedServiceOption('service-option-id-uno', 250),
            $this->getMockedServiceOption('service-option-id-dos', 850),
        ];
        $serviceRateMock = $this->getMockedServiceRate($serviceOptionMocks, 5000, 0, 5000);
        $serviceMock = $this->getMockedService([$serviceRateMock]);
        $shipment = $this->getMockedShipment(1337, $serviceMock, $serviceOptionMocks);

        $priceCalculator = new PriceCalculator();
        $this->assertEquals(1100, $priceCalculator->calculateOptionsPrice($shipment));
    }

    /** @test */
    public function testItTotalPriceIsNullWhenOptionPriceIsNull()
    {
        $serviceOptionMocks = [
            $this->getMockedServiceOption('service-option-id-uno', 250),
            $this->getMockedServiceOption('service-option-id-dos', 400),
            $this->getMockedServiceOption('service-option-id-tres', null),
        ];
        $serviceRateMock = $this->getMockedServiceRate($serviceOptionMocks, 5000, 0, 5000);
        $serviceMock = $this->getMockedService([$serviceRateMock]);
        $shipment = $this->getMockedShipment(1337, $serviceMock, $serviceOptionMocks);

        $priceCalculator = new PriceCalculator();
        $this->assertNull($priceCalculator->calculate($shipment));
    }

    /** @test */
    public function testItReturnsNullForNotPricedServices()
    {
        $serviceRateMock = $this->getMockedServiceRate([], null, 0, 5000);
        $serviceMock = $this->getMockedService([$serviceRateMock]);
        $shipment = $this->getMockedShipment(1337, $serviceMock, []);

        $priceCalculator = new PriceCalculator();
        $this->assertNull($priceCalculator->calculate($shipment));
    }

    /** @test */
    public function testItCalculatesThePriceOfShipmentsWithoutServiceOptions()
    {
        $serviceRateMock = $this->getMockedServiceRate([], 3914, 0, 5000);
        $serviceMock = $this->getMockedService([$serviceRateMock]);
        $shipment = $this->getMockedShipment(1337, $serviceMock);

        $priceCalculator = new PriceCalculator();
        $this->assertEquals(3914, $priceCalculator->calculate($shipment));
    }

    /** @test */
    public function testItThrowsAnExceptionWhenNoServiceRateCanBeMatchedForShipment()
    {
        $serviceMock = $this->getMockedService();
        $shipment = $this->getMockedShipment(1233, $serviceMock);

        $priceCalculator = new PriceCalculator();

        $this->expectException(CalculationException::class);
        $this->expectExceptionMessage('Cannot find a matching service rate for given shipment');
        $priceCalculator->calculate($shipment);
    }

    /** @test */
    public function testItThrowsAnExceptionWhenShipmentHasInvalidOptions()
    {
        $serviceOptionMocks = [
            $this->getMockedServiceOption('service-option-id-uno', 250),
            $this->getMockedServiceOption('service-option-id-dos', 850),
        ];
        $serviceRateMock = $this->getMockedServiceRate([], 5000, 0, 5000);
        $serviceMock = $this->getMockedService([$serviceRateMock]);

        $shipment = $this->getMockedShipment(1337, $serviceMock, $serviceOptionMocks);

        $priceCalculator = new PriceCalculator();

        $this->expectException(CalculationException::class);
        $this->expectExceptionMessage('Cannot calculate a price for given shipment; invalid option: ');
        $priceCalculator->calculate($shipment);
    }

    /** @test */
    public function testItThrowsAnExceptionIfShipmentDoesNotHaveWeightSet()
    {
        $serviceMock = $this->getMockedService();
        $shipment = $this->getMockedShipment(null, $serviceMock);

        $priceCalculator = new PriceCalculator();

        $this->expectException(InvalidResourceException::class);
        $this->expectExceptionMessage('Cannot calculate shipment price without a valid shipment weight.');
        $priceCalculator->calculate($shipment);
    }

    /** @test */
    public function testItThrowsAnExceptionIfShipmentWeightIsNotInRangeOfServiceRateWeightLimits()
    {
        $serviceRateMock = $this->getMockedServiceRate([], 3914, 0, 5000);
        $serviceMock = $this->getMockedService([$serviceRateMock]);
        $shipment = $this->getMockedShipment(999999, $serviceMock);

        $priceCalculator = new PriceCalculator();

        $this->expectException(CalculationException::class);
        $this->expectExceptionMessage(
            'Could not calculate price for the given service rate since it does not support the shipment weight.'
        );
        $priceCalculator->calculate($shipment);
    }

    /** @test */
    public function testItThrowsAnExceptionIfShipmentWeightIsNegative()
    {
        $serviceRateMock = $this->getMockedServiceRate();
        $serviceMock = $this->getMockedService([$serviceRateMock]);
        $shipment = $this->getMockedShipment(-545, $serviceMock);

        $priceCalculator = new PriceCalculator();

        $this->expectException(InvalidResourceException::class);
        $this->expectExceptionMessage(
            'Cannot calculate shipment price without a valid shipment weight.'
        );
        $priceCalculator->calculate($shipment);
    }

    /** @test */
    public function testItThrowsAnExceptionWhenCalculatingShipmentPriceButNoServiceIsSet()
    {
        $shipment = $this->getMockedShipment(5000, null, []);

        $calculator = new PriceCalculator();
        $this->expectException(InvalidResourceException::class);
        $this->expectExceptionMessage('Cannot calculate shipment price without a set service.');
        $calculator->calculate($shipment);
    }

    /** @test */
    public function testItThrowsAnExceptionWhenCalculatingShipmentPriceButNoContractIsSet()
    {
        /** @var ShipmentInterface $shipment */
        $shipment = $this->createMock(ShipmentInterface::class);
        $physicalProperties = $this->createMock(PhysicalPropertiesInterface::class);
        $physicalProperties->method('getWeight')->willReturn(1234);

        $shipment->method('getPhysicalProperties')->willReturn($physicalProperties);
        $shipment->method('getService')->willReturn($this->createMock(ServiceInterface::class));

        $shipment->method('getContract')->willReturn(null);

        $calculator = new PriceCalculator();
        $this->expectException(InvalidResourceException::class);
        $this->expectExceptionMessage('Cannot calculate shipment price without a set contract.');
        $calculator->calculate($shipment);
    }
}
