<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Exceptions\ServiceMatchingException;
use MyParcelCom\ApiSdk\Resources\Interfaces\ServiceInterface;
use MyParcelCom\ApiSdk\Shipments\ServiceMatcher;
use MyParcelCom\ApiSdk\Tests\Traits\MocksContract;
use PHPUnit\Framework\TestCase;

class ServiceMatcherTest extends TestCase
{
    use MocksContract;


    /** @test */
    public function testGetMatchedWeightGroups()
    {
        $contracts = [
            $this->getMockedServiceContract([
                [
                    'weight_min' => 3500,
                    'weight_max' => 3600,
                    'price'      => 779,
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
                [
                    'weight_min' => 0,
                    'weight_max' => 3499,
                    'price'      => 12,
                    'step_size'  => 0,
                    'step_price' => 0,
                ],
            ]),
            $this->getMockedServiceContract([
                [
                    'weight_min' => 0,
                    'weight_max' => 20,
                    'price'      => 88,
                    'step_size'  => 100,
                    'step_price' => 10,
                ],
            ]),
            $this->getMockedServiceContract([
                [
                    'weight_min' => 0,
                    'weight_max' => 5000,
                    'price'      => 4456,
                    'step_size'  => 0,
                    'step_price' => 0,
                ],
                [
                    'weight_min' => 5001,
                    'weight_max' => 10000,
                    'price'      => 7444,
                    'step_size'  => 0,
                    'step_price' => 0,
                ],
            ]),
        ];

        $matcher = new ServiceMatcher();

        $this->assertCount(
            3,
            $matcher->getMatchedWeightGroups(
                $this->getMockedShipment(0),
                $contracts
            )
        );
        $this->assertCount(
            3,
            $matcher->getMatchedWeightGroups(
                $this->getMockedShipment(500),
                $contracts
            )
        );
        $this->assertCount(
            2,
            $matcher->getMatchedWeightGroups(
                $this->getMockedShipment(10000),
                $contracts
            )
        );
        $this->assertCount(
            1,
            $matcher->getMatchedWeightGroups(
                $this->getMockedShipment(999999),
                $contracts
            )
        );

        $this->expectException(ServiceMatchingException::class);
        $matcher->getMatchedWeightGroups(
            $this->getMockedShipment(-5010),
            $contracts
        );
    }

    /** @test */
    public function testGetMatchedInsurances()
    {
        $contracts = [
            $this->getMockedServiceContract([], [
                [
                    'covered' => 500,
                    'price'   => 80,
                ],
                [
                    'covered' => 2500,
                    'price'   => 500,
                ],
                [
                    'covered' => 15000,
                    'price'   => 789,
                ],
                [
                    'covered' => 900000,
                    'price'   => 989,
                ],
            ]),
            $this->getMockedServiceContract([], [
                [
                    'covered' => 5000,
                    'price'   => 823,
                ],
            ]),
            $this->getMockedServiceContract([], [
                [
                    'covered' => 10,
                    'price'   => 5,
                ],
                [
                    'covered' => 100,
                    'price'   => 50,
                ],
            ]),
            $this->getMockedServiceContract([], [
                [
                    'covered' => 500,
                    'price'   => 80,
                ],
                [
                    'covered' => 100,
                    'price'   => 5,
                ],
                [
                    'covered' => 1000,
                    'price'   => 99,
                ],
                [
                    'covered' => 900,
                    'price'   => 90,
                ],
            ]),
        ];

        $matcher = new ServiceMatcher();

        $this->assertCount(
            4,
            $matcher->getMatchedInsurances(
                $this->getMockedShipment(10, 0),
                $contracts
            )
        );

        $this->assertCount(
            4,
            $matcher->getMatchedInsurances(
                $this->getMockedShipment(10, 99),
                $contracts
            )
        );

        $this->assertCount(
            3,
            $matcher->getMatchedInsurances(
                $this->getMockedShipment(10, 1000),
                $contracts
            )
        );

        $this->assertCount(
            2,
            $matcher->getMatchedInsurances(
                $this->getMockedShipment(10, 1002),
                $contracts
            )
        );

        $this->assertCount(
            1,
            $matcher->getMatchedInsurances(
                $this->getMockedShipment(10, 100000),
                $contracts
            )
        );

        $this->assertCount(
            0,
            $matcher->getMatchedInsurances(
                $this->getMockedShipment(10, 898765463546987),
                $contracts
            )
        );

        $this->expectException(ServiceMatchingException::class);
        $matcher->getMatchedInsurances(
            $this->getMockedShipment(10, -4847),
            $contracts
        );
    }

    /** @test */
    public function testGetMatchedOptions()
    {
        $contracts = [
            $this->getMockedServiceContract([], [], [
                ['id' => 'option-id-1', 'price' => 1],
                ['id' => 'option-id-2', 'price' => 23],
                ['id' => 'option-id-4', 'price' => 112],
                ['id' => 'option-id-7', 'price' => 10],
            ]),
            $this->getMockedServiceContract([], [], [
                ['id' => 'option-id-1', 'price' => 124],
                ['id' => 'option-id-3', 'price' => 5],
            ]),
            $this->getMockedServiceContract([], [], [
                ['id' => 'option-id-1', 'price' => 89],
                ['id' => 'option-id-3', 'price' => 44],
                ['id' => 'option-id-4', 'price' => 546],
                ['id' => 'option-id-6', 'price' => 3],
                ['id' => 'option-id-7', 'price' => 2],
            ]),
            $this->getMockedServiceContract([], [], [
                ['id' => 'option-id-4', 'price' => 15],
                ['id' => 'option-id-8', 'price' => 2576],
            ]),
            $this->getMockedServiceContract([], [], []),
        ];

        $matcher = new ServiceMatcher();

        $this->assertCount(
            5,
            $matcher->getMatchedOptions(
                $this->getMockedShipment(10, 0, []),
                $contracts
            )
        );
        $this->assertCount(
            3,
            $matcher->getMatchedOptions(
                $this->getMockedShipment(10, 0, ['option-id-4']),
                $contracts
            )
        );
        $this->assertCount(
            2,
            $matcher->getMatchedOptions(
                $this->getMockedShipment(10, 0, ['option-id-1', 'option-id-4']),
                $contracts
            )
        );
        $this->assertCount(
            1,
            $matcher->getMatchedOptions(
                $this->getMockedShipment(10, 0, ['option-id-1', 'option-id-3', 'option-id-4', 'option-id-6']),
                $contracts
            )
        );
        $this->assertCount(
            0,
            $matcher->getMatchedOptions(
                $this->getMockedShipment(10, 0, [
                    'option-id-1',
                    'option-id-2',
                    'option-id-3',
                    'option-id-4',
                    'option-id-5',
                ]),
                $contracts
            )
        );
    }

    /** @test */
    public function testGetMatchedDeliveryMethod()
    {
        $serviceBuilder = $this->getMockBuilder(ServiceInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes();

        $pickupService = $serviceBuilder->getMock();
        $deliveyService = $serviceBuilder->getMock();

        $pickupService->method('getDeliveryMethod')
            ->willReturn(ServiceInterface::DELIVERY_METHOD_PICKUP);
        $deliveyService->method('getDeliveryMethod')
            ->willReturn(ServiceInterface::DELIVERY_METHOD_DELIVERY);

        $deliveyShipment = $this->getMockedShipment();
        $pickupShipment = $this->getMockedShipment();
        $pickupShipment->method('getPickupLocationCode')->willReturn('p1ckup');


        $matcher = new ServiceMatcher();

        $this->assertTrue(
            $matcher->matchesDeliveryMethod(
                $deliveyShipment,
                $deliveyService
            )
        );
        $this->assertFalse(
            $matcher->matchesDeliveryMethod(
                $deliveyShipment,
                $pickupService
            )
        );
        $this->assertTrue(
            $matcher->matchesDeliveryMethod(
                $pickupShipment,
                $pickupService
            )
        );
        $this->assertFalse(
            $matcher->matchesDeliveryMethod(
                $pickupShipment,
                $deliveyService
            )
        );
    }

    /** @test */
    public function testMatches()
    {

        $contracts = [
            $this->getMockedServiceContract([
                [
                    'weight_min' => 0,
                    'weight_max' => 5000,
                    'price'      => 4456,
                    'step_size'  => 0,
                    'step_price' => 0,
                ],
                [
                    'weight_min' => 5001,
                    'weight_max' => 10000,
                    'price'      => 7444,
                    'step_size'  => 0,
                    'step_price' => 0,
                ],
            ], [
                ['covered' => 500, 'price' => 80],
                ['covered' => 2500, 'price' => 500],
                ['covered' => 15000, 'price' => 789],
                ['covered' => 900000, 'price' => 989],
            ], [
                ['id' => 'option-id-1', 'price' => 1],
                ['id' => 'option-id-2', 'price' => 23],
                ['id' => 'option-id-4', 'price' => 112],
                ['id' => 'option-id-7', 'price' => 10],
            ]),
            $this->getMockedServiceContract([
                [
                    'weight_min' => 0,
                    'weight_max' => 20,
                    'price'      => 88,
                    'step_size'  => 100,
                    'step_price' => 10,
                ],
            ], [
                ['covered' => 5000, 'price' => 823],
            ], [
                ['id' => 'option-id-1', 'price' => 124],
                ['id' => 'option-id-5', 'price' => 5],
            ]),
            $this->getMockedServiceContract([
                [
                    'weight_min' => 3500,
                    'weight_max' => 3600,
                    'price'      => 779,
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
                [
                    'weight_min' => 0,
                    'weight_max' => 3499,
                    'price'      => 12,
                    'step_size'  => 0,
                    'step_price' => 0,
                ],
            ], [], [
                ['id' => 'option-id-1', 'price' => 89],
                ['id' => 'option-id-3', 'price' => 44],
                ['id' => 'option-id-4', 'price' => 546],
                ['id' => 'option-id-6', 'price' => 3],
                ['id' => 'option-id-7', 'price' => 2],
            ]),
            $this->getMockedServiceContract([
                [
                    'weight_min' => 0,
                    'weight_max' => 100,
                    'price'      => 79,
                    'step_size'  => 0,
                    'step_price' => 0,
                ],
                [
                    'weight_min' => 101,
                    'weight_max' => 1000,
                    'price'      => 799,
                    'step_size'  => 0,
                    'step_price' => 0,
                ],
                [
                    'weight_min' => 1001,
                    'weight_max' => 10000,
                    'price'      => 7999,
                    'step_size'  => 0,
                    'step_price' => 0,
                ],
            ], [
                ['covered' => 10, 'price' => 5],
                ['covered' => 100, 'price' => 50],
            ], [
                ['id' => 'option-id-4', 'price' => 15],
                ['id' => 'option-id-8', 'price' => 2576],
            ]),
            $this->getMockedServiceContract([
                [
                    'weight_min' => 0,
                    'weight_max' => 5000,
                    'price'      => 645,
                    'step_size'  => 0,
                    'step_price' => 0,
                ],
                [
                    'weight_min' => 5001,
                    'weight_max' => 9999,
                    'price'      => 895,
                    'step_size'  => 0,
                    'step_price' => 0,
                ],
            ], [
                ['covered' => 500, 'price' => 80],
                ['covered' => 100, 'price' => 5],
                ['covered' => 1000, 'price' => 99],
                ['covered' => 900, 'price' => 90],
            ], [
                ['id' => 'option-id-3', 'price' => 66],
            ]),
        ];

        $serviceBuilder = $this->getMockBuilder(ServiceInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes();

        $serviceA = $serviceBuilder->getMock();
        $serviceB = $serviceBuilder->getMock();
        $serviceC = $serviceBuilder->getMock();

        $serviceA
            ->method('getServiceContracts')
            ->willReturn([$contracts[0], $contracts[1], $contracts[2]]);
        $serviceA
            ->method('getDeliveryMethod')
            ->willReturn(ServiceInterface::DELIVERY_METHOD_DELIVERY);
        $serviceB
            ->method('getServiceContracts')
            ->willReturn([$contracts[0], $contracts[4]]);
        $serviceB
            ->method('getDeliveryMethod')
            ->willReturn(ServiceInterface::DELIVERY_METHOD_DELIVERY);
        $serviceC
            ->method('getServiceContracts')
            ->willReturn([$contracts[2], $contracts[3], $contracts[4]]);
        $serviceC
            ->method('getDeliveryMethod')
            ->willReturn(ServiceInterface::DELIVERY_METHOD_DELIVERY);

        $matcher = new ServiceMatcher();

        $this->assertTrue($matcher->matches($this->getMockedShipment(10, 0, []), $serviceA));
        $this->assertTrue($matcher->matches($this->getMockedShipment(10, 0, []), $serviceB));
        $this->assertTrue($matcher->matches($this->getMockedShipment(10, 0, []), $serviceC));

        $this->assertTrue($matcher->matches($this->getMockedShipment(10, 100, [
            'option-id-2',
            'option-id-4',
        ]), $serviceA));
        $this->assertTrue($matcher->matches($this->getMockedShipment(10, 100, [
            'option-id-2',
            'option-id-4',
        ]), $serviceB));
        $this->assertFalse($matcher->matches($this->getMockedShipment(10, 100, [
            'option-id-2',
            'option-id-4',
        ]), $serviceC));

        $this->assertTrue($matcher->matches($this->getMockedShipment(10001, 1000, ['option-id-1']), $serviceA));
        $this->assertFalse($matcher->matches($this->getMockedShipment(10001, 1000, ['option-id-1']), $serviceB));
        $this->assertFalse($matcher->matches($this->getMockedShipment(10001, 1000, ['option-id-1']), $serviceC));

        $this->assertFalse($matcher->matches($this->getMockedShipment(9000, 1000, ['option-id-3']), $serviceA));
        $this->assertTrue($matcher->matches($this->getMockedShipment(9000, 1000, ['option-id-3']), $serviceB));
        $this->assertTrue($matcher->matches($this->getMockedShipment(9000, 1000, ['option-id-3']), $serviceC));

        $this->assertFalse($matcher->matches($this->getMockedShipment(999999999, 5001, []), $serviceA));
        $this->assertFalse($matcher->matches($this->getMockedShipment(999999999, 5001, []), $serviceB));
        $this->assertFalse($matcher->matches($this->getMockedShipment(999999999, 5001, []), $serviceC));
    }
}
