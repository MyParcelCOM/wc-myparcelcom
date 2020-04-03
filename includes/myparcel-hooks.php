<?php
declare(strict_types=1);

use MyParcelCom\ApiSdk\MyParcelComApi;
use MyParcelCom\ApiSdk\Resources\Address;
use MyParcelCom\ApiSdk\Resources\Shipment;
use MyParcelCom\ApiSdk\Resources\Interfaces\PhysicalPropertiesInterface;

function myparcelExceptionRedirection()
{
    $_SESSION['errormessage'] = ERROR_MESSAGE_CREDENTIAL;
    $url                      = admin_url('/edit.php?post_type=shop_order');
    wp_redirect($url);
    exit;
}

/**
 *
 * @return void
 */
function addFrontEndJs()
{
    $absolutePath = __FILE__;
    wp_enqueue_style(
        VIEW_STYLE_TYPE,
        plugins_url('', $absolutePath).'/../assets/front-end/css/frontend-myparcel.css'
    );
    if (is_page('checkout')) {
        wp_register_script(
            CHECKOUT_PAGE_SCRIPT,
            plugins_url('woocommerce-connect-myparcel/assets/front-end/js/address-checkout-page.js', $absolutePath),
            '',
            '1.0',
            true
        );
        wp_enqueue_script('checkout-page-script');
    }
}

add_action('wp_enqueue_scripts', 'addFrontEndJs');

/**
 * @param array $columns
 *
 * @return array
 */
function customShopOrderColumn($columns): array
{
    $customShopOrderColumn = [];
    $i                     = 0;
    foreach ($columns as $key => $value) {
        if (5 === $i) {
            $customShopOrderColumn['order_type']          = __(ORDER_TYPE_TEXT, 'order_type');
            $customShopOrderColumn['shipped_status']      = __(SHIPPED_STATUS_TEXT, 'shipped_status');
            $customShopOrderColumn['shipped_label']       = __(SHIPPED_LABEL, 'shipped_label');
            $customShopOrderColumn['get_shipment_status'] = __(SHIPMENT_STATUS, 'get_shipment_status');
        }
        $customShopOrderColumn[$key] = $value;
        $i++;
    }

    return $customShopOrderColumn;
}

add_filter('manage_edit-shop_order_columns', 'customShopOrderColumn', 11);

/**
 * @param string $column
 *
 * @return void
 */
function customOrdersListColumnContent($column)
{
    global $post, $woocommerce, $the_order;
    $order   = new WC_Order($post->ID);
    $orderId = trim(str_replace('#', '', $order->get_order_number()));
    renderOrderColumnContent($column, $orderId, $the_order);
}

add_action('manage_shop_order_posts_custom_column', 'customOrdersListColumnContent', 10, 2);

/**
 * @param array $actions
 *
 * @return array
 */
function bulkActionsEditProduct($actions): array
{
    $actions['export_myparcel_order'] = __(EXPORT_ORDER_TO_MYPARCELCOM_TEXT, 'export_myparcel_order');
    $actions['print_label_shipment']  = __(PRINT_LABEL_SHIPMENT_TEXT, 'print_label_shipment');

    return $actions;
}

add_filter('bulk_actions-edit-shop_order', 'bulkActionsEditProduct', 20, 1);

/**
 * @param string $redirectTo
 * @param string $action
 * @param array  $postIds
 *
 * @return string
 */
function exportPrintLabelBulkActionHandler($redirectTo, $action, $postIds): string
{
    $queryParam = ['_customer_user', 'm', 'export_shipment_action', 'export_shipment_action_n', 'check_action'];
    $redirectTo = remove_query_arg($queryParam, $redirectTo);
    if (EXPORT_MYPARCEL_ORDER_TEXT === $action) {
        $isAllMyParcelOrder = true;
        foreach ($postIds as $postId) {
            if (!isMyParcelOrder($postId)) {
                $isAllMyParcelOrder = false;
                break;
            }
        }
        if (!empty($isAllMyParcelOrder)) {
            $orderShippedCount = 0;
            foreach ($postIds as $postId) {
                //Check if order belongs to partial shipment or normal one
                $ifShipmentTrue = get_option('ship_exists');
                $shipKey        = get_post_meta($postId, GET_META_MYPARCEL_SHIPMENT_KEY, true);
                //Get tracking key
                $shippedTrackingArray = get_post_meta($postId, GET_META_SHIPMENT_TRACKING_KEY, true);
                $shippedTrackingArray = (!empty($shippedTrackingArray)) ? json_decode($shippedTrackingArray, true) : [];
                $shippedData          = get_post_meta($postId, GET_META_MYPARCEL_ORDER_SHIPMENT_TEXT, true);
                if ($shippedData) {
                    $shippedItems        = (!empty($shippedData)) ? json_decode($shippedData, true) : '';
                    $totalWeight         = 0;
                    $extractShippedItems = extractShipmentItemArr($shippedItems, $ifShipmentTrue, $totalWeight);
                    $shippedItemArray    = $extractShippedItems["shippedItemeArray"];
                    $shippedItemsNewArr  = $extractShippedItems["shippedItemsNewArr"];
                    $shippedCount        = $extractShippedItems["shippedCount"];
                    if ($shippedCount < count($shippedItemsNewArr)) {
                        $shippedItemsNewArr = json_encode($shippedItemsNewArr);
                        $packages           = WC()->shipping->get_packages();
                        $shipmentTrackKey   = createPartialOrderShipment($postId, $totalWeight, $shippedItemArray);
                        update_post_meta($postId, GET_META_MYPARCEL_ORDER_SHIPMENT_TEXT, $shippedItemsNewArr);
                        setShipmentTrackingMeta($shippedTrackingArray, $shipmentTrackKey, $shippedItemArray, $postId);
                    } else {
                        return $redirectTo = add_query_arg(['check_action' => 'shipped_already_created'], $redirectTo);
                    }

                    $orderShippedCount++;
                    // Update the shipment key 
                    if (!empty($shipKey)) {
                        update_post_meta(
                            $postId,
                            GET_META_MYPARCEL_SHIPMENT_KEY,
                            $shipKey
                        ); //Update the shipment key on database
                    } else {
                        add_post_meta(
                            $postId,
                            GET_META_MYPARCEL_SHIPMENT_KEY,
                            uniqid()
                        ); //Update the shipment key on database
                    }
                    $redirectTo = ($orderShippedCount > 0) ? add_query_arg(
                        ['export_shipment_action' => $orderShippedCount, 'check_action' => 'export_order'],
                        $redirectTo
                    ) : $redirectTo;
                } else {
                    if (empty($shipKey) || $shipKey === '') {
                        $totalWeight      = getTotalWeightByPostID($postId);
                        $packages         = WC()->shipping->get_packages();
                        $shipmentTrackKey = createPartialOrderShipment($postId, $totalWeight);
                        $orderShippedCount++;
                        /* Update the shipment key*/
                        updateShipmentKey($postId, $shipKey);
                        $getMyParcelKey = get_post_meta($postId, GET_META_MYPARCEL_SHIPMENT_KEY, true);
                        if ($getMyParcelKey) {
                            $shipTrackingArray = [
                                "trackingKey" => $shipmentTrackKey,
                                "items"       => '',
                            ];

                            $shippedTrackingArray = json_encode($shipTrackingArray);
                            update_post_meta($postId, GET_META_SHIPMENT_TRACKING_KEY, $shippedTrackingArray);
                            update_post_meta($postId, '_my_parcel_shipment_for_normal_order', 'exported');
                        }
                        $redirectTo = ($orderShippedCount > 0) ? add_query_arg(
                            ['export_shipment_action' => $orderShippedCount, 'check_action' => 'export_order'],
                            $redirectTo
                        ) : $redirectTo;
                    } else {
                        return $redirectTo = add_query_arg(['check_action' => 'shipped_already_created'], $redirectTo);
                    }
                }
            }
        } else {
            $redirectTo = add_query_arg('export_shipment_action_n', 1, $redirectTo);
        }
    }

    return $redirectTo;
}

add_filter('handle_bulk_actions-edit-shop_order', 'exportPrintLabelBulkActionHandler', 10, 3);

set_transient("shipment-plugin-notice", SHIPMENT_PLUGIN_NOTICE, 3);

/**
 *
 * @return void
 */
function exportPrintBulkActionAdminNotice()
{
    if (SHIPMENT_PLUGIN_NOTICE === get_transient("shipment-plugin-notice")) {
        if (!empty($_REQUEST['export_shipment_action']) && 'export_order' == $_REQUEST['check_action']) {
            $orderShippedCount = intval($_REQUEST['export_shipment_action']);
            printf(
                '<div id="message" class="updated notice notice-success is-dismissible" style="color:green;">'._n(
                    '%s Success: Orders shipment created successfully.',
                    '%s Orders shipment created successfully.',
                    $orderShippedCount
                ).'</div>',
                $orderShippedCount
            );
        } elseif (!empty($_REQUEST['export_shipment_action_n'])) {
            $msgDiv = '<div id="message" class="updated notice notice-success is-dismissible" style="color:red;">ERROR: Please choose only MyParcel.com order.</div>';
            printf($msgDiv);
        } elseif (isset($_REQUEST['check_action']) && $_REQUEST['check_action'] == 'already_export_order') {
            $msgDiv = '<div id="message" class="updated notice notice-success is-dismissible" style="color:red;">ERROR: Order is already shipped.</div>';
            printf($msgDiv);
        } elseif (isset($_REQUEST['check_action']) && $_REQUEST['check_action'] == 'select_shipped_order_first') {
            $msgDiv = '<div id="message" class="updated notice notice-success is-dismissible" style="color:red;">ERROR: Please update  shipping quantity first.</div>';
            printf($msgDiv);
        } elseif (isset($_REQUEST['check_action']) && $_REQUEST['check_action'] == 'shipped_already_created') {
            $msgDiv = '<div id="message" class="updated notice notice-success is-dismissible" style="color:red;">ERROR: Order already exported to MyParcel.com.</div>';
            printf($msgDiv);
        }
        delete_transient("shipment-plugin-notice");
    }
}

add_action('admin_notices', 'exportPrintBulkActionAdminNotice');

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
        if (MYPARCEL_METHOD === $orderItemName) {
            return true;
        }
    }

    $orderMeta = get_post_meta($orderId, GET_META_MYPARCEL_ORDER_META, true);
    if (MYPARCEL_METHOD === $orderMeta) {
        return true;
    }

    return false;
}

/**
 * Admin Ajax URL
 */
function incAdminAjaxUrl()
{
    echo '<script type="text/javascript">
           var ajaxurl = "'.admin_url('admin-ajax.php').'";
         </script>';
}

add_action('wp_head', 'incAdminAjaxUrl');

/**
 * @param array $orderId
 *
 * @return array
 **/
function getPartialShippingQuantity($orderId): array
{
    $orderId    = isset($orderId) ? $orderId : 0;
    $getRecords = get_post_meta($orderId, GET_META_MYPARCEL_ORDER_SHIPMENT_TEXT, true);
    $records    = json_decode($getRecords, true);

    return $records;
}

/**
 * Logic for exporting order to Myparcel.com
 *
 * @param array $orderId
 *
 * @return $shipmentId
 **/
function createPartialOrderShipment($orderId, $totalWeight, $shippedItems = [])
{
    global $woocommerce;
    $currency       = get_woocommerce_currency();
    $countAllWeight = ($totalWeight) ? $totalWeight : 500;
    $orderData      = getOrderData($orderId);
    $shipment       = new Shipment();

    // SHIPPING INFORMATION:
    $orderShippingFirstName = $orderData['shipping']['first_name'];
    $orderShippingLastName  = $orderData['shipping']['last_name'];
    $orderShippingAddress1  = $orderData['shipping']['address_1'];
    $orderShippingAddress2  = $orderData['shipping']['address_2'];
    $orderShippingCompany   = $orderData['shipping']['company'];
    $orderShippingCity      = $orderData['shipping']['city'];
    $orderShippingPostcode  = $orderData['shipping']['postcode'];
    $orderShippingCountry   = $orderData['shipping']['country'];
    $orderBillingEmail      = $orderData['billing']['email'];
    $orderBillingPhone      = $orderData['billing']['phone'];
    $isEU                   = isEUCountry($orderShippingCountry);
    $selectedShop           = getSelectedShop();

    if ($isEU == false) {
        $shipAddItems = setItemForNonEuCountries($orderId, $currency, $shippedItems);
    } else {
        $shipAddItems = setItemForEuCountries($orderId, $shippedItems);
    }
    $recipient = new Address();    // Creating address object
    $recipient
        ->setStreet1($orderShippingAddress1)
        ->setStreet2($orderShippingAddress2)
        ->setCompany($orderShippingAddress1)
        ->setCity($orderShippingCity)
        ->setPostalCode($orderShippingPostcode)
        ->setFirstName($orderShippingFirstName)
        ->setLastName($orderShippingLastName)
        ->setCountryCode($orderShippingCountry)
        ->setEmail($orderBillingEmail)
        ->setPhoneNumber($orderBillingPhone);
    // Create the shipment and set required parameters.
    $shipment
        ->setRecipientAddress($recipient)
        ->setWeight($countAllWeight, PhysicalPropertiesInterface::WEIGHT_GRAM)
        ->setDescription('Order id: '.(string)($orderId))
        ->setItems($shipAddItems)
        ->setShop($selectedShop);

    $getAuth  = new MyParcelApi();
    $api      = $getAuth->apiAuthentication();
    $services = $api->getServices($shipment);
    // Have the SDK determine the cheapest service and post the shipment to the MyParcel.com API.
    $createdShipment = $api->createShipment($shipment);
    $shipmentId      = $createdShipment->getId();
    setShipmentRegister($shipmentId);

    return $shipmentId;
}


/**
 * @param Shipment $shipment
 * @param string   $when
 *
 * @return mixed
 **/
function setShipmentRegister($shipmentId)
{
    $getAuth  = new MyParcelApi();
    $api      = $getAuth->apiAuthentication();
    $shipment = $api->getShipment($shipmentId);
    $shipment->setRegisterAt(new \DateTime());
    $updateShipmentResp = $api->updateShipment($shipment);

    return;
}


/**
 * @param Shipment $shipment
 * @param string   $when
 *
 * @return mixed
 **/
function setRegisterAt($shipment, $when = 'now')
{
    $api = MyParcelComApi::getSingleton();
    $shipment->setRegisterAt($when);

    return $api->updateShipment($shipment);
}

/**
 * @param Order  $order_id
 * @param string $when
 *
 * @return mixed
 **/
add_action('woocommerce_thankyou', 'express_shipping_update_order_status', 10, 1);
function express_shipping_update_order_status($order_id)
{
    if (!$order_id) {
        return;
    }
    // Get an instance of the WC_Order object
    $order = wc_get_order($order_id);
    // Get the WC_Order_Item_Shipping object data
    foreach ($order->get_shipping_methods() as $shipping_item) {
        $methodId = $shipping_item->get_id();
        $pd       = wc_update_order_item_meta(
            $methodId,
            'method_id',
            MYPARCEL_METHOD
        ); //Update all the method id to myparcel
    }
}

/**
 * @param Orderid $order_id
 *
 * @return bool
 **/
add_action('save_post', 'notify_shop_owner_new_order', 1, 2);
function notify_shop_owner_new_order($order_id)
{
    if (!$order_id) {
        return;
    }
    // Get the post object
    $post = get_post($order_id);
    if ($post->post_type == 'shop_order') {
        update_post_meta($order_id, GET_META_MYPARCEL_ORDER_META, MYPARCEL_METHOD);
    }
}

/**
 * @param CountryCode $countryCode
 *
 * @return bool
 **/
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
        'GB',
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

    return (in_array($countryCode, $euCountryCodes));
}

function shutDownFunction()
{
    $error = error_get_last();
    if ($error != null || $error != '') {
        // Given URL
        $url = $error['file'];
        // Search substring
        $key     = KEY_TEXT;
        $message = '';
        if (strpos($url, $key) == false) {
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