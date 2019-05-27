<?php

namespace MyParcelCom\ApiSdk\Authentication;

use GuzzleHttp\Psr7\Request;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use MyParcelCom\ApiSdk\Exceptions\AuthenticationException;
use MyParcelCom\ApiSdk\Http\Exceptions\RequestException;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;

class ClientCredentials implements AuthenticatorInterface
{
    const CACHE_TOKEN = 'auth.token';
    const CACHE_AUTHENTICATING = 'auth.lock';
    const PATH_ACCESS_TOKEN = '/access-token';
    const TTL_MARGIN = 60;

    /** @var string */
    protected $clientSecret;

    /** @var string */
    protected $clientId;

    /** @var string */
    protected $authUri;

    /** @var CacheInterface */
    private $cache;

    /** @var HttpClient */
    private $httpClient;

    /**
     * Create an authenticator for the client_credentials grant. Requires a
     * client id and a client secret. When not connecting to the MyParcel.com
     * sandbox, the `$authUri` should be set to the correct uri. Optionally a
     * cache can be supplied to store the api-key in.
     *
     * @param string              $clientId
     * @param string              $clientSecret
     * @param string              $authUri
     * @param CacheInterface|null $cache
     * @param HttpClient|null     $httpClient
     */
    public function __construct(
        $clientId,
        $clientSecret,
        $authUri = 'https://sandbox-auth.myparcel.com',
        CacheInterface $cache = null,
        HttpClient $httpClient = null
    ) {
        $this->clientSecret = $clientSecret;
        $this->clientId = $clientId;
        $this->authUri = $authUri;
        $this->cache = $cache ?: new FilesystemCache('myparcelcom');

        if ($httpClient !== null) {
            $this->setHttpClient($httpClient);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationHeader($forceRefresh = false)
    {
        while ($this->isAuthenticating()) {
            // Wait for 200ms
            usleep(200000);
            // Don't force another authentication cycle if we're already
            // authenticating.
            $forceRefresh = false;
        }

        if ($forceRefresh) {
            return $this->authenticate();
        }

        return $this->getCachedHeader() ?: $this->authenticate();
    }

    /**
     * Returns true if another request is already authenticating.
     *
     * @return bool
     */
    protected function isAuthenticating()
    {
        return (bool)$this->cache->get(self::CACHE_AUTHENTICATING);
    }

    /**
     * @param bool $authenticating
     * @return $this
     */
    protected function setAuthenticating($authenticating = true)
    {
        // Don't let the authenticating lock stay active for more than 30s.
        $this->cache->set(self::CACHE_AUTHENTICATING, $authenticating, 30);

        return $this;
    }

    /**
     * Authenticate with the server and return the header.
     *
     * @return array
     */
    protected function authenticate()
    {
        $this->setAuthenticating();

        $body = json_encode([
            'grant_type'    => self::GRANT_CLIENT_CREDENTIALS,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope'         => self::SCOPES,
        ]);

        $request = new Request(
            'post',
            $this->authUri . self::PATH_ACCESS_TOKEN,
            [
                self::HEADER_CONTENT_TYPE => self::MIME_TYPE_JSON,
            ],
            $body
        );

        try {
            $response = $this->getHttpClient()->sendRequest($request);
            if ($response->getStatusCode() >= 400) {
                throw new RequestException($request, $response);
            }

            $data = json_decode((string)$response->getBody(), true);

            $header = [
                self::HEADER_AUTH => $data['token_type'] . ' ' . $data['access_token'],
            ];

            $this->setAuthenticating(false);
            $this->setCachedHeader($header, $data['expires_in']);

            return $header;
        } catch (RequestException $exception) {
            $this->setAuthenticating(false);
            $this->handleRequestException($exception);
        }
    }

    /**
     * Get the cached header or `null` if no header is cached (or expired).
     *
     * @return array|null
     */
    protected function getCachedHeader()
    {
        return $this->cache->get(self::CACHE_TOKEN);
    }

    /**
     * Set the cached header with a time to live.
     *
     * @param array $header
     * @param int   $ttl
     * @return $this
     */
    protected function setCachedHeader(array $header, $ttl)
    {
        $this->cache->set(self::CACHE_TOKEN, $header, $ttl - self::TTL_MARGIN);

        return $this;
    }

    /**
     * Clear the cached resources.
     *
     * @return $this
     */
    public function clearCache()
    {
        $this->cache->clear();

        return $this;
    }

    /**
     * Set the http client to use. This can be used when extra options need to
     * be set on the client.
     *
     * @param HttpClient $client
     * @return $this
     */
    public function setHttpClient(HttpClient $client)
    {
        $this->httpClient = $client;

        return $this;
    }

    /**
     * Get the http client to connect to the auth server.
     *
     * @return HttpClient
     */
    protected function getHttpClient()
    {
        if (!isset($this->httpClient)) {
            $this->httpClient = HttpClientDiscovery::find();
        }

        return $this->httpClient;
    }

    /**
     * Handle the request exception.
     *
     * @param RequestException $exception
     * @return void
     */
    protected function handleRequestException(RequestException $exception)
    {
        if (empty($exception->getResponse())) {
            throw new AuthenticationException(
                'The authentication server could not be reached, please check if the uri is correct',
                404,
                $exception
            );
        }

        $response = json_decode((string)$exception->getResponse()->getBody(), true);
        $message = 'An unknown error occurred while authenticating with the oauth2 server';

        if (!empty($response['errors'])) {
            $errors = $response['errors'];
            $error = reset($errors);
            $code = $error['code'];

            switch ($code) {
                case '14000': // AUTH_INVALID_CLIENT
                    $message = 'Client id or client secret is invalid';
                    break;
                case '14001': // AUTH_INVALID_SCOPE
                    $message = 'Invalid scope requested for client';
                    break;
                case '14002': // AUTH_INVALID_TOKEN
                    $message = 'Access token is invalid';
                    break;
            }
        }

        throw new AuthenticationException(
            $message,
            401,
            $exception
        );
    }
}
