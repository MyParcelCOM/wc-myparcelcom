<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Resources\Contract;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceOptionInterface;
use MyParcelCom\ApiSdk\Resources\Service;
use MyParcelCom\ApiSdk\Resources\ServiceRate;
use PHPUnit\Framework\TestCase;

class ServiceRateTest extends TestCase
{
    /**
     * @test
     * @dataProvider attributeDataProvider
     * @param callable $setter
     * @param mixed    $value
     * @param callable $getter
     */
    public function testItSetsAndGetsAttributes($setter, $value, $getter)
    {
        $serviceRate = new ServiceRate();

        call_user_func_array([$serviceRate, $setter], [$value]);

        $this->assertEquals($value, call_user_func([$serviceRate, $getter]));
    }

    /** @test */
    public function testItSetsAndGetsServiceRelationship()
    {
        $serviceRate = new ServiceRate();
        $serviceMock = $this->createMock(Service::class);

        $this->assertEquals($serviceMock, $serviceRate->setService($serviceMock)->getService());
    }

    /** @test */
    public function testItSetsAndGetsContractRelationship()
    {
        $serviceRate = new ServiceRate();
        $contractMock = $this->createMock(Contract::class);

        $this->assertEquals($contractMock, $serviceRate->setContract($contractMock)->getContract());
    }

    /** @test */
    public function testItSetsAddsAndGetsServiceOptionRelationships()
    {
        $serviceRate = new ServiceRate();

        $mock = $this->getMockClass(ServiceOptionInterface::class);
        $serviceOptions = [new $mock(), new $mock(), new $mock()];

        $this->assertEquals($serviceOptions, $serviceRate->setServiceOptions($serviceOptions)->getServiceOptions());

        $serviceOption = new $mock();
        $serviceOptions[] = $serviceOption;
        $this->assertEquals($serviceOptions, $serviceRate->addServiceOption($serviceOption)->getServiceOptions());
    }

    /**
     * @return array
     */
    public function attributeDataProvider()
    {
        return [
            'id'         => ['setId', 'service-rate-id', 'getId'],
            'type'       => ['setType', 'service-rates', 'getType'],
            'weight_min' => ['setWeightMin', 123, 'getWeightMin'],
            'weight_max' => ['setWeightMax', 456, 'getWeightMax'],
            'length_max' => ['setLengthMax', 789, 'getLengthMax'],
            'height_max' => ['setHeightMax', 987, 'getHeightMax'],
            'width_max'  => ['setWidthMax', 654, 'getWidthMax'],
            'volume_max' => ['setVolumeMax', 321, 'getVolumeMax'],
            'currency'   => ['setCurrency', 'GBP', 'getCurrency'],
            'price'      => ['setPrice', 500, 'getPrice'],
            'step_price' => ['setStepPrice', 880, 'getStepPrice'],
            'step_size'  => ['setStepSize', 1234, 'getStepSize'],
        ];
    }

    /** @test */
    public function testJsonSerialize()
    {
        $serviceMock = $this->createMock(Service::class);
        $serviceMock->method('jsonSerialize')->willReturn([
            'id'   => 'service-id',
            'type' => 'services',
        ]);

        $contractMock = $this->createMock(Contract::class);
        $contractMock->method('jsonSerialize')->willReturn([
            'id'   => 'contract-id',
            'type' => 'contracts',
        ]);

        $serviceOptionMockBuilder = $this->getMockBuilder(ServiceOptionInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes();

        $serviceOptionMockA = $serviceOptionMockBuilder->getMock();
        $serviceOptionMockA->method('jsonSerialize')
            ->willReturn([
                'type' => 'service-options',
                'id'   => 'service-option-id-1',
                'meta' => [
                    'price'    => [
                        'amount'   => 200,
                        'currency' => 'EUR',
                    ],
                    'included' => false,
                ],
            ]);

        $serviceOptionMockB = $serviceOptionMockBuilder->getMock();
        $serviceOptionMockB->method('jsonSerialize')
            ->willReturn([
                'type' => 'service-options',
                'id'   => 'service-option-id-2',
                'meta' => [
                    'price'    => [
                        'amount'   => 300,
                        'currency' => 'EUR',
                    ],
                    'included' => false,
                ],
            ]);

        $serviceOptionMockC = $serviceOptionMockBuilder->getMock();
        $serviceOptionMockC->method('jsonSerialize')
            ->willReturn([
                'type' => 'service-options',
                'id'   => 'service-option-id-3',
                'meta' => [
                    'price'    => [
                        'amount'   => 400,
                        'currency' => 'EUR',
                    ],
                    'included' => false,
                ],
            ]);

        $serviceRate = (new ServiceRate())
            ->setId('service-rate-id')
            ->setWeightMin(123)
            ->setWeightMax(456)
            ->setLengthMax(789)
            ->setWidthMax(987)
            ->setHeightMax(654)
            ->setVolumeMax(321)
            ->setCurrency('GBP')
            ->setPrice(741)
            ->setStepSize(10)
            ->setStepPrice(10)
            ->setService($serviceMock)
            ->setContract($contractMock)
            ->setServiceOptions([
                $serviceOptionMockA,
                $serviceOptionMockB,
                $serviceOptionMockC,
            ]);

        $this->assertEquals([
            'id'            => 'service-rate-id',
            'type'          => 'service-rates',
            'attributes'    => [
                'price'      => [
                    'amount'   => 741,
                    'currency' => 'GBP',
                ],
                'step_price' => [
                    'amount'   => 10,
                    'currency' => 'GBP',
                ],
                'step_size'  => 10,
                'weight_min' => 123,
                'weight_max' => 456,
                'length_max' => 789,
                'width_max'  => 987,
                'height_max' => 654,
                'volume_max' => 321,
            ],
            'relationships' => [
                'service'         => [
                    'data' => [
                        'id'   => 'service-id',
                        'type' => 'services',
                    ],
                ],
                'contract'        => [
                    'data' => [
                        'id'   => 'contract-id',
                        'type' => 'contracts',
                    ],
                ],
                'service_options' => [
                    'data' => [
                        [
                            'id'   => 'service-option-id-1',
                            'type' => 'service-options',
                            'meta' => [
                                'price'    => [
                                    'amount'   => 200,
                                    'currency' => 'EUR',
                                ],
                                'included' => false,
                            ],
                        ],
                        [
                            'id'   => 'service-option-id-2',
                            'type' => 'service-options',
                            'meta' => [
                                'price'    => [
                                    'amount'   => 300,
                                    'currency' => 'EUR',
                                ],
                                'included' => false,
                            ],
                        ],
                        [
                            'id'   => 'service-option-id-3',
                            'type' => 'service-options',
                            'meta' => [
                                'price'    => [
                                    'amount'   => 400,
                                    'currency' => 'EUR',
                                ],
                                'included' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ], $serviceRate->jsonSerialize());
    }
}
