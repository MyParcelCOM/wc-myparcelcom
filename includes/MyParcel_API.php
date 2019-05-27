<?php declare(strict_types=1);

use \MyParcelCom\ApiSdk\MyParcelComApi;
use \MyParcelCom\ApiSdk\Authentication\ClientCredentials;

class MyParcel_API
{
	/**
     *
     * @return MyParcelComApi
     */
 	public function apiAuthentication(): MyParcelComApi
 	{
        $apiUrl = get_option('api_url');
        $apiAuthUrl = get_option('api_auth_url');
        $clientKey = get_option('client_key');
        $clientSecretKey = get_option('client_secret_key');
        
        if(!empty($apiUrl) && !empty($apiAuthUrl) && !empty($clientKey) && !empty($clientSecretKey)) {
            $api = new MyParcelComApi($apiUrl);
            $authenticator = new ClientCredentials(
                $clientKey,
                $clientSecretKey,
                $apiAuthUrl
            );                  
            $api->authenticate($authenticator);

            return $api;
        }
    }
}

