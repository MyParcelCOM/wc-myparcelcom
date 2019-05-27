<?php

namespace MyParcelCom\ApiSdk\Http\Contracts\HttpClient;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Exception for when a request failed.
 *
 * Examples:
 *      - Request is invalid (e.g. method is missing)
 *      - Runtime request errors (e.g. the body stream is not seekable)
 */
interface RequestExceptionInterface
{
    /**
     * Returns the request.
     *
     * The request object MAY be a different object from the one passed to ClientInterface::sendRequest()
     *
     * @return RequestInterface
     */
    public function getRequest();

    /**
     * Returns the response of the failed request.
     *
     * May return null if there was no response.
     *
     * @return ResponseInterface|null
     */
    public function getResponse();
}
