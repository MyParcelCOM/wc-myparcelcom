<?php

declare(strict_types=1);

use MyParcelCom\ApiSdk\Authentication\ClientCredentials;
use MyParcelCom\ApiSdk\Exceptions\AuthenticationException;
use MyParcelCom\ApiSdk\MyParcelComApi;

class MyParcelApi
{
    protected const PRODUCTION_API_URL = 'https://api.myparcel.com';
    protected const PRODUCTION_AUTH_URL = 'https://auth.myparcel.com';
    protected const SANDBOX_API_URL = 'https://sandbox-api.myparcel.com';
    protected const SANDBOX_AUTH_URL = 'https://sandbox-auth.myparcel.com';
    protected const CHECK_ACT_TEST_MODE = '1';

    public function apiAuthentication(): ?MyParcelComApi
    {
        $clientKey = get_option('client_key');
        $clientSecretKey = get_option('client_secret_key');
        $actTestMode = get_option('act_test_mode');
        if (!empty($actTestMode) && (self::CHECK_ACT_TEST_MODE === $actTestMode)) {
            $apiUrl = self::SANDBOX_API_URL;
            $apiAuthUrl = self::SANDBOX_AUTH_URL;
        } else {
            $apiUrl = self::PRODUCTION_API_URL;
            $apiAuthUrl = self::PRODUCTION_AUTH_URL;
        }
        if (!empty($apiUrl) && !empty($apiAuthUrl) && !empty($clientKey) && !empty($clientSecretKey)) {
            try {
                $api = new MyParcelComApi($apiUrl);
                $authenticator = new ClientCredentials(
                    $clientKey,
                    $clientSecretKey,
                    $apiAuthUrl
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
