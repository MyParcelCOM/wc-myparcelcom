<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Validate webhook callback payload.
 */
function myparcelcomWebhookPermissionCallback(WP_REST_Request $request)
{
    $hash = hash_hmac('sha256', $request->get_body(), get_option(MYPARCEL_WEBHOOK_SECRET, ''));
    $signature = $request->get_header('X-MYPARCELCOM-SIGNATURE');

    return $hash === $signature;
}

/**
 * Handle webhook callback and store status information.
 */
function myparcelcomWebhookCallback(WP_REST_Request $request)
{
    $body = $request->get_json_params();

    $shipmentData = $body['data']['relationships']['shipment']['data'];
    $statusData = $body['data']['relationships']['status']['data'];
    $included = $body['included'];

    foreach ($included as $includeData) {
        if ($includeData['type'] === $shipmentData['type'] && $includeData['id'] === $shipmentData['id']) {
            $shipmentData = $includeData;
        }
        if ($includeData['type'] === $statusData['type'] && $includeData['id'] === $statusData['id']) {
            $statusData = $includeData;
        }
    }

    $order = wc_get_order((int) $shipmentData['attributes']['customer_reference'] ?? null);

    if ($order && $order->get_meta(MYPARCEL_SHIPMENT_ID) === $shipmentData['id']) {
        // Fix for a very nasty bug where the MYPARCEL_SHIPMENT_DATA gets cleaned up by the HPOS compatibility mode.
        // For some reason, encoded JSON containing a tracking_url is not properly saved into the wp_postmeta table.
        if (get_option('woocommerce_custom_orders_table_data_sync_enabled') === 'yes') {
            update_post_meta($order->get_id(), MYPARCEL_SHIPMENT_DATA, json_encode(array_filter([
                'status_code'   => $statusData['attributes']['code'],
                'status_name'   => $statusData['attributes']['name'],
                'tracking_code' => $shipmentData['attributes']['tracking_code'] ?? null,
                'tracking_url'  => $shipmentData['attributes']['tracking_url'] ?? null,
            ])));
        }

        $order->update_meta_data(MYPARCEL_SHIPMENT_DATA, json_encode(array_filter([
            'status_code'   => $statusData['attributes']['code'],
            'status_name'   => $statusData['attributes']['name'],
            'tracking_code' => $shipmentData['attributes']['tracking_code'] ?? null,
            'tracking_url'  => $shipmentData['attributes']['tracking_url'] ?? null,
        ])));
        $order->save_meta_data();
    }

    exit('ok');
}

/**
 * Register route to receive webhook callbacks.
 */
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
