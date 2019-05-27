<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Resources\Region;
use PHPUnit\Framework\TestCase;

class RegionTest extends TestCase
{
    /** @test */
    public function testId()
    {
        $region = new Region();
        $this->assertEquals('region-id', $region->setId('region-id')->getId());
    }

    /** @test */
    public function testType()
    {
        $region = new Region();
        $this->assertEquals('regions', $region->getType());
    }

    /** @test */
    public function testCountryCode()
    {
        $region = new Region();
        $this->assertEquals('NL', $region->setCountryCode('NL')->getCountryCode());
    }

    /** @test */
    public function testRegionCode()
    {
        $region = new Region();
        $this->assertEquals('ZH', $region->setRegionCode('ZH')->getRegionCode());
    }


    /** @test */
    public function testCurrency()
    {
        $region = new Region();
        $this->assertEquals('EUR', $region->setCurrency('EUR')->getCurrency());
    }

    /** @test */
    public function testName()
    {
        $region = new Region();
        $this->assertEquals('Rotterdam', $region->setName('Rotterdam')->getName());
    }


    /** @test */
    public function testJsonSerialize()
    {
        $region = (new Region())
            ->setId('region-id')
            ->setCountryCode('NL')
            ->setRegionCode('ZH')
            ->setCurrency('EUR')
            ->setName('Rotterdam');

        $this->assertEquals([
            'id'         => 'region-id',
            'type'       => 'regions',
            'attributes' => [
                'country_code' => 'NL',
                'region_code'  => 'ZH',
                'currency'     => 'EUR',
                'name'         => 'Rotterdam',
            ],
        ], $region->jsonSerialize());
    }
}
