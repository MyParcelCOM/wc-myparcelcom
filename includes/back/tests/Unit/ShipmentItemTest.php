<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Resources\ShipmentItem;
use PHPUnit\Framework\TestCase;

class ShipmentItemTest extends TestCase
{
    /** @test */
    public function testSku()
    {
        $item = new ShipmentItem();
        $this->assertEquals('CM01-W', $item->setSku('CM01-W')->getSku());
    }

    /** @test */
    public function testDescription()
    {
        $item = new ShipmentItem();
        $this->assertEquals('Tea cup', $item->setDescription('Tea cup')->getDescription());
    }

    /** @test */
    public function testHsCode()
    {
        $item = new ShipmentItem();
        $this->assertEquals('8321.21.28', $item->setHsCode('8321.21.28')->getHsCode());
    }

    /** @test */
    public function testQuantity()
    {
        $item = new ShipmentItem();
        $this->assertEquals(12, $item->setQuantity(12)->getQuantity());
    }

    /** @test */
    public function testItemValue()
    {
        $item = new ShipmentItem();
        $this->assertEquals(349, $item->setItemValue(349)->getItemValue());
    }

    /** @test */
    public function testCurrency()
    {
        $item = new ShipmentItem();
        $this->assertEquals('GBP', $item->setCurrency('GBP')->getCurrency());
    }

    /** @test */
    public function testOriginCountryCode()
    {
        $item = new ShipmentItem();
        $this->assertEquals('GB', $item->setOriginCountryCode('GB')->getOriginCountryCode());
    }

    /** @test */
    public function testJsonSerialize()
    {
        $item = (new ShipmentItem())
            ->setSku('CM01-W')
            ->setDescription('Tea cup')
            ->setHsCode('8321.21.28')
            ->setQuantity(12)
            ->setItemValue(349)
            ->setCurrency('GBP')
            ->setOriginCountryCode('GB');
        $this->assertEquals(
            [
                'sku'                 => 'CM01-W',
                'description'         => 'Tea cup',
                'hs_code'             => '8321.21.28',
                'quantity'            => 12,
                'item_value'          => [
                    'amount'   => 349,
                    'currency' => 'GBP',
                ],
                'origin_country_code' => 'GB',

            ],
            $item->jsonSerialize()
        );
    }
}
