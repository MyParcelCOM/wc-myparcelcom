<?php

namespace MyParcelCom\ApiSdk\Http\Exceptions;

use Exception;
use MyParcelCom\ApiSdk\Http\Contracts\HttpClient\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class RequestException extends Exception implements RequestExceptionInterface
{
    /** @var RequestInterface */
    private $request;

    /** @var ResponseInterface */
    private $response;

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param Throwable|null    $previous
     */
    public function __construct(RequestInterface $request, ResponseInterface $response, Throwable $previous = null)
    {
        $this->request = $request;
        $this->response = $response;

        parent::__construct($response->getReasonPhrase(), $response->getStatusCode(), $previous);
    }

    /**
     * Returns the request.
     *
     * The request object MAY be a different object from the one passed to ClientInterface::sendRequest()
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the response of the failed request.
     *
     * May return null if there was no response.
     *
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}
