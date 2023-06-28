<?php

namespace MyParcelCom\ApiSdk\Tests\Traits;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Http\Client\HttpClient;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use MyParcelCom\ApiSdk\Http\Exceptions\RequestException;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Cache\Simple\NullCache;

trait MocksApiCommunication
{
    protected $clientCalls = [];

    protected function getNullCache()
    {
        // Symfony 5.0.0 removed their PSR-16 cache classes. Their PSR-6 cache classes can be wrapped in Psr16Cache.
        if (class_exists('\Symfony\Component\Cache\Psr16Cache')) {
            $psr6Cache = new NullAdapter();
            return new Psr16Cache($psr6Cache);
        } else {
            return new NullCache();
        }
    }

    protected function getClientMock()
    {
        $client = $this->getMockBuilder(HttpClient::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $client
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) {
                $method = strtolower($request->getMethod());
                $uri = urldecode((string) $request->getUri());

                $filePath = implode(DIRECTORY_SEPARATOR, [
                        dirname(dirname(__FILE__)),
                        'Stubs',
                        $method,
                        str_replace(
                            ['?', '&'],
                            DIRECTORY_SEPARATOR,
                            str_replace(
                                [':', '{', '}', '(', ')', '/', '\\', '@', '[', ']', '='],
                                '-',
                                $uri
                            )
                        ),
                    ]) . '.json';

                $filePath = urldecode($filePath);

                if (strpos($filePath, 'stream') !== false) {
                    $filePath = preg_replace('/.json/', '.txt', $filePath);
                }

                if (!file_exists($filePath)) {
                    throw new \RuntimeException(sprintf(
                        'File with path `%s` does not exist, please create this file with valid response data',
                        $filePath
                    ));
                }

                $returnJson = file_get_contents($filePath);

                if (strpos($returnJson, '"errors": [') !== false && strpos($returnJson, '"data": ') === false) {
                    throw new RequestException(
                        new Request($method, $uri),
                        new Response(500, [], $returnJson)
                    );
                }

                $response = new Response(200, [], $returnJson);

                if (!isset($this->clientCalls[$uri])) {
                    $this->clientCalls[$uri] = 0;
                }
                $this->clientCalls[$uri]++;

                return $response;
            });

        return $client;
    }

    protected function getAuthenticatorMock()
    {
        $authenticator = $this->getMockBuilder(AuthenticatorInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();

        $authenticator->method('getAuthorizationHeader')
            ->willReturn(['Authorization' => 'Bearer test-api-token']);

        return $authenticator;
    }
}
