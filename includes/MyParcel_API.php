<?php declare(strict_types=1);

use \MyParcelCom\ApiSdk\MyParcelComApi;
use \MyParcelCom\ApiSdk\Authentication\ClientCredentials;

class MyParcel_API
{
    const API_URL = 'https://api.myparcel.com';
    const API_AUTH_URL = 'https://auth.myparcel.com';
    const API_SANDBOX_URL = 'https://sandbox-api.myparcel.com';
    const API_SANDBOX_AUTH_URL = 'https://sandbox-auth.myparcel.com';
	/**
     *
     * @return MyParcelComApi
     */
 	public function apiAuthentication(): MyParcelComApi
 	{
        $clientKey          = get_option('client_key');
        $clientSecretKey    = get_option('client_secret_key');        
        $actTestMode = get_option('act_test_mode'); 
        if (!empty($actTestMode) && (1 == $actTestMode)) {
            // $apiUrl         = "https://sandbox-api.myparcel.com"; // Sandbox api URL
            // $apiAuthUrl     = "https://sandbox-auth.myparcel.com"; // Sandbox api Auth URL
            $apiUrl         = self::API_SANDBOX_URL;
            $apiAuthUrl     = self::API_SANDBOX_AUTH_URL;
        }else{
            // $apiUrl         = "https://api.myparcel.com"; // Production api URL 
            // $apiAuthUrl     = "https://auth.myparcel.com";// Production api Auth URL
            $apiUrl         = self::API_URL; // Production api URL 
            $apiAuthUrl     = self::API_AUTH_URL; //Production api Auth URL
        }
        if(!empty($apiUrl) && !empty($apiAuthUrl) && !empty($clientKey) && !empty($clientSecretKey)) {
            $api = new MyParcelComApi($apiUrl);
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

