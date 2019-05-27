<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('vendor/autoload.php');


// $api = new \MyParcelCom\ApiSdk\MyParcelComApi(
//     'https://sandbox-api.myparcel.com'
// );

// $authenticator = new \MyParcelCom\ApiSdk\Authentication\ClientCredentials(
//     '0cd3c3b5-4f3e-4093-9201-601a0d70eb1c',
//     'KfY9mXK7jxPsT62rgC1uVf79uWOmPnW3LSNEfzIiV9H2HOBhSy95AcMuXLkDNh2O',
//     'https://sandbox-auth.myparcel.com'
// );

// $api->authenticate($authenticator);
// echo "<pre>";
// print_r($api);
// die;


// Create the singleton once, to make it available everywhere.
$api = \MyParcelCom\ApiSdk\MyParcelComApi::createSingleton(
    new \MyParcelCom\ApiSdk\Authentication\ClientCredentials(
        '0cd3c3b5-4f3e-4093-9201-601a0d70eb1c',
        'KfY9mXK7jxPsT62rgC1uVf79uWOmPnW3LSNEfzIiV9H2HOBhSy95AcMuXLkDNh2O',
        'https://sandbox-auth.myparcel.com'
    ),
    'https://sandbox-api.myparcel.com'
);



