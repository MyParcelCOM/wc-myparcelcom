<?php

declare(strict_types=1);

use MyParcelCom\ApiSdk\Http\Exceptions\RequestException;
use MyParcelCom\ApiSdk\Resources\Address;
use MyParcelCom\ApiSdk\Resources\Customs;
use MyParcelCom\ApiSdk\Resources\PhysicalProperties;
use MyParcelCom\ApiSdk\Resources\Shipment;

/**
 * @param array $columns
 * @return array
 */
function customShopOrderColumn($columns): array
{
    $customShopOrderColumn = [];
    foreach ($columns as $key => $value) {
        $customShopOrderColumn[$key] = $value;
        if ($key === 'order_status') {
            $customShopOrderColumn['get_shipment_status'] = __('MyParcel.com status', 'get_shipment_status');
            // TODO: reactivate the label column once webhooks are properly implemented
            //$customShopOrderColumn['shipped_label'] = __('Label', 'shipped_label');
        }
    }

    return $customShopOrderColumn;
}

add_filter('manage_edit-shop_order_columns', 'customShopOrderColumn', 11);

/**
 * @param string $column
 * @return void
 */
function customOrdersListColumnContent($column)
{
    global $post, $the_order;
    $order = new WC_Order($post->ID);
    renderOrderColumnContent($column, $order->get_id(), $the_order);
}

add_action('manage_shop_order_posts_custom_column', 'customOrdersListColumnContent', 10, 2);

/**
 * @param array $actions
 * @return array
 */
function bulkActionsEditProduct($actions): array
{
    $actions['export_myparcel_order'] = 'Export orders to MyParcel.com';
    $actions['print_label_shipment'] = 'Print MyParcel.com label';

    return $actions;
}

add_filter('bulk_actions-edit-shop_order', 'bulkActionsEditProduct', 20, 1);

/**
 * @param string $redirectTo
 * @param string $action
 * @param int[]  $postIds
 * @return string
 */
function exportPrintLabelBulkActionHandler($redirectTo, $action, $postIds): string
{
    $queryParam = ['_customer_user', 'm', 'check_action', 'shipment_created_amount', 'shipment_error_messages'];
    $redirectTo = remove_query_arg($queryParam, $redirectTo);

    if ($action === 'export_myparcel_order') {
        $orderShippedCount = 0;

        foreach ($postIds as $postId) {
            $shipmentKey = get_post_meta($postId, GET_META_MYPARCEL_SHIPMENT_KEY, true);

            if (empty($shipmentKey)) {
                try {
                    $shipmentTrackKey = createShipmentForOrder($postId);

                    $orderShippedCount++;
                    /* Update the shipment key*/
                    updateShipmentKey($postId, $shipmentKey);
                    $shipmentKey = get_post_meta($postId, GET_META_MYPARCEL_SHIPMENT_KEY, true);
                    if ($shipmentKey) {
                        $shippedTrackingArray = json_encode([
                            'trackingKey' => $shipmentTrackKey,
                            'items'       => '',
                        ]);
                        update_post_meta($postId, GET_META_SHIPMENT_TRACKING_KEY, $shippedTrackingArray);
                    }

                    if ($orderShippedCount > 0) {
                        $redirectTo = add_query_arg([
                            'check_action'            => 'shipment_created',
                            'shipment_created_amount' => $orderShippedCount,
                        ], $redirectTo);
                    }
                } catch (RequestException $exception) {
                    $response = json_decode((string) $exception->getResponse()->getBody(), true);
                    $errorMessages = [
                        implode(' ', [$exception->getMessage(), 'for order', '#' . $postId]),
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
            } else {
                return add_query_arg(['check_action' => 'shipment_already_created'], $redirectTo);
            }
        }
    }

    return $redirectTo;
}

add_filter('handle_bulk_actions-edit-shop_order', 'exportPrintLabelBulkActionHandler', 10, 3);

set_transient('shipment-plugin-notice', SHIPMENT_PLUGIN_NOTICE, 3);

/**
 * @return void
 */
function exportPrintBulkActionAdminNotice()
{
    if (SHIPMENT_PLUGIN_NOTICE === get_transient("shipment-plugin-notice")) {
        if (isset($_REQUEST['check_action'])) {
            switch ($_REQUEST['check_action']) {
                case 'shipment_created':
                    $orderShippedCount = intval(isset($_REQUEST['shipment_created_amount']) ? $_REQUEST['shipment_created_amount'] : 0);
                    echo '<div class="notice notice-success is-dismissible" style="color:green;"><p>'
                        . sprintf(_n('%s order successfully exported to MyParcel.com', '%s orders successfully exported to MyParcel.com', $orderShippedCount), $orderShippedCount)
                        . '</p></div>';
                    break;

                case 'shipment_already_created':
                    echo '<div class="notice notice-success is-dismissible" style="color:red;"><p>Order already exported to MyParcel.com</p></div>';
                    break;

                case 'shipment_error':
                    $errorMessages = array_map('htmlspecialchars', $_REQUEST['shipment_error_messages']);

                    echo '<div class="notice notice-success is-dismissible" style="color:red;"><p>'
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
 * @param int $orderId
 * @return string
 * @throws RequestException
 */
function createShipmentForOrder($orderId)
{
    $totalWeight    = getTotalWeightByPostID($orderId) * 1000;
    $countAllWeight = $totalWeight > 1000 ? $totalWeight : 1000;
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
        ->setEmail($orderData['billing']['email'])
        ->setPhoneNumber($orderData['billing']['phone']);

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
        ->setChannel('WooCommerce_' . MYPARCEL_PLUGIN_VERSION);

    if ($orderData['total']) {
        $shipment
            ->setTotalValueAmount((int) round($orderData['total'] * 100))
            ->setTotalValueCurrency($currency);
    }

    $getAuth = new MyParcelApi();
    $api = $getAuth->apiAuthentication();
    $createdShipment = $api->createShipment($shipment);

    return $createdShipment->getId();
}

/**
 * @param string $countryCode
 * @return bool
 */
function isEUCountry($countryCode)
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

function extra_fields_for_myparcel() {
    $screens = [ 'product' ];
    foreach ( $screens as $screen ) {
        add_meta_box( 'product_country', 'Country Of Origin', 'country_of_origin_fn', $screen, 'side' );
        add_meta_box( 'product_hs_code', 'HS code', 'hs_code_fn', $screen, 'side' );
    }
}
add_action( 'add_meta_boxes', 'extra_fields_for_myparcel' );

function country_of_origin_fn( $post ) {
    $value = get_post_meta( $post->ID, 'myparcel_product_country', true ); ?>
    <label for="coo_input">Country Of Origin</label>
    <input type="text" name="coo_input" id="coo_input" value="<?php echo $value; ?>">
    <?php
}

function hs_code_fn( $post ) {
    $value = get_post_meta( $post->ID, 'myparcel_hs_code', true ); ?>
    <label for="hs_code_input">HS code</label>
    <input type="text" name="hs_code_input" id="hs_code_input" value="<?php echo $value; ?>">
    <?php
}

function save_product_metadata_myparcel( $post_id ) {
    if ( array_key_exists( 'hs_code_input', $_POST ) ) {
        update_post_meta( $post_id, 'myparcel_hs_code', $_POST['hs_code_input'] );
    }
    if ( array_key_exists( 'coo_input', $_POST ) ) {
        update_post_meta( $post_id, 'myparcel_product_country', $_POST['coo_input'] );
    }
}
add_action( 'save_post', 'save_product_metadata_myparcel' );
