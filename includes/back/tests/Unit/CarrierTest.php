<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Resources\Carrier;
use PHPUnit\Framework\TestCase;

class CarrierTest extends TestCase
{
    /** @test */
    public function testId()
    {
        $carrier = new Carrier();

        $this->assertNull($carrier->getId());

        $this->assertEquals('carrier-id', $carrier->setId('carrier-id')->getId());
    }

    /** @test */
    public function testName()
    {
        $carrier = new Carrier();

        $this->assertNull($carrier->getName());

        $this->assertEquals('MyParcel.com Carrier', $carrier->setName('MyParcel.com Carrier')->getName());
    }

    /** @test */
    public function testGetType()
    {
        $carrier = new Carrier();

        $this->assertEquals('carriers', $carrier->getType());
    }

    /** @test */
    public function testCode()
    {
        $carrier = new Carrier();

        $this->assertNull($carrier->getCode());

        $this->assertEquals('some-code', $carrier->setCode('some-code')->getCode());
    }

    /** @test */
    public function testCredentialsFormat()
    {
        $carrier = new Carrier();

        $this->assertEquals([], $carrier->getCredentialsFormat());

        $carrier->setCredentialsFormat([
            "additionalProperties" => false,
            "required"             => [
                "api_secret",
            ],
            "properties"           => [
                "api_secret" => [
                    "type" => "string",
                ],
            ],
        ]);

        $this->assertEquals([
            "additionalProperties" => false,
            "required"             => [
                "api_secret",
            ],
            "properties"           => [
                "api_secret" => [
                    "type" => "string",
                ],
            ],
        ], $carrier->getCredentialsFormat());
    }

    /** @test */
    public function testJsonSerialize()
    {
        $carrier = (new Carrier())
            ->setId('carrier-id')
            ->setName('MyParcel.com Carrier')
            ->setCode('carrier-code')
            ->setCredentialsFormat([
                "additionalProperties" => false,
                "required"             => [
                    "api_user",
                    "api_password",
                ],
                "properties"           => [
                    "api_user"     => [
                        "type" => "string",
                    ],
                    "api_password" => [
                        "type" => "string",
                    ],
                ],
            ]);

        $this->assertEquals([
            'id'         => 'carrier-id',
            'type'       => 'carriers',
            'attributes' => [
                'name'               => 'MyParcel.com Carrier',
                'code'               => 'carrier-code',
                'credentials_format' => [
                    "additionalProperties" => false,
                    "required"             => [
                        "api_user",
                        "api_password",
                    ],
                    "properties"           => [
                        "api_user"     => [
                            "type" => "string",
                        ],
                        "api_password" => [
                            "type" => "string",
                        ],
                    ],
                ],
            ],
        ], $carrier->jsonSerialize());
    }
}
