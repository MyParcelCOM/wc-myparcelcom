<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use MyParcelCom\ApiSdk\Http\Exceptions\RequestException;
use MyParcelCom\ApiSdk\Resources\Address;
use MyParcelCom\ApiSdk\Resources\Customs;
use MyParcelCom\ApiSdk\Resources\Interfaces\ShipmentInterface;
use MyParcelCom\ApiSdk\Resources\PhysicalProperties;
use MyParcelCom\ApiSdk\Resources\Shipment;

/**
 * Register our scripts and make sure they are only injected when viewing the orders overview.
 */
function ordersOverviewJsCss()
{
    // The WooCommerce order overview is called "edit-shop_order" while their order detail page is called "shop_order".
    if (in_array(get_current_screen()->id, ['edit-shop_order', 'woocommerce_page_wc-orders'])) {
        $assetsPath = plugins_url('', __FILE__) . '/../assets';
        wp_enqueue_style('myparcelcom-orders', $assetsPath . '/admin/css/admin-orders.css?v=3.1.0');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('myparcelcom-orders', $assetsPath . '/admin/js/admin-orders.js?v=3.1.0', ['jquery']);
    }
}

add_action('admin_enqueue_scripts', 'ordersOverviewJsCss', 999);

/**
 * Insert our column in the order overview table after the "order_status" column.
 */
function customShopOrderColumn(array $columns): array
{
    $customShopOrderColumn = [];
    foreach ($columns as $key => $value) {
        $customShopOrderColumn[$key] = $value;
        if ($key === 'order_status') {
            $customShopOrderColumn['myparcelcom_shipment_status'] = __('MyParcel.com status', 'get_shipment_status');
        }
    }

    return $customShopOrderColumn;
}

add_filter('manage_edit-shop_order_columns', 'customShopOrderColumn', 11);
add_filter('manage_woocommerce_page_wc-orders_columns', 'customShopOrderColumn', 11); // HPOS

/**
 * This function is called for every cell of every column that is rendered in the "shop_order" table.
 */
function customOrdersListColumnContent(string $column, WC_Order|int $orderId): void
{
    switch ($column) {
        case 'myparcelcom_shipment_status':
            $order = is_int($orderId) ? wc_get_order($orderId) : $orderId;

            $shipmentData = $order->get_meta(MYPARCEL_SHIPMENT_DATA);

            // If no shipment data is found, we check the legacy meta, which is used by our v2.x plugin.
            if (empty($shipmentData)) {
                $legacyMeta = $order->get_meta(MYPARCEL_LEGACY_SHIPMENT_META);
                if (!empty($legacyMeta)) {
                    $legacyData = json_decode($legacyMeta, true);
                    $shipmentId = $legacyData[MYPARCEL_LEGACY_SHIPMENT_ID];

                    // Our v2.x plugin fired a request for every shipment, but our v3.x plugin uses webhook data.
                    // To support shipments which have been made prior to the webhooks, we request them one more time.
                    try {
                        $api = MyParcelApi::createSingletonFromConfig();
                        $shipment = $api->getShipment($shipmentId);

                        $order->update_meta_data(MYPARCEL_SHIPMENT_ID, $shipment->getId());
                        $order->update_meta_data(MYPARCEL_SHIPMENT_DATA, json_encode([
                            'status_code'   => $shipment->getShipmentStatus()->getStatus()->getCode(),
                            'status_name'   => $shipment->getShipmentStatus()->getStatus()->getName(),
                            'tracking_code' => $shipment->getTrackingCode(),
                            'tracking_url'  => $shipment->getTrackingUrl(),
                        ]));
                        $order->save_meta_data();
                    } catch (Exception) {}
                }
            }

            if (!empty($shipmentData)) {
                $shipmentValues = json_decode($shipmentData, true);
                echo '<div class="order-status status-completed" title="' . $shipmentValues['status_code'] . '">';
                echo '<span>' . $shipmentValues['status_name'] . '</span>';
                echo '</div>';

                if (isset($shipmentValues['tracking_code'])) {
                    echo '<a href="' . $shipmentValues['tracking_url'] . '" style="margin-left:1em" target="_blank">';
                    echo $shipmentValues['tracking_code'];
                    echo '</a>';
                }
            }
            break;
    }
}

add_action('manage_shop_order_posts_custom_column', 'customOrdersListColumnContent', 10, 2);
add_action('manage_woocommerce_page_wc-orders_custom_column', 'customOrdersListColumnContent', 10, 2); // HPOS

/**
 * Insert our actions in the order overview bulk action selection.
 */
function bulkActionsEditProduct(array $actions): array
{
    $actions['export_myparcel_order'] = 'Export orders to MyParcel.com';
    $actions['print_label_shipment'] = 'Print MyParcel.com label';

    return $actions;
}

add_filter('bulk_actions-edit-shop_order', 'bulkActionsEditProduct', 20, 1);
add_filter('bulk_actions-woocommerce_page_wc-orders', 'bulkActionsEditProduct', 20, 1); // HPOS

/**
 * Handle bulk action to export orders.
 */
function myparcelcomBulkActionHandler(string $redirectTo, string $action, array $orderIds): string
{
    $queryParam = ['_customer_user', 'm', 'check_action', 'shipment_created_amount', 'shipment_error_messages'];
    $redirectTo = remove_query_arg($queryParam, $redirectTo);

    if ($action === 'export_myparcel_order') {
        $orderShippedCount = 0;

        foreach ($orderIds as $orderId) {
            $order = wc_get_order($orderId);
            $shipmentId = $order->get_meta(MYPARCEL_SHIPMENT_ID);

            // If no shipment ID is found, we check the legacy meta, which is used by our v2.x plugin.
            $legacyMeta = $order->get_meta(MYPARCEL_LEGACY_SHIPMENT_META);
            if (!empty($legacyMeta)) {
                $legacyData = json_decode($legacyMeta, true);
                $shipmentId = $legacyData[MYPARCEL_LEGACY_SHIPMENT_ID] ?? null;
            }

            if (empty($shipmentId)) {
                try {
                    $shipment = createShipmentForOrder($orderId);
                    $order->update_meta_data(MYPARCEL_SHIPMENT_ID, $shipment->getId());
                    $order->update_meta_data(MYPARCEL_SHIPMENT_DATA, json_encode([
                        'status_code' => 'shipment-processing',
                        'status_name' => 'Shipment is processing',
                    ]));
                    $order->save_meta_data();
                    $orderShippedCount++;
                } catch (RequestException $exception) {
                    $response = json_decode((string) $exception->getResponse()->getBody(), true);
                    $errorMessages = [
                        implode(' ', [$exception->getMessage(), 'for order', '#' . $orderId]),
                    ];

                    if (isset($response['errors'])) {
                        foreach ($response['errors'] as $error) {
                            if (isset($error['meta']['json_schema_errors'])) {
                                foreach ($error['meta']['json_schema_errors'] as $schemaError) {
                                    if (in_array($schemaError['message'], [
                                        'Failed to match all schemas',
                                        'Failed to match exactly one schema',
                                    ])) {
                                        continue;
                                    }
                                    $errorMessages[] = implode(' ', [
                                        str_replace('data.attributes.', '', $schemaError['property']),
                                        '-',
                                        $schemaError['message']
                                    ]);
                                }
                            } else if (isset($error['detail'])) {
                                $errorMessages[] = $error['detail'];
                            } else if (isset($error['title'])) {
                                $errorMessages[] = $error['title'];
                            }
                        }
                    }

                    return add_query_arg([
                        'check_action'            => 'shipment_error',
                        'shipment_error_messages' => array_map('urlencode', $errorMessages),
                    ], $redirectTo);
                } catch (Throwable $throwable) {
                    return add_query_arg([
                        'check_action'            => 'shipment_error',
                        'shipment_error_messages' => [$throwable->getMessage()],
                    ], $redirectTo);
                }
            }
        }

        $redirectTo = add_query_arg([
            'check_action'            => $orderShippedCount === 0 ? 'shipment_already_created' : 'shipment_created',
            'shipment_created_amount' => $orderShippedCount,
        ], $redirectTo);
    }

    return $redirectTo;
}

add_filter('handle_bulk_actions-edit-shop_order', 'myparcelcomBulkActionHandler', 10, 3);
add_filter('handle_bulk_actions-woocommerce_page_wc-orders', 'myparcelcomBulkActionHandler', 10, 3); // HPOS

set_transient('shipment-plugin-notice', 'alive', 3);

/**
 * Show notices with the results of bulk actions.
 */
function exportPrintBulkActionAdminNotice(): void
{
    if (get_transient('shipment-plugin-notice') === 'alive') {
        if (isset($_REQUEST['check_action'])) {
            switch ($_REQUEST['check_action']) {
                case 'shipment_created':
                    $orderShippedCount = intval( $_REQUEST['shipment_created_amount'] ?? 0 );
                    echo '<div class="notice notice-success is-dismissible"><p>'
                        . sprintf(_n('%s order successfully exported to MyParcel.com', '%s orders successfully exported to MyParcel.com', $orderShippedCount), $orderShippedCount)
                        . '</p></div>';
                    break;

                case 'shipment_already_created':
                    echo '<div class="notice notice-warning is-dismissible"><p>Order already exported to MyParcel.com</p></div>';
                    break;

                case 'shipment_error':
                    $errorMessages = array_map(
                        fn (string $message) => htmlspecialchars(stripslashes($message)),
                        $_REQUEST['shipment_error_messages'], // Already urldecoded.
                    );

                    echo '<div class="notice notice-error is-dismissible"><p>'
                        . implode('<br>', $errorMessages)
                        . '</p></div>';
                    break;
            }
        }
        delete_transient('shipment-plugin-notice');
    }
}

add_action('admin_notices', 'exportPrintBulkActionAdminNotice');

/**
 * Create a shipment via the MyParcel.com API using an order ID.
 * @throws RequestException
 */
function createShipmentForOrder(int $orderId): ShipmentInterface
{
    $pluginData     = get_plugin_data(plugin_dir_path(__FILE__) . '../woocommerce-connect-myparcel.php', false, false);
    $totalWeight    = getTotalWeightByOrderID($orderId) * 1000;
    $countAllWeight = max($totalWeight, 1000);
    $order          = wc_get_order($orderId);
    $orderData      = $order->get_data();
    $shipment       = new Shipment();
    $currency       = $orderData['currency'] ?: get_woocommerce_currency();
    $shop           = getSelectedShop();
    $senderAddress  = $shop->getSenderAddress();
    $shipmentItems  = getShipmentItems($orderId, $currency, $senderAddress->getCountryCode());
    $isDomestic     = $senderAddress->getCountryCode() === $orderData['shipping']['country'];
    $isEU           = isEUCountry($senderAddress->getCountryCode()) && isEUCountry($orderData['shipping']['country']);

    if (!$isDomestic && !$isEU) {
        $customs = (new Customs())
            ->setContentType(Customs::CONTENT_TYPE_MERCHANDISE)
            ->setNonDelivery(Customs::NON_DELIVERY_RETURN)
            ->setIncoterm(Customs::INCOTERM_DAP)
            ->setInvoiceNumber('N/A');
        if ($orderData['shipping_total']) {
            $customs
                ->setShippingValueAmount((int) round($orderData['shipping_total'] * 100))
                ->setShippingValueCurrency($currency);
        }
        $shipment->setCustoms($customs);
    }

    $recipient = (new Address())
        ->setCompany($orderData['shipping']['company'])
        ->setFirstName($orderData['shipping']['first_name'])
        ->setLastName($orderData['shipping']['last_name'])
        ->setStreet1($orderData['shipping']['address_1'])
        ->setStreet2($orderData['shipping']['address_2'])
        ->setPostalCode($orderData['shipping']['postcode'])
        ->setCity($orderData['shipping']['city'])
        ->setCountryCode($orderData['shipping']['country'])
        ->setEmail(!empty($orderData['shipping']['email']) ? $orderData['shipping']['email'] : $orderData['billing']['email'])
        ->setPhoneNumber(!empty($orderData['shipping']['phone']) ? $orderData['shipping']['phone'] : $orderData['billing']['phone']);

    if (isset($orderData['shipping']['state']) && preg_match('/^[A-Z\d]{1,3}$/', $orderData['shipping']['state'])) {
        $recipient->setStateCode($orderData['shipping']['state']);
    }

    $shipment
        ->setRegisterAt(0)
        ->setSenderAddress($senderAddress)
        ->setReturnAddress($shop->getReturnAddress())
        ->setRecipientAddress($recipient)
        ->setPhysicalProperties(
            (new PhysicalProperties())->setWeight((int) round($countAllWeight))
        )
        ->setCustomerReference((string) $orderId)
        ->setDescription('Order #' . $order->get_order_number())
        ->setTags(array_values(array_filter([$orderData['payment_method_title'], $order->get_shipping_method()])))
        ->setItems($shipmentItems)
        ->setShop($shop)
        ->setChannel('WooCommerce_' . $pluginData['Version']);

    if ($orderData['total']) {
        $shipment
            ->setTotalValueAmount((int) round($orderData['total'] * 100))
            ->setTotalValueCurrency($currency);
    }

    $api = MyParcelApi::createSingletonFromConfig();

    return $api->createShipment($shipment);
}

/**
 * Helper function to check if a country code is in the EU.
 */
function isEUCountry(string $countryCode): bool
{
    $euCountryCodes = [
        'AT',
        'BE',
        'BG',
        'HR',
        'CY',
        'CZ',
        'DE',
        'DK',
        'EE',
        'ES',
        'FI',
        'FR',
        'GR',
        'HU',
        'IE',
        'IT',
        'LV',
        'LT',
        'LU',
        'MT',
        'MC',
        'NL',
        'PL',
        'PT',
        'RO',
        'SK',
        'SI',
        'SE',
    ];

    return in_array($countryCode, $euCountryCodes);
}

/**
 * Add meta box input fields on the right side of the "product" edit page.
 */
function addMyparcelcomProductMeta(WP_Post $post): void
{
    add_meta_box('product_country', 'Country Of Origin', 'renderCountryOfOriginInput', 'product', 'side');
    add_meta_box('product_hs_code', 'HS code', 'renderHsCodeInput', 'product', 'side');
}

add_action('add_meta_boxes_product', 'addMyparcelcomProductMeta');

/**
 * Render product meta input field for Country Of Origin.
 */
function renderCountryOfOriginInput(WP_Post $post): void
{
    $value = get_post_meta($post->ID, 'myparcel_product_country', true);
    echo '<label for="coo_input">Country Of Origin</label>';
    echo '<input type="text" name="coo_input" id="coo_input" value="' . $value . '">';
}

/**
 * Render product meta input field for HS code.
 */
function renderHsCodeInput(WP_Post $post): void
{
    $value = get_post_meta($post->ID, 'myparcel_hs_code', true);
    echo '<label for="hs_code_input">HS code</label>';
    echo '<input type="text" name="hs_code_input" id="hs_code_input" value="' . $value . '">';
}

/**
 * Make sure our meta fields are saved when a product is saved.
 */
function saveMyparcelcomProductMeta(int $postId): void
{
    if (array_key_exists('hs_code_input', $_POST)) {
        update_post_meta($postId, 'myparcel_hs_code', $_POST['hs_code_input']);
    }
    if (array_key_exists('coo_input', $_POST)) {
        update_post_meta($postId, 'myparcel_product_country', $_POST['coo_input']);
    }
}

add_action('save_post', 'saveMyparcelcomProductMeta');
