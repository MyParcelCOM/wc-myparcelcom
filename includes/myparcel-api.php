<?php

declare(strict_types=1);

use MyParcelCom\ApiSdk\Authentication\ClientCredentials;
use MyParcelCom\ApiSdk\MyParcelComApi;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class MyParcelApi extends MyParcelComApi
{
    protected const PRODUCTION_API_URL = 'https://api.myparcel.com';
    protected const PRODUCTION_AUTH_URL = 'https://auth.myparcel.com';
    protected const SANDBOX_API_URL = 'https://api.sandbox.myparcel.com';
    protected const SANDBOX_AUTH_URL = 'https://auth.sandbox.myparcel.com';

    public static function createSingletonFromConfig(array $config = null): MyParcelComApi
    {
        $testMode = $config ? $config['act_test_mode'] : get_option('act_test_mode');
        $apiUrl = $testMode ? self::SANDBOX_API_URL : self::PRODUCTION_API_URL;
        $authUrl = $testMode ? self::SANDBOX_AUTH_URL : self::PRODUCTION_AUTH_URL;

        $authenticator = new ClientCredentials(
            $config ? $config['client_key'] : (string) get_option('client_key'),
            $config ? $config['client_secret_key'] : (string) get_option('client_secret_key'),
            $authUrl,
        );

        // force token refresh (to get a token with new ACL scopes)
        $authenticator->clearCache();

        return MyParcelComApi::createSingleton($authenticator, $apiUrl);
    }
}
