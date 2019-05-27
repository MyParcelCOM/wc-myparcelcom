<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Resources\ServiceInsurance;
use PHPUnit\Framework\TestCase;

class ServiceInsuranceTest extends TestCase
{
    /** @test */
    public function testId()
    {
        $insurance = new ServiceInsurance();
        $this->assertEquals('service-insurance-id', $insurance->setId('service-insurance-id')->getId());
    }

    /** @test */
    public function testGetType()
    {
        $insurance = new ServiceInsurance();
        $this->assertEquals('service-insurances', $insurance->getType());
    }

    /** @test */
    public function testCovered()
    {
        $insurance = new ServiceInsurance();
        $this->assertEquals(10000, $insurance->setCovered(10000)->getCovered());
    }

    /** @test */
    public function testPrice()
    {
        $insurance = new ServiceInsurance();
        $this->assertEquals(500, $insurance->setPrice(500)->getPrice());
    }

    /** @test */
    public function testCurrency()
    {
        $insurance = new ServiceInsurance();
        $this->assertEquals('EUR', $insurance->setCurrency('EUR')->getCurrency());
    }

    /** @test */
    public function testJsonSerialize()
    {
        $insurance = (new ServiceInsurance())
            ->setId('service-insurance-id')
            ->setCovered(10000)
            ->setPrice(500)
            ->setCurrency('EUR');

        $this->assertEquals([
            'id'         => 'service-insurance-id',
            'type'       => 'service-insurances',
            'attributes' => [
                'covered' => [
                    'amount'   => 10000,
                    'currency' => 'EUR',
                ],
                'price'   => [
                    'amount'   => 500,
                    'currency' => 'EUR',
                ],
            ],
        ], $insurance->jsonSerialize());
    }
}
