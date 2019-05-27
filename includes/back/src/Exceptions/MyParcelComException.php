<?php

namespace MyParcelCom\ApiSdk\Exceptions;

use MyParcelCom\ApiSdk\Traits\HasErrors;
use RuntimeException;

/**
 * This exception is thrown whenever a request to the API fails. This can be
 * because the API is unreachable, authentication has failed, invalid resources
 * are sent or received, etc.
 *
 * In general each of these exceptions are thrown with an exception that extends
 * this exception.
 */
class MyParcelComException extends RuntimeException
{
    use HasErrors;
}
