<?php

namespace MyParcelCom\ApiSdk\Authentication;

use MyParcelCom\ApiSdk\Exceptions\AuthenticationException;

interface AuthenticatorInterface
{
    const HEADER_AUTH = 'Authorization';
    const HEADER_ACCEPT = 'Accept';
    const HEADER_CONTENT_TYPE = 'Content-Type';
    const MIME_TYPE_JSONAPI = 'application/vnd.api+json';
    const MIME_TYPE_JSON = 'application/json';
    const GRANT_CLIENT_CREDENTIALS = 'client_credentials';
    const SCOPES = '*';

    /**
     * Authenticate with the OAuth2 server and return the header to be used in
     * requests to the API. When `$forceRefresh` is set to `true`, any possibly
     * cached header is ignored and a new request is done to the auth server.
     *
     * @param bool $forceRefresh
     * @throws AuthenticationException
     * @return array
     */
    public function getAuthorizationHeader($forceRefresh = false);
}
