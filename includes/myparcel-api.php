<?php

declare(strict_types=1);

use MyParcelCom\ApiSdk\Authentication\ClientCredentials;
use MyParcelCom\ApiSdk\Exceptions\AuthenticationException;
use MyParcelCom\ApiSdk\MyParcelComApi;

class MyParcelApi
{
    protected const PRODUCTION_API_URL = 'https://api.myparcel.com';
    protected const PRODUCTION_AUTH_URL = 'https://auth.myparcel.com';
    protected const SANDBOX_API_URL = 'https://api.sandbox.myparcel.com';
    protected const SANDBOX_AUTH_URL = 'https://auth.sandbox.myparcel.com';

    public function apiAuthentication(): ?MyParcelComApi
    {
        $clientKey = get_option('client_key');
        $clientSecretKey = get_option('client_secret_key');
        $actTestMode = get_option('act_test_mode');

        return $this->doAuthentication($clientKey, $clientSecretKey, $actTestMode);
    }

    public function doAuthentication($clientId = null, $clientSecret = null, $testMode = null): ?MyParcelComApi
    {
        if (!empty($testMode) && $testMode === '1') {
            $apiUrl = self::SANDBOX_API_URL;
            $authUrl = self::SANDBOX_AUTH_URL;
        } else {
            $apiUrl = self::PRODUCTION_API_URL;
            $authUrl = self::PRODUCTION_AUTH_URL;
        }
        if (!empty($apiUrl) && !empty($authUrl) && !empty($clientId) && !empty($clientSecret)) {
            try {
                $api = new MyParcelComApi($apiUrl);
                $authenticator = new ClientCredentials(
                    $clientId,
                    $clientSecret,
                    $authUrl
                );
                $authenticator->getAuthorizationHeader(true);
                $api->authenticate($authenticator);

                return $api;
            } catch (AuthenticationException $e) {
                return null;
            }
        } else {
            return new MyParcelComApi();
        }
    }
}
