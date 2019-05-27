<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Exceptions\CalculationException;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Shipments\PriceCalculator;
use MyParcelCom\ApiSdk\Tests\Traits\MocksContract;
use PHPUnit\Framework\TestCase;

class PriceCalculatorTest extends TestCase
{
    use MocksContract;

    /** @test */
    public function testCalculateGroupPrice()
    {
        $contract = $this->getMockedServiceContract([
            [
                'weight_min' => 0,
                'weight_max' => 5000,
                'price'      => 250,
                'step_size'  => 0,
                'step_price' => 0,
            ],
            [
                'weight_min' => 5001,
                'weight_max' => 10000,
                'price'      => 400,
                'step_size'  => 0,
                'step_price' => 0,
            ],
            [
                'weight_min' => 10001,
                'weight_max' => 20000,
                'price'      => 750,
                'step_size'  => 0,
                'step_price' => 0,
            ],
            [
                'weight_min' => 20001,
                'weight_max' => 50000,
                'price'      => 999,
                'step_size'  => 1000,
                'step_price' => 100,
            ],
        ]);

        $calculator = new PriceCalculator();

        $this->assertEquals(
            250,
            $calculator->calculateGroupPrice($this->getMockedShipment(0, 0, [], $contract))
        );

        $this->assertEquals(
            250,
            $calculator->calculateGroupPrice($this->getMockedShipment(2000, 0, [], $contract))
        );

        $this->assertEquals(
            400,
            $calculator->calculateGroupPrice($this->getMockedShipment(7500, 0, [], $contract))
        );

        $this->assertEquals(
            1499,
            $calculator->calculateGroupPrice($this->getMockedShipment(55000, 0, [], $contract))
        );

        $this->assertEquals(
            99999995999,
            $calculator->calculateGroupPrice($this->getMockedShipment(999999999999, 0, [], $contract))
        );

        $this->expectException(CalculationException::class);
        $calculator->calculateGroupPrice($this->getMockedShipment(-5000, 0, [], $contract));
    }

    /** @test */
    public function testCalculateGroupPriceWeightTooLow()
    {
        $contract = $this->getMockedServiceContract([
            [
                'weight_min' => 2001,
                'weight_max' => 7500,
                'price'      => 695,
                'step_size'  => 0,
                'step_price' => 0,
            ],
        ]);

        $calculator = new PriceCalculator();

        $this->expectException(CalculationException::class);
        $calculator->calculateGroupPrice($this->getMockedShipment(1000, 0, [], $contract));
    }

    /** @test */
    public function testCalculateGroupPriceWeightTooHigh()
    {
        $contract = $this->getMockedServiceContract([
            [
                'weight_min' => 3500,
                'weight_max' => 3600,
                'price'      => 799,
                'step_size'  => 0,
                'step_price' => 0,
            ],
            [
                'weight_min' => 10000,
                'weight_max' => 68777,
                'price'      => 799,
                'step_size'  => 0,
                'step_price' => 0,
            ],
            [
                'weight_min' => 3601,
                'weight_max' => 9999,
                'price'      => 799,
                'step_size'  => 0,
                'step_price' => 0,
            ],
        ]);
        $calculator = new PriceCalculator();

        $this->expectException(CalculationException::class);
        $calculator->calculateGroupPrice($this->getMockedShipment(98544, 0, [], $contract));
    }

    /** @test */
    public function testCalculateInsurancePrice()
    {
        $contract = $this->getMockedServiceContract([], [
            [
                'covered' => 1000,
                'price'   => 99,
            ],
            [
                'covered' => 2000,
                'price'   => 500,
            ],
            [
                'covered' => 123456,
                'price'   => 789,
            ],
            [
                'covered' => 100100,
                'price'   => 600,
            ],
        ]);

        $calculator = new PriceCalculator();

        $this->assertEquals(
            99,
            $calculator->calculateInsurancePrice($this->getMockedShipment(10, 587, [], $contract))
        );
        $this->assertEquals(
            500,
            $calculator->calculateInsurancePrice($this->getMockedShipment(20, 1177, [], $contract))
        );
        $this->assertEquals(
            600,
            $calculator->calculateInsurancePrice($this->getMockedShipment(30, 100000, [], $contract))
        );
        $this->assertEquals(
            789,
            $calculator->calculateInsurancePrice($this->getMockedShipment(40, 123456, [], $contract))
        );
    }

    /** @test */
    public function testCalculateInsurancePriceTooHighInsurance()
    {
        $contract = $this->getMockedServiceContract([], [
            [
                'covered' => 100,
                'price'   => 101,
            ],
            [
                'covered' => 987654,
                'price'   => 12,
            ],
            [
                'covered' => 13456,
                'price'   => 79,
            ],
        ]);

        $calculator = new PriceCalculator();

        $this->expectException(CalculationException::class);
        $calculator->calculateInsurancePrice($this->getMockedShipment(50, 99999999999999999999, [], $contract));
    }

    /** @test */
    public function testCalculateInsurancePriceNegativeInsurance()
    {
        $contract = $this->getMockedServiceContract([], [
            [
                'covered' => 654,
                'price'   => 121,
            ],
            [
                'covered' => 1456,
                'price'   => 739,
            ],
        ]);

        $calculator = new PriceCalculator();

        $this->expectException(CalculationException::class);
        $calculator->calculateInsurancePrice($this->getMockedShipment(50, -124, [], $contract));
    }

    /** @test */
    public function testCalculateOptionsPrice()
    {
        $contract = $this->getMockedServiceContract([], [], [
            [
                'id'    => 'option-a',
                'price' => 123,
            ],
            [
                'id'    => 'option-b',
                'price' => 738,
            ],
        ]);

        $calculator = new PriceCalculator();

        $this->assertEquals(
            123,
            $calculator->calculateOptionsPrice($this->getMockedShipment(50, 0, ['option-a'], $contract))
        );
        $this->assertEquals(
            738,
            $calculator->calculateOptionsPrice($this->getMockedShipment(50, 0, ['option-b'], $contract))
        );
        $this->assertEquals(
            861,
            $calculator->calculateOptionsPrice($this->getMockedShipment(50, 0, ['option-a', 'option-b'], $contract))
        );
    }

    /** @test */
    public function testCalculateOptionsPriceInvalidOption()
    {
        $contract = $this->getMockedServiceContract([], [], [
            [
                'id'    => 'option-a',
                'price' => 1233,
            ],
            [
                'id'    => 'option-b',
                'price' => 73,
            ],
            [
                'id'    => 'option-c',
                'price' => 789,
            ],
        ]);

        $calculator = new PriceCalculator();

        $this->expectException(CalculationException::class);
        $calculator->calculateOptionsPrice($this->getMockedShipment(50, 0, ['option-q'], $contract));
    }

    /** @test */
    public function testCalculate()
    {
        $contract = $this->getMockedServiceContract([
            [
                'weight_min' => 3500,
                'weight_max' => 3600,
                'price'      => 779,
                'step_size'  => 0,
                'step_price' => 0,
            ],
            [
                'weight_min' => 10000,
                'weight_max' => 68777,
                'price'      => 799,
                'step_size'  => 100000,
                'step_price' => 10000,
            ],
            [
                'weight_min' => 3601,
                'weight_max' => 9999,
                'price'      => 799,
                'step_size'  => 0,
                'step_price' => 0,
            ],
            [
                'weight_min' => 0,
                'weight_max' => 3499,
                'price'      => 12,
                'step_size'  => 0,
                'step_price' => 0,
            ],
        ], [
            [
                'covered' => 654,
                'price'   => 121,
            ],
            [
                'covered' => 1456,
                'price'   => 739,
            ],
        ], [
            [
                'id'    => 'option-a',
                'price' => 1233,
            ],
            [
                'id'    => 'option-b',
                'price' => 73,
            ],
            [
                'id'    => 'option-c',
                'price' => 789,
            ],
        ]);

        $calculator = new PriceCalculator();

        $this->assertEquals(
            73 + 739 + 799,
            $calculator->calculate($this->getMockedShipment(30012, 1245, ['option-b'], $contract))
        );

        $this->assertEquals(
            12 + 121,
            $calculator->calculate($this->getMockedShipment(30, 100, [], $contract))
        );

        $this->assertEquals(
            779,
            $calculator->calculate($this->getMockedShipment(3515, 0, [], $contract))
        );

        $this->assertEquals(
            799 + 121 + 1233 + 789,
            $calculator->calculate($this->getMockedShipment(9000, 157, ['option-a', 'option-c'], $contract))
        );
    }

    /** @test */
    public function testCalculateException()
    {
        $calculator = new PriceCalculator();
        $shipment = $this->createMock(ShipmentInterface::class);
        $shipment->method('getServiceContract')->willReturn(null);

        $this->expectException(CalculationException::class);
        $calculator->calculate($shipment);
    }

    /** @test */
    public function testCalculateGroupPriceException()
    {
        $calculator = new PriceCalculator();
        $shipment = $this->createMock(ShipmentInterface::class);
        $shipment->method('getServiceContract')->willReturn(null);

        $this->expectException(CalculationException::class);
        $calculator->calculateGroupPrice($shipment);
    }

    /** @test */
    public function testCalculateOptionsPriceException()
    {
        $calculator = new PriceCalculator();
        $shipment = $this->createMock(ShipmentInterface::class);
        $shipment->method('getServiceContract')->willReturn(null);

        $this->expectException(CalculationException::class);
        $calculator->calculateOptionsPrice($shipment);
    }

    /** @test */
    public function testCalculateInsurancePriceException()
    {
        $calculator = new PriceCalculator();
        $shipment = $this->createMock(ShipmentInterface::class);
        $shipment->method('getServiceContract')->willReturn(null);

        $this->expectException(CalculationException::class);
        $calculator->calculateInsurancePrice($shipment);
    }
}
