<?php

namespace MyParcelCom\ApiSdk\Tests\Feature\Proxy;

use Http\Client\HttpClient;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\MyParcelComApi;
use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Proxy\FileStreamProxy;
use MyParcelCom\ApiSdk\Tests\Traits\MocksApiCommunication;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Cache\Simple\NullCache;

class FileStreamProxyTest extends TestCase
{
    use MocksApiCommunication;

    /** @var HttpClient */
    private $client;
    /** @var AuthenticatorInterface */
    private $authenticator;
    /** @var MyParcelComApiInterface */
    private $api;
    /** @var FileStreamProxy */
    private $fileStreamProxy;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->getClientMock();
        $this->authenticator = $this->getAuthenticatorMock();
        $this->api = (new MyParcelComApi('https://api', $this->client))
            ->setCache(new NullCache())
            ->authenticate($this->authenticator);

        $this->fileStreamProxy = new FileStreamProxy(
            'file-stream-id-1',
            'application/pdf',
            $this->api
        );
    }

    /** @test */
    public function testToString()
    {
        $this->assertEquals('This is some stream data.', $this->fileStreamProxy->__toString());
    }

    /** @test */
    public function testClose()
    {
        $this->assertNotEmpty($this->fileStreamProxy->__toString());
        $this->fileStreamProxy->close();
        $this->assertEmpty($this->fileStreamProxy->__toString());
    }

    /** @test */
    public function testDetach()
    {
        $this->assertNotEmpty($this->fileStreamProxy->getSize());
        $this->fileStreamProxy->detach();
        $this->assertEmpty($this->fileStreamProxy->getSize());
    }

    /** @test */
    public function testGetSize()
    {
        $this->assertEquals(25, $this->fileStreamProxy->getSize());
    }

    /** @test */
    public function testPositionFunctions()
    {
        $this->fileStreamProxy->rewind();
        $this->fileStreamProxy->seek(9);
        $this->assertEquals(9, $this->fileStreamProxy->tell());

        $this->fileStreamProxy->rewind();
        $this->assertEquals(0, $this->fileStreamProxy->tell());

        $this->assertFalse($this->fileStreamProxy->eof());
        $this->fileStreamProxy->seek(0, SEEK_END);
        $this->fileStreamProxy->read(100);
        $this->assertTrue($this->fileStreamProxy->eof());
    }

    /** @test */
    public function testIsSeekable()
    {
        $this->fileStreamProxy->rewind();
        $this->assertTrue($this->fileStreamProxy->isSeekable());
        $this->fileStreamProxy->seek(5);
        $this->assertEquals(5, $this->fileStreamProxy->tell());

        $this->fileStreamProxy->close();
        $this->assertFalse($this->fileStreamProxy->isSeekable());
        $this->expectException(RuntimeException::class);
        $this->fileStreamProxy->seek(7);
    }

    /** @test */
    public function testIsWritable()
    {
        $this->fileStreamProxy->rewind();
        $this->assertTrue($this->fileStreamProxy->isWritable());
        $this->fileStreamProxy->write('This is new stream data written to the stream.');
        $this->assertEquals(
            'This is new stream data written to the stream.',
            $this->fileStreamProxy->__toString()
        );

        $this->fileStreamProxy->close();
        $this->assertFalse($this->fileStreamProxy->isWritable());
        $this->expectException(RuntimeException::class);
        $this->fileStreamProxy->write('This will trigger an exception.');
    }

    /** @test */
    public function testIsReadable()
    {
        $this->fileStreamProxy->rewind();
        $this->assertTrue($this->fileStreamProxy->isReadable());
        $this->assertEquals('This is', $this->fileStreamProxy->read(7));

        $this->fileStreamProxy->close();
        $this->assertFalse($this->fileStreamProxy->isReadable());
        $this->expectException(RuntimeException::class);
        $this->fileStreamProxy->read(4);
    }

    /** @test */
    public function testGetContents()
    {
        $this->fileStreamProxy->rewind();
        $this->assertEquals('This is some stream data.', $this->fileStreamProxy->getContents());
    }

    /** @test */
    public function testMetaDeta()
    {
        $this->fileStreamProxy->rewind();
        $this->assertNotEmpty($this->fileStreamProxy->getMetadata('uri'));
    }

    /** @test */
    public function testClientCalls()
    {
        // Check if the uri has been called only once
        // while requesting multiple attributes.
        $firstProxy = new FileStreamProxy('file-stream-id-1', 'application/pdf', $this->api);
        $firstProxy->getContents();
        $firstProxy->getSize();
        $firstProxy->isWritable();

        $this->assertEquals(1, $this->clientCalls['https://api/files/file-stream-id-1']);

        // Creating a new proxy for the same resource will
        // change the amount of client calls to 2.
        $secondProxy = new FileStreamProxy('file-stream-id-1', 'application/pdf', $this->api);
        $secondProxy->close();

        $this->assertEquals(2, $this->clientCalls['https://api/files/file-stream-id-1']);
    }
}
