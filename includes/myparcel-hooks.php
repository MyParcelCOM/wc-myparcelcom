<?php
declare(strict_types=1);

use MyParcelCom\ApiSdk\Resources\Address;
use MyParcelCom\ApiSdk\Resources\Shipment;
use MyParcelCom\ApiSdk\Resources\Interfaces\PhysicalPropertiesInterface;
use MyParcelCom\ApiSdk\Resources\ShipmentStatusProxy;
use MyParcelCom\ApiSdk\Resources\ShipmentStatusProxyTest;
use MyParcelCom\ApiSdk\Resources\ShipmentStatusTest;

function myparcelExceptionRedirection()
{
    $_SESSION['errormessage'] = "Incorrect client id or secret.";
    $url = admin_url('/edit.php?post_type=shop_order');
    wp_redirect($url);
    exit;
}

add_action('woocommerce_after_shipping_rate', 'shippingText', 10);
/**
 * @param object $method
 *
 * @return void
 */
function shippingText($method): void
{
    if ('myparcel' === $method->get_method_id()) {
        echo "<p>" . $method->get_meta_data()['delivery_method'] . '/ ' . $method->get_meta_data()['carrier_name'] . '/ ' . $method->get_meta_data()['transit_time'] . "</p>";
        echo "<p>" . $method->get_meta_data()['line_2'] . "</p>";
    }
}

add_action('wp_enqueue_scripts', 'addFrontEndJs');

/**
 *
 * @return void
 */
function addFrontEndJs(): void
{
    wp_enqueue_style('view_order_style', plugins_url('', __FILE__) . '/../assets/front-end/css/frontend-myparcel.css');
    if (is_page('checkout')) {
        wp_register_script('checkout-page-script', plugins_url('woocommerce-connect-myparcel/assets/front-end/js/address-checkout-page.js', _FILE_), '', '1.0', true);
        wp_enqueue_script('checkout-page-script');
    }
}

add_filter('manage_edit-shop_order_columns', 'customShopOrderColumn', 11);

/**
 * @param array $columns
 *
 * @return array
 */
function customShopOrderColumn($columns): array
{
    $newColumn = array();
    $i = 0;
    foreach ($columns as $key => $value) {
        if (5 == $i) {
            $newColumn['order_type'] = __('Shipped by', 'order_type');
            $newColumn['shipped_status'] = __('Shipping status', 'shipped_status');
        }
        $newColumn[$key] = $value;
        $i++;
    }
    return $newColumn;
}


add_action('manage_shop_order_posts_custom_column', 'customOrdersListColumnContent', 10, 2);

/**
 * @param string $column
 *
 * @return void
 */
function customOrdersListColumnContent($column): void
{
    global $post, $woocommerce, $the_order;
    $orderId = $the_order->id;
    renderOrderColumnContent($column, $orderId, $the_order);
}

add_filter('bulk_actions-edit-shop_order', 'bulkActionsEditProduct', 20, 1);

/**
 * @param array $actions
 *
 * @return array
 */
function bulkActionsEditProduct($actions): array
{
    $actions['export_myparcel_order'] = __('Export orders to MyParcel.com', 'export_myparcel_order');
    return $actions;
}

add_filter('handle_bulk_actions-edit-shop_order', 'exportPrintLabelBulkActionHandler', 10, 3);

/**
 * @param string $redirectTo
 * @param string $action
 * @param array $postIds
 *
 * @return string
 */
function exportPrintLabelBulkActionHandler($redirectTo, $action, $postIds): string
{
    $queryParam = array('_customer_user', 'm', 'export_shipment_action', 'export_shipment_action_n', 'check_action');
    $redirectTo = remove_query_arg($queryParam, $redirectTo);
    if ('export_myparcel_order' == $action) {
        $isAllMyParcelOrder = true;
        foreach ($postIds as $postId) {
            if (!isMyParcelOrder($postId)) {
                $isAllMyParcelOrder = false;
                break;
            }
        }
        if ($isAllMyParcelOrder) {
            $orderShippedCount = 0;
            foreach ($postIds as $postId) {
                //Check if order belongs to partial shipment or normal one
                $ifShipmentTrue = get_option('ship_exists');
                $shipKey = get_post_meta($postId, 'myparcel_shipment_key', true);
                //Get tracking key
                $shippedTrackingArray = get_post_meta($postId, 'shipment_track_key', true);
                $shippedTrackingArray = (!empty($shippedTrackingArray)) ? json_decode($shippedTrackingArray, true) : array();
                $shippedData = get_post_meta($postId, '_my_parcel_order_shipment', true);
                if ($shippedData) {
                    $shippedItems = (!empty($shippedData)) ? json_decode($shippedData, true) : '';
                    $totalWeight = 0;
                    $itemIdArr = [];
                    $extractShippedItems = extractShipmentItemArr($shippedItems, $ifShipmentTrue, $totalWeight);
                    $shippedItemeArray = $extractShippedItems["shippedItemeArray"];
                    $shippedItemsNewArr = $extractShippedItems["shippedItemsNewArr"];
                    $shippedCount = $extractShippedItems["shippedCount"];
                    if ($shippedCount < count($shippedItemsNewArr)) {
                        $shippedItemsNewArr = json_encode($shippedItemsNewArr);
                        update_post_meta($postId, '_my_parcel_order_shipment', $shippedItemsNewArr);
                        $packages = WC()->shipping->get_packages();
                        $shipmentTrackKey = createPartialOrderShipment($postId, $totalWeight, $shippedItemeArray);
                        setShipmentTrackingMeta($shippedTrackingArray, $shipmentTrackKey, $shippedItemeArray, $postId);
                    } else {
                        return $redirectTo = add_query_arg(array('check_action' => 'shipped_already_created'), $redirectTo);
                    }

                    $orderShippedCount++;
                    // Update the shipment key 
                    if (!empty($shipKey)) {
                        update_post_meta($postId, 'myparcel_shipment_key', $shipKey); //Update the shipment key on database
                    } else {
                        add_post_meta($postId, 'myparcel_shipment_key', uniqid()); //Update the shipment key on database
                    }
                    $redirectTo = ($orderShippedCount > 0) ? add_query_arg(array('export_shipment_action' => $orderShippedCount, 'check_action' => 'export_order'), $redirectTo) : $redirectTo;
                } else {
                    $order_data = getOrderData($postId);
                    $totalWeight = getTotalWeightByPostID($postId);
                    $packages = WC()->shipping->get_packages();
                    $shipmentTrackKey = createPartialOrderShipment($postId, $totalWeight);
                    $orderShippedCount++;
                    /* Update the shipment key*/
                    updateShipmentKey($shipKey, $postId);
                    $getMyParcelKey = get_post_meta($postId, 'myparcel_shipment_key', true);
                    if ($getMyParcelKey) {
                        update_post_meta($postId, '_my_parcel_shipment_for_normal_order', 'exported');
                    }
                    $redirectTo = ($orderShippedCount > 0) ? add_query_arg(array('export_shipment_action' => $orderShippedCount, 'check_action' => 'export_order'), $redirectTo) : $redirectTo;
                }
            }
        } else {
            $redirectTo = add_query_arg('export_shipment_action_n', 1, $redirectTo);
        }
    }
    return $redirectTo;
}

add_action('admin_notices', 'exportPrintBulkActionAdminNotice');

set_transient("shipment-plugin-notice", "alive", 3);

/**
 *
 * @return void
 */
function exportPrintBulkActionAdminNotice(): void
{
    if ("alive" == get_transient("shipment-plugin-notice")) {
        if (!empty($_REQUEST['export_shipment_action']) && 'export_order' == $_REQUEST['check_action']) {
            $orderShippedCount = intval($_REQUEST['export_shipment_action']);
            printf('<div id="message" class="updated notice notice-success is-dismissible" style="color:green;">' . _n('%s Success: Orders shipment created successfully.', '%s Orders shipment created successfully.', $orderShippedCount) . '</div>', $orderShippedCount);
        } elseif (!empty($_REQUEST['export_shipment_action_n'])) {
            $msgDiv = '<div id="message" class="updated notice notice-success is-dismissible" style="color:red;">ERROR: Please choose only MyParcel.com order.</div>';
            printf($msgDiv);
        } elseif ('already_export_order' == $_REQUEST['check_action']) {
            $msgDiv = '<div id="message" class="updated notice notice-success is-dismissible" style="color:red;">ERROR: Order is already shipped.</div>';
            printf($msgDiv);
        } elseif ('select_shipped_order_first' == $_REQUEST['check_action']) {
            $msgDiv = '<div id="message" class="updated notice notice-success is-dismissible" style="color:red;">ERROR: Please update  shipping quantity first.</div>';
            printf($msgDiv);
        } elseif ('shipped_already_created' == $_REQUEST['check_action']) {
            $msgDiv = '<div id="message" class="updated notice notice-success is-dismissible" style="color:red;">ERROR: Order already exported to MyParcel.com.</div>';
            printf($msgDiv);
        } elseif ('phpsdk_exception_handle' == $_REQUEST['check_action']) {
            $msgDiv = '<div id="message" class="updated notice notice-success is-dismissible" style="color:red;">ERROR: Something went wrong!</div>';
            printf($msgDiv);
        }
        delete_transient("shipment-plugin-notice");
    }

}

/**
 * @param integer $orderId
 *
 * @return bool
 */
function isMyParcelOrder($orderId): bool
{
    $theOrder = wc_get_order($orderId);
    foreach ($theOrder->get_items('shipping') as $itemId => $shippingItemObj) {
        $orderItemName = $shippingItemObj->get_method_id();
        if ('myparcel' == $orderItemName) {
            return true;
        }
    }

    $orderMeta = get_post_meta($orderId, 'myparcel_order_meta', true);
    if ('myparcel' == $orderMeta) {
        return true;
    }
    return false;
}

add_action('wp_head', 'incAdminAjaxUrl');
function incAdminAjaxUrl()
{
    echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}

/**
 * @param array $orderId
 *
 * @return array
 **/
function getPartialShippingQuantity($orderId): array
{
    $orderId = isset($orderId) ? $orderId : 0;
    $getRecords = get_post_meta($orderId, '_my_parcel_order_shipment', true);
    $records = json_decode($getRecords, true);
    return $records;
}

/**
 * Logic for exporing order to Myparcel.com
 *
 * @param array $orderId
 *
 * @return $shipmentId
 **/
function createPartialOrderShipment($orderId, $totalWeight, $shippedItemsNewArr=array())
{
    global $woocommerce;
    $currency = get_woocommerce_currency();
    $countAllWeight = ($totalWeight) ? $totalWeight : 500;
    $order_data = getOrderData($orderId);
    $shipment = new Shipment();
    // SHIPPING INFORMATION:
    $order_shipping_first_name = $order_data['shipping']['first_name'];
    $order_shipping_last_name = $order_data['shipping']['last_name'];
    $order_shipping_address_1 = $order_data['shipping']['address_1'];
    $order_shipping_city = $order_data['shipping']['city'];
    $order_shipping_postcode = $order_data['shipping']['postcode'];
    $order_shipping_country = $order_data['shipping']['country'];
    $order_billing_email = $order_data['billing']['email'];
    $order_billing_phone = $order_data['billing']['phone'];
    $isEU = isEUCountry($order_shipping_country);
    if ($isEU == false) {
        $shipAddItems = setItemForNonEuCountries($orderId, $currency, $shippedItemsNewArr);
    } else {
        $shipAddItems = setItemForEuCountries($orderId, $shippedItemsNewArr);
    }

    $recipient = new Address();    // Creating address object
    $recipient
        ->setStreet1($order_shipping_address_1)
        ->setCity($order_shipping_city)
        ->setPostalCode($order_shipping_postcode)
        ->setFirstName($order_shipping_first_name)
        ->setLastName($order_shipping_last_name)
        ->setCountryCode($order_shipping_country)
        ->setEmail($order_billing_email)
        ->setPhoneNumber($order_billing_phone);

    // Create the shipment and set required parameters.
    $shipment
        ->setRecipientAddress($recipient)
        ->setWeight($countAllWeight, PhysicalPropertiesInterface::WEIGHT_GRAM)
        ->setDescription('Order id: ' . (string)($orderId))
        ->setItems($shipAddItems);

    $getAuth = new MyParcel_API();
    $api = $getAuth->apiAuthentication();
    // Have the SDK determine the cheapest service and post the shipment to the MyParcel.com API.
    $createdShipment = $api->createShipment($shipment);
    $shipmentId = $createdShipment->getId();
    return $shipmentId;

}

/**
 * @param Shipment $shipment
 * @param string $when
 * @return mixed
 **/
function setRegisterAt($shipment, $when = 'now')
{
    $api = MyParcelComApi::getSingleton();
    $shipment->setRegisterAt($when);
    return $api->updateShipment($shipment);
}

/**
 * @param Order $order_id
 * @param string $when
 * @return mixed
 **/
add_action('woocommerce_thankyou', 'express_shipping_update_order_status', 10, 1);
function express_shipping_update_order_status($order_id)
{
    if (!$order_id) return;
    // Get an instance of the WC_Order object
    $order = wc_get_order($order_id);
    // Get the WC_Order_Item_Shipping object data
    foreach ($order->get_shipping_methods() as $shipping_item) {
        $methodId = $shipping_item->get_id();
        $pd = wc_update_order_item_meta($methodId, 'method_id', 'myparcel'); //Update all the method id to myparcel
    }
}

/**
 * @param Orderid $order_id
 * @return bool
 **/
add_action('save_post', 'notify_shop_owner_new_order', 1, 2);
function notify_shop_owner_new_order($order_id)
{
    if (!$order_id) return;
    // Get the post object
    $post = get_post($order_id);
    if ($post->post_type == 'shop_order') {
        update_post_meta($order_id, 'myparcel_order_meta', 'myparcel');
    }
}

/**
 * @param CountryCode $countrycode
 * @return bool
 **/
function isEUCountry($countrycode)
{
    $eu_countrycodes = array(
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DE', 'DK', 'EE', 'EL',
        'ES', 'FI', 'FR', 'GB', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV',
        'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'
    );
    return (in_array($countrycode, $eu_countrycodes));
}

function shutDownFunction()
{
    $error = error_get_last();
    if ($error != null || $error != '') {
        // Given URL
        $url = $error['file'];
        // Search substring
        $key = 'api-sdk';
        $message = '';
        if (strpos($url, $key) == false) {
            $message = 'Not found';
        } else {
            $message = 'Exists';
        }
        // fatal error, E_ERROR === 1
        if ($error['type'] === E_ERROR && $message == "Exists") {
            myparcelExceptionRedirection();
        }

    }
}

register_shutdown_function('shutDownFunction');

add_action('init', 'register_session');
function register_session()
{
    if (!session_id())
        session_start();
}
