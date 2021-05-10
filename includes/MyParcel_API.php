<?php declare(strict_types=1);

use \MyParcelCom\ApiSdk\MyParcelComApi;
use \MyParcelCom\ApiSdk\Authentication\ClientCredentials;

class MyParcel_API
{
    protected const API_URL              = 'https://api.myparcel.com';
    protected const API_AUTH_URL         = 'https://auth.myparcel.com';
    protected const API_SANDBOX_URL      = 'https://sandbox-api.myparcel.com';
    protected const API_SANDBOX_AUTH_URL = 'https://sandbox-auth.myparcel.com';
    protected const CHECK_ACT_TEST_MODE  = '1';

    /**
     * @return MyParcelComApi
     */
    public function apiAuthentication(): MyParcelComApi
    {
        $clientKey       = get_option('client_key');
        $clientSecretKey = get_option('client_secret_key');
        $actTestMode     = get_option('act_test_mode');
        if (!empty($actTestMode) && (self::CHECK_ACT_TEST_MODE === $actTestMode)) {
            $apiUrl     = self::API_SANDBOX_URL;
            $apiAuthUrl = self::API_SANDBOX_AUTH_URL;
        } else {
            $apiUrl     = self::API_URL; // Production API URL
            $apiAuthUrl = self::API_AUTH_URL; //Production API Auth URL
        }
        if (!empty($apiUrl) && !empty($apiAuthUrl) && !empty($clientKey) && !empty($clientSecretKey)) {
            $api           = new MyParcelComApi($apiUrl);
            $authenticator = new ClientCredentials(
                $clientKey,
                $clientSecretKey,
                $apiAuthUrl
            );
            $authenticator->getAuthorizationHeader(true);
            $api->authenticate($authenticator);

            return $api;
        }
    }
}
