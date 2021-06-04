<?php

declare(strict_types=1);

use MyParcelCom\ApiSdk\Resources\Address;
use MyParcelCom\ApiSdk\Resources\Customs;
use MyParcelCom\ApiSdk\Resources\PhysicalProperties;
use MyParcelCom\ApiSdk\Resources\Shipment;

function myparcelExceptionRedirection()
{
    $_SESSION['errormessage'] = ERROR_MESSAGE_CREDENTIAL;
    $url                      = admin_url('/edit.php?post_type=shop_order');
    wp_redirect($url);
    exit;
}

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
    $order   = new WC_Order($post->ID);
    $orderId = trim(str_replace('#', '', $order->get_order_number()));
    renderOrderColumnContent($column, $orderId, $the_order);
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
    $queryParam = ['_customer_user', 'm', 'export_shipment_action', 'check_action'];
    $redirectTo = remove_query_arg($queryParam, $redirectTo);

    if ($action === 'export_myparcel_order') {
        $orderShippedCount = 0;

        foreach ($postIds as $postId) {
            $shipmentKey = get_post_meta($postId, GET_META_MYPARCEL_SHIPMENT_KEY, true);

            if (empty($shipmentKey)) {
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
                $redirectTo = ($orderShippedCount > 0) ? add_query_arg(
                    ['export_shipment_action' => $orderShippedCount, 'check_action' => 'export_order'],
                    $redirectTo
                ) : $redirectTo;
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
        if (!empty($_REQUEST['export_shipment_action']) && 'export_order' == $_REQUEST['check_action']) {
            $orderShippedCount = intval($_REQUEST['export_shipment_action']);
            echo '<div class="notice notice-success is-dismissible" style="color:green;"><p>'
                . sprintf(_n('%s order successfully exported to MyParcel.com', '%s orders successfully exported to MyParcel.com', $orderShippedCount), $orderShippedCount)
                . '</p></div>';
        } elseif (isset($_REQUEST['check_action']) && $_REQUEST['check_action'] == 'shipment_already_created') {
            echo '<div class="notice notice-success is-dismissible" style="color:red;"><p>Order already exported to MyParcel.com</p></div>';
        }
        delete_transient('shipment-plugin-notice');
    }
}

add_action('admin_notices', 'exportPrintBulkActionAdminNotice');

/**
 * @param int $orderId
 * @return string
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
                ->setShippingValueAmount($orderData['shipping_total'] * 100)
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
            (new PhysicalProperties())->setWeight($countAllWeight)
        )
        ->setCustomerReference((string) $orderId)
        ->setDescription('Order #' . $orderId)
        ->setTags([$orderData['payment_method_title'], $order->get_shipping_method()])
        ->setItems($shipmentItems)
        ->setShop($shop)
        ->setChannel('WooCommerce_' . MYPARCEL_PLUGIN_VERSION);

    if ($orderData['total']) {
        $shipment
            ->setTotalValueAmount($orderData['total'] * 100)
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
        'EL',
        'ES',
        'FI',
        'FR',
        'HU',
        'IE',
        'IT',
        'LT',
        'LU',
        'LV',
        'MT',
        'NL',
        'PL',
        'PT',
        'RO',
        'SE',
        'SI',
        'SK',
    ];

    return in_array($countryCode, $euCountryCodes);
}

function shutDownFunction()
{
    $error = error_get_last();
    if ($error != null || $error != '') {
        // Given URL
        $url = $error['file'];
        // Search substring
        if (strpos($url, 'api-sdk') == false) {
            $message = NOT_FOUND_TEXT;
        } else {
            $message = IS_EXISTS_TEXT;
        }
        // fatal error, E_ERROR === 1
        if ($error['type'] === E_ERROR && $message === IS_EXISTS_TEXT) {
            myparcelExceptionRedirection();
        }
    }
}

register_shutdown_function('shutDownFunction');

function register_session()
{
    if (!session_id()) {
        session_start();
    }
}

add_action('init', 'register_session');

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
