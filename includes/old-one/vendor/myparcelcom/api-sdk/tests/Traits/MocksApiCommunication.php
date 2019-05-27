<?php

namespace MyParcelCom\ApiSdk\Tests\Traits;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use MyParcelCom\ApiSdk\Authentication\AuthenticatorInterface;
use function GuzzleHttp\Promise\promise_for;

trait MocksApiCommunication
{
    protected $clientCalls = [];

    protected function getClientMock()
    {
        $client = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $client
            ->method('requestAsync')
            ->willReturnCallback(function ($method, $uri, $options) {
                $filePath = implode(DIRECTORY_SEPARATOR, [
                        dirname(dirname(__FILE__)),
                        'Stubs',
                        $method,
                        str_replace([':', '{', '}', '(', ')', '/', '\\', '@', '?', '[', ']', '=', '&'], '-', $uri),
                    ]) . '.json';

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

                if ($method === 'post') {
                    // Any post will have the data from the stub added to the
                    // original request. This simulates the api creating the
                    // resource and returning it with added attributes.
                    $returnJson = \GuzzleHttp\json_encode(
                        array_merge_recursive(
                            \GuzzleHttp\json_decode($returnJson, true),
                            // You may wonder why we would encode and then
                            // decode this, but it is possible that the json in
                            // the options array is not an associative array,
                            // which we need to be able to merge.
                            \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($options['json']), true)
                        )
                    );
                }
                if ($method === 'patch') {
                    // Any patch will have the data from the stub merged with the
                    // original request data. This simulates the api updating the
                    // resource and returning it with merged attributes.
                    $returnJson = \GuzzleHttp\json_encode(
                        array_replace_recursive(
                            \GuzzleHttp\json_decode($returnJson, true),
                            \GuzzleHttp\json_decode(\GuzzleHttp\json_encode($options['json']), true)
                        )
                    );
                }

                if (strpos($returnJson, '"errors": [') !== false) {
                    return new RejectedPromise(new RequestException(
                        'This carrier does not have any pickup and dropoff locations.',
                        new Request($method, $uri),
                        new Response(500, [], $returnJson)
                    ));
                }

                $response = new Response(200, [], $returnJson);

                if (!isset($this->clientCalls[$uri])) {
                    $this->clientCalls[$uri] = 0;
                }
                $this->clientCalls[$uri]++;

                return promise_for($response);
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
