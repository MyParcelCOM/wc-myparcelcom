<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceContractInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\ServiceOptionPrice;
use PHPUnit\Framework\TestCase;

class ServiceOptionPriceTest extends TestCase
{
    /** @test */
    public function testId()
    {
        $optionPrice = new ServiceOptionPrice();
        $this->assertEquals('service-option-id', $optionPrice->setId('service-option-id')->getId());
    }

    /** @test */
    public function testType()
    {
        $optionPrice = new ServiceOptionPrice();
        $this->assertEquals('service-option-prices', $optionPrice->getType());
    }

    /** @test */
    public function testPrice()
    {
        $optionPrice = new ServiceOptionPrice();
        $this->assertEquals(55, $optionPrice->setPrice(55)->getPrice());
    }

    /** @test */
    public function testCurrency()
    {
        $optionPrice = new ServiceOptionPrice();
        $this->assertEquals('NOK', $optionPrice->setCurrency('NOK')->getCurrency());
    }

    /** @test */
    public function testRequired()
    {
        $optionPrice = new ServiceOptionPrice();
        $this->assertTrue($optionPrice->setRequired(true)->isRequired());
        $this->assertFalse($optionPrice->setRequired(false)->isRequired());
    }

    /** @test */
    public function testServiceContract()
    {
        $optionPrice = new ServiceOptionPrice();

        $mock = $this->getMockClass(ServiceContractInterface::class);
        $serviceContract = new $mock();

        $this->assertEquals($serviceContract, $optionPrice->setServiceContract($serviceContract)->getServiceContract());
    }

    /** @test */
    public function testServiceOption()
    {
        $optionPrice = new ServiceOptionPrice();

        $mock = $this->getMockClass(ServiceOptionInterface::class);
        $option = new $mock();

        $this->assertEquals($option, $optionPrice->setServiceOption($option)->getServiceOption());
    }

    /** @test */
    public function testJsonSerialize()
    {
        $serviceOptionMock = $this->getMockBuilder(ServiceOptionInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $serviceOptionMock->method('jsonSerialize')
            ->willReturn([
                'type' => 'service-options',
                'id'   => 'service-option-id',
            ]);

        $serviceContractMock = $this->getMockBuilder(ServiceContractInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $serviceContractMock->method('jsonSerialize')
            ->willReturn([
                'type' => 'service-contracts',
                'id'   => 'service-contract-id',
            ]);

        $optionPrice = (new ServiceOptionPrice())
            ->setId('service-option-id')
            ->setPrice(55)
            ->setCurrency('NOK')
            ->setServiceContract($serviceContractMock)
            ->setServiceOption($serviceOptionMock);

        $this->assertEquals([
            'id'            => 'service-option-id',
            'type'          => 'service-option-prices',
            'attributes'    => [
                'price' => [
                    'amount'   => 55,
                    'currency' => 'NOK',
                ],
            ],
            'relationships' => [
                'service_option'   => [
                    'data' => [
                        'type' => 'service-options',
                        'id'   => 'service-option-id',
                    ],
                ],
                'service_contract' => [
                    'data' => [
                        'type' => 'service-contracts',
                        'id'   => 'service-contract-id',
                    ],
                ],
            ],
        ], $optionPrice->jsonSerialize());
    }
}
