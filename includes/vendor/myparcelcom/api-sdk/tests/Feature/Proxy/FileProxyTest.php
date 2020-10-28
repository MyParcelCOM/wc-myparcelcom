<?php

namespace MyParcelCom\ApiSdk\Tests\Feature\Proxy;

use Http\Client\HttpClient;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\MyParcelComApi;
use MyParcelCom\ApiSdk\MyParcelComApiInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Proxy\FileProxy;
use MyParcelCom\ApiSdk\Tests\Traits\MocksApiCommunication;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Cache\Simple\NullCache;

class FileProxyTest extends TestCase
{
    use MocksApiCommunication;

    /** @var HttpClient */
    private $client;
    /** @var AuthenticatorInterface */
    private $authenticator;
    /** @var MyParcelComApiInterface */
    private $api;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->getClientMock();
        $this->authenticator = $this->getAuthenticatorMock();
        $this->api = (new MyParcelComApi('https://api', $this->client))
            ->setCache(new NullCache())
            ->authenticate($this->authenticator);
    }

    /** @test */
    public function testAttributes()
    {
        $fileProxy = (new FileProxy())
            ->setMyParcelComApi($this->api)
            ->setId('file-id-1');

        $this->assertEquals('file-id-1', $fileProxy->getId());
        $this->assertEquals(ResourceInterface::TYPE_FILE, $fileProxy->getType());
        $this->assertEquals('label', $fileProxy->getDocumentType());

        $formats = $fileProxy->getFormats();
        $this->assertEquals([
            'extension' => 'pdf',
            'mime_type' => 'application/pdf',
        ], $formats[0]);

        $pngFormat = [
            'mime_type' => 'image/png',
            'extension' => 'png',
        ];
        $jpegFormat = [
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
        ];
        $pdfFormat = [
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
        ];

        $this->assertEquals(
            [$pngFormat, $jpegFormat],
            $fileProxy
                ->setFormats([$pngFormat, $jpegFormat])
                ->getFormats()
        );

        $this->assertEquals(
            [$pdfFormat, $pngFormat, $jpegFormat],
            $fileProxy
                ->addFormat($pdfFormat['mime_type'], $pdfFormat['extension'])
                ->getFormats()
        );

        $stream = $this->createMock(StreamInterface::class);
        $stream
            ->method('getContents')
            ->willReturn('Some stream test data');
        $this->assertEquals(
            'Some stream test data',
            $fileProxy
                ->setStream($stream, 'application/pdf')
                ->getStream('application/pdf')
                ->getContents()
        );

        $base64Data = base64_encode('Some base64 test data');
        $this->assertEquals(
            $base64Data,
            $fileProxy
                ->setBase64Data($base64Data, 'image/jpeg')
                ->getBase64Data('image/jpeg')
        );

        $createdPath = tempnam(sys_get_temp_dir(), 'myparcelcom_file') . '.pdf';
        file_put_contents($createdPath, 'Some path test data');

        $retrievedPath = $fileProxy
            ->setTemporaryFilePath($createdPath, 'image/png')
            ->getTemporaryFilePath('image/png');
        $this->assertEquals('Some path test data', file_get_contents($retrievedPath));
    }

    /** @test */
    public function testClientCalls()
    {
        // Check if the uri has been called only once
        // while requesting multiple attributes.
        $firstProxy = new FileProxy();
        $firstProxy
            ->setMyParcelComApi($this->api)
            ->setId('file-id-1');
        $firstProxy->getDocumentType();
        $firstProxy->getStream();
        $firstProxy->getFormats();

        $this->assertEquals(1, $this->clientCalls['https://api/files/file-id-1']);

        // Creating a new proxy for the same resource will
        // change the amount of client calls to 2.
        $secondProxy = new FileProxy();
        $secondProxy
            ->setMyParcelComApi($this->api)
            ->setId('file-id-1');
        $secondProxy->getDocumentType();

        $this->assertEquals(2, $this->clientCalls['https://api/files/file-id-1']);
    }

    /** @test */
    public function testJsonSerialize()
    {
        $fileProxy = new FileProxy();
        $fileProxy
            ->setMyParcelComApi($this->api)
            ->setResourceUri('https://api/files/file-id-1')
            ->setId('file-proxy-id-1');

        $this->assertEquals([
            'id'   => 'file-proxy-id-1',
            'type' => ResourceInterface::TYPE_FILE,
        ], $fileProxy->jsonSerialize());
    }
}
