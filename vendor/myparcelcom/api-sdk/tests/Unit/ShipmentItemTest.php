<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Exceptions\MyParcelComException;
use MyParcelCom\ApiSdk\Resources\Interfaces\PhysicalPropertiesInterface;
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
    public function testImageUrl()
    {
        $item = new ShipmentItem();
        $this->assertEquals('//tea.cup', $item->setImageUrl('//tea.cup')->getImageUrl());
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
    public function testItemWeight()
    {
        $item = new ShipmentItem();
        $this->assertEquals(3000, $item->setItemWeight(3000)->getItemWeight());
        $this->assertEquals(3000, $item->setItemWeight(3, PhysicalPropertiesInterface::WEIGHT_KILOGRAM)->getItemWeight());
        $this->assertEquals(3, $item->setItemWeight(3000)->getItemWeight(PhysicalPropertiesInterface::WEIGHT_KILOGRAM));
    }

    /** @test */
    public function testVatPercentage()
    {
        $item = new ShipmentItem();
        $this->assertNull($item->getVatPercentage());
        $this->assertEquals(20, $item->setVatPercentage(20)->getVatPercentage());
    }

    /** @test */
    public function testInvalidVatPercentageException()
    {
        $this->expectException(MyParcelComException::class);

        (new ShipmentItem())->setVatPercentage(101);
    }

    /** @test */
    public function testNegativeVatPercentageException()
    {
        $this->expectException(MyParcelComException::class);

        (new ShipmentItem())->setVatPercentage(-1);
    }

    /** @test */
    public function testJsonSerialize()
    {
        $item = (new ShipmentItem())
            ->setSku('CM01-W')
            ->setDescription('Tea cup')
            ->setImageUrl('//tea.cup')
            ->setHsCode('8321.21.28')
            ->setQuantity(12)
            ->setItemValue(349)
            ->setItemWeight(128)
            ->setCurrency('GBP')
            ->setVatPercentage(20)
            ->setOriginCountryCode('GB');
        $this->assertEquals(
            [
                'sku'                 => 'CM01-W',
                'description'         => 'Tea cup',
                'image_url'           => '//tea.cup',
                'hs_code'             => '8321.21.28',
                'quantity'            => 12,
                'item_value'          => [
                    'amount'   => 349,
                    'currency' => 'GBP',
                ],
                'item_weight'         => 128,
                'vat_percentage'      => 20,
                'origin_country_code' => 'GB',
            ],
            $item->jsonSerialize()
        );
    }
}
