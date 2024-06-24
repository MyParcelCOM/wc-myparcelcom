<?php

declare(strict_types=1);

function myparcelcomWebhookPermissionCallback(WP_REST_Request $request)
{
    $hash = hash_hmac('sha256', $request->get_body(), get_option(MYPARCEL_WEBHOOK_SECRET, ''));
    $signature = $request->get_header('X-MYPARCELCOM-SIGNATURE');

    return $hash === $signature;
}

function myparcelcomWebhookCallback(WP_REST_Request $request)
{
    $body = $request->get_json_params();

    $shipmentData = $body['data']['relationships']['shipment']['data'];
    $statusData = $body['data']['relationships']['status']['data'];
    $included = $body['included'];

    var_dump($shipmentData['id']);die;

    exit('ok');
}

function registerMyparcelcomRoutes()
{
    register_rest_route('myparcelcom', '/webhook', [
        'methods'             => 'POST',
        'callback'            => 'myparcelcomWebhookCallback',
        'permission_callback' => 'myparcelcomWebhookPermissionCallback',
        'show_in_index'       => false,
    ]);
}

add_action('rest_api_init', 'registerMyparcelcomRoutes');
