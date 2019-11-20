<?php

declare(strict_types=1);

use MyParcelCom\ApiSdk\Resources\Shipment;
use MyParcelCom\ApiSdk\Resources\ShipmentItem;
use MyParcelCom\ApiSdk\Resources\File;

/**
 * @param $orderId
 *
 * @return mixed
 */
function getOrderData($orderId)
{
    $order      = wc_get_order($orderId);
    $order_data = $order->get_data();

    return $order_data;
}

/**
 * @param $orderId
 *
 * @return mixed
 */
function getOrderItems($orderId)
{
    $order = wc_get_order($orderId);
    $items = $order->get_items();

    return $items;
}

/**
 * @param $productId
 *
 * @return mixed
 */
function getWeightByProductId($productId)
{
    $product = wc_get_product($productId);
    $weight  = $product->get_weight();

    return $weight;
}

/**
 * @param $postId
 *
 * @return float|int
 */
function getTotalWeightByPostID($postId)
{
    $items       = getOrderItems($postId);
    $totalWeight = 0;
    foreach ($items as $item) {
        $product = wc_get_product($item['product_id']);
        // Now you have access to (see above)...
        $quantity       = $item->get_quantity(); // get quantity
        $product        = $item->get_product(); // get the WC_Product object
        $product_weight = $product->get_weight(); // get the product weight
        $totalWeight    += floatval($product_weight * $quantity);
    }

    return $totalWeight;
}

//set Ship item for non EU country by post id
function setItemForNonEuCountries($orderId, $currency, $shippedItemsNewArr)
{
    global $woocommerce;
    $items        = getOrderItems($orderId);
    $shipAddItems = [];
    if ($shippedItemsNewArr) {
        foreach ($shippedItemsNewArr as $getShippedItem) {
            $item_id   = $getShippedItem["item_id"];
            $shipItems = new ShipmentItem();
            $product   = wc_get_product($items[$item_id]['product_id']);
            // Now you have access to (see above)...
            $quantity    = $getShippedItem["shipped"]; // get quantity
            $productName = $product->get_name();
            $sku         = ($product->get_sku()) ? $product->get_sku() : MYPARCEL_NA_TEXT;    // Get the product SKU
            $price       = $product->get_price(); // Get the product price
            $itemValue   = ($price * 1) * 100;
            $shipItems
                ->setSku($sku)
                ->setDescription($productName)
                ->setQuantity($quantity)
                ->setItemValue($itemValue)
                ->setCurrency($currency);

            $shipAddItems[] = $shipItems;
        }
    } else {
        foreach ($items as $item) {
            $shipItems = new ShipmentItem();
            $product   = wc_get_product($items[$item_id]['product_id']);
            // Now you have access to (see above)...
            $quantity    = $item->get_quantity(); // get quantity
            $product     = $item->get_product(); // get the WC_Product object
            $productName = $product->get_name();
            $sku         = ($product->get_sku()) ? $product->get_sku() : MYPARCEL_NA_TEXT;    // Get the product SKU
            $price       = $product->get_price(); // Get the product price
            $itemValue   = ($price * 1) * 100;
            $shipItems
                ->setSku($sku)
                ->setDescription($productName)
                ->setQuantity($quantity)
                ->setItemValue($itemValue)
                ->setCurrency($currency);

            $shipAddItems[] = $shipItems;
        }
    }

    return $shipAddItems;
}

//set Ship item for EU country by post id
function setItemForEuCountries($orderId, $shippedItemsNewArr)
{
    global $woocommerce;
    $items        = getOrderItems($orderId);
    $shipAddItems = [];
    if ($shippedItemsNewArr) {
        foreach ($shippedItemsNewArr as $getShippedItem) {
            $item_id     = $getShippedItem["item_id"];
            $shipItems   = new ShipmentItem();
            $product     = wc_get_product($items[$item_id]['product_id']);
            $quantity    = $getShippedItem["shipped"]; // get quantity
            $productName = $product->get_name();
            $shipItems
                ->setDescription($productName)
                ->setQuantity($quantity);
            $shipAddItems[] = $shipItems;
        }
    } else {
        foreach ($items as $item) {
            $shipItems   = new ShipmentItem();
            $product     = wc_get_product($item['product_id']);
            $quantity    = $item->get_quantity(); // get quantity
            $productName = $product->get_name();
            $shipItems
                ->setDescription($productName)
                ->setQuantity($quantity);
            $shipAddItems[] = $shipItems;
        }
    }

    return $shipAddItems;
}

/**
 * @param string $orderId
 * @param string $itemId
 * @param string $shipQty
 * @param string $totalShipQty
 * @param string $qty
 * @param string $type
 * @param string $weight
 * @param string $remainQty
 * @param string $flagStatus
 *
 * @return array
 */
function setOrderShipment(
    $orderId,
    $itemId,
    $shipQty,
    $totalShipQty,
    $qty,
    $weight,
    $remainQty,
    $flagStatus,
    $type = SHIPPED_TEXT
): array {
    $shipmentNewAr                  = [];
    $shipmentNewAr['order_id']      = $orderId;
    $shipmentNewAr['item_id']       = $itemId;
    $shipmentNewAr['shipped']       = $shipQty;
    $shipmentNewAr['total_shipped'] = $totalShipQty;
    $shipmentNewAr['qty']           = $qty;
    $shipmentNewAr['type']          = $type;
    $shipmentNewAr['weight']        = $weight;
    $shipmentNewAr['remain_qty']    = $remainQty;
    $shipmentNewAr['flagStatus']    = $flagStatus;

    return $shipmentNewAr;
}

/**
 * @param $shippedItems
 * @param $ifShipmentTrue
 * @param $totalWeight
 *
 * @return array
 */
function extractShipmentItemArr($shippedItems, $ifShipmentTrue, &$totalWeight)
{
    $shippedItemeArray  = [];
    $shippedItemsNewArr = [];
    $shippedCount       = 0;
    foreach ($shippedItems as $key => $shippedItem) {
        $type               = $shippedItem['type'];
        $shippedQtyNew      = $shippedItem['shipped'];
        $totalShippedQtyNew = $shippedItem['total_shipped'];
        $totalQtyNew        = $shippedItem['qty'];
        $remainQtyNew       = $shippedItem['remain_qty'];
        $weightNew          = $shippedItem['weight'];
        $flagStatus         = $shippedItem['flagStatus'];
        $item_id            = $shippedItem['item_id'];
        if (1 == $ifShipmentTrue) {
            if ($remainQtyNew == 0) {  //logic for weight > 0
                $totalWeight += $weightNew * $totalQtyNew;
            } else {
                $totalWeight += $weightNew * $totalShippedQtyNew;  // All shipped quantity
            }
            $shippedItem["flagStatus"] = 1;
        } else {
            if (0 == $flagStatus) {
                $totalWeight               += $weightNew * $shippedQtyNew;
                $shippedItem["flagStatus"] = 1;
                array_push(
                    $shippedItemeArray,
                    [
                        "item_id" => $item_id,
                        "shipped" => $shippedQtyNew,
                        "weight"  => $totalWeight,
                    ]
                );
            } else {
                $shippedCount++;
            }
        }
        array_push($shippedItemsNewArr, $shippedItem);
    }
    $response = [
        "shippedItemeArray"  => $shippedItemeArray,
        "shippedItemsNewArr" => $shippedItemsNewArr,
        "shippedCount"       => $shippedCount,
    ];

    return $response;
}

/**
 * @param array $shippedTrackingArray
 * @param array $shipmentTrackKey
 * @param array $shippedItemeArray
 * @param int   $postId
 */
function setShipmentTrackingMeta($shippedTrackingArray, $shipmentTrackKey, $shippedItemeArray, $postId)
{
    $shipTrackingArray = [
        "trackingKey" => $shipmentTrackKey,
        "items"       => $shippedItemeArray,
    ];
    array_push($shippedTrackingArray, $shipTrackingArray);
    $shippedTrackingArray = json_encode($shippedTrackingArray);
    update_post_meta($postId, GET_META_SHIPMENT_TRACKING_KEY, $shippedTrackingArray);
}

/**
 * @param null $shipKey
 * @param int  $postId
 */
function updateShipmentKey($shipKey = null, $postId)
{
    if (!empty($shipKey)) {
        update_post_meta($postId, GET_META_MYPARCEL_SHIPMENT_KEY, $shipKey); //Update the shipment key on database
    } else {
        add_post_meta($postId, GET_META_MYPARCEL_SHIPMENT_KEY, uniqid()); //Update the shipment key on database
    }
}

/**
 * @param $shipped
 * @param $key
 * @param $itemQuantity
 * @param $orderId
 * @param $itemId
 * @param $qtyHtml
 * @param $tdHtml
 * @param $remainHtml
 */
function prepareHtmlForUpdateQuantity(
    $shipped,
    $key,
    $itemQuantity,
    $orderId,
    $itemId,
    &$qtyHtml,
    &$tdHtml,
    &$remainHtml
) {
    if (is_int($key)) {
        if (isset($shipped[$key]['type']) && 'shipped' == $shipped[$key]['type']) {
            if (isset($shipped[$key]['total_shipped']) && $shipped[$key]['total_shipped'] == $itemQuantity) {
                $addRemainQty = (isset($shipped[$key]['remain_qty'])) ? $shipped[$key]['remain_qty'] : $shipped[$key]['qty'];
                $qtyHtml      = '<input type="text" name="ship_qty" class="ship_qty ship_qty_'.$itemId.'" value="'.$shipped[$key]['total_shipped'].'" data-flag-id="0" data-rqty="'.$addRemainQty.'" data-qty="'.$itemQuantity.'" data-old-qty="'.$shipped[$key]['total_shipped'].'" data-item-id="'.$itemId.'" data-order-id="'.$orderId.'" style="width: 43px;"/>';

                $tdHtml = '<a href="javascript:void(0);" class="partial-anchor-top partial-anchor-top-'.$itemId.'" title="Shipped: '.$shipped[$key]['total_shipped'].'/'.$itemQuantity.'"><span class="new-shipped-color ship-status ship-status-'.$itemId.'">Updated Shipping Qty - '.$shipped[$key]['total_shipped'].'</span></a>';

                $remainHtml = '<a href="javascript:void(0);" class="partial-anchor-remain-'.$itemId.'"><span class="remain-qty">'.$shipped[$key]['remain_qty'].'</span></a>';

            } elseif (isset($shipped[$key]['total_shipped']) && $shipped[$key]['total_shipped'] > 0 && isset($shipped[$key]['total_shipped']) && $shipped[$key]['total_shipped'] < $itemQuantity) {
                $addRemainQty = (!empty($shipped[$key]['remain_qty'])) ? $shipped[$key]['remain_qty'] : $shipped[$key]['qty'];
                $qtyHtml      = '<input type="text" name="ship_qty" class="ship_qty ship_qty_'.$itemId.'" value="'.$shipped[$key]['total_shipped'].'" data-flag-id="0" data-rqty="'.$addRemainQty.'" data-qty="'.$itemQuantity.'" data-old-qty="'.$shipped[$key]['total_shipped'].'" data-item-id="'.$itemId.'" data-order-id="'.$orderId.'" style="width: 43px;"/>';
                $tdHtml       = '<a href="javascript:void(0);" class="partial-anchor-top partial-anchor-top-'.$itemId.'" title="Partially Shipped: '.$shipped[$key]['total_shipped'].'/'.$itemQuantity.'"><span class="partial-shipped-color ship-status ship-status-'.$itemId.'">Partially Shipped - '.$shipped[$key]['total_shipped'].'</span></a>';
                $remainHtml   = '<a href="javascript:void(0);" class="partial-anchor-remain-'.$itemId.'"><span class="remain-qty">'.$shipped[$key]['remain_qty'].'</span></a>';
            }
        }
    } else {
        $qtyHtml    = '<input type="text" name="ship_qty" class="ship_qty ship_qty_'.$itemId.'" value="'.$itemQuantity.'" data-flag-id="0" data-rqty="'.$itemQuantity.'" data-qty="'.$itemQuantity.'" data-old-qty="0" data-item-id="'.$itemId.'" data-order-id="'.$orderId.'" style="width: 43px;"/>';
        $tdHtml     = '<a href="javascript:void(0);" class="partial-anchor-top partial-anchor-top-'.$itemId.'" title="Not Shipped"><span class="not-shipped-color ship-status ship-status-'.$itemId.'">Not Shipped - '.$itemQuantity.'</span></a>';
        $remainHtml = '<a href="javascript:void(0);" class="partial-anchor-remain-'.$itemId.'"><span class="remain-qty">'.$itemQuantity.'</span></a>';
    }
}

/**
 * @desc setting page html
 */
function prepareHtmlForSettingPage()
{
    ?>
  <div>
    <h2><?php echo MYPARCEL_API_SETTING_TEXT; ?></h2>
    <form method="post" action="options.php" id="api-setting-form">
        <?php
        settings_fields('myplugin_options_group');
        ?>
      <table class="form-table">
        <tbody>
        <tr valign="top">
          <th scope="row"><label for="client_key"><?php echo MYPARCEL_API_CLIENTID_LABEL_TEXT; ?> </label></th>
          <td>
            <input type="text" id="client_key" class="regular-text" name="client_key"
                   value="<?php echo get_option('client_key'); ?>"/>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="client_secret_key"><?php echo MYPARCEL_API_CLIENTSECRET_LABEL_TEXT; ?> </label>
          </th>
          <td>
            <input type="password" id="client_secret_key" class="regular-text" name="client_secret_key"
                   value="<?php echo get_option('client_secret_key'); ?>"/>
          </td>
        </tr>
        <tr>
          <th scope="row"><?php echo MYPARCEL_API_ACT_TESTMODE_TEXT; ?></th>
          <td>
            <fieldset>
              <legend class="screen-reader-text"><span></span></legend>
              <label for="users_can_register">
                <input type="checkbox" name="act_test_mode"
                       value="1" <?php checked(1, (int)get_option('act_test_mode')); ?>>
              </label>
            </fieldset>
          </td>
        </tr>
        </tbody>
      </table>
      <h2><?php echo MYPARCEL_API_TEXT; ?></h2>
      <table cellpadding="5" cellspacing="5" class="form-table">
        <tr valign="top">
          <th scope="row"><label><?php echo MYPARCEL_API_CURRENT_VERSION; ?></label></th>
          <td>1.0</td>
        </tr>
        <tr valign="top">
          <th scope="row"><label><?php echo MYPARCEL_API_SUPPORT_TEXT; ?></label></th>
          <td>
            <a href="<?php echo MYPARCEL_SUPPORT_TEXT_AND_URL; ?>" target="_blank"><?php echo MYPARCEL_SUPPORT_TEXT_AND_URL; ?></a>
          </td>
        </tr>
      </table>
        <?php submit_button('Save changes'); ?>
    </form>
  </div>
    <?php
}

/**
 * @desc enqueue js and css file
 */
function enqueueJsAndCssFile()
{
    wp_enqueue_style('font-awesome-icon', plugins_url('', __FILE__).'/../../assets/admin/css/font-awesome.css');
    wp_enqueue_style('fancybox', plugins_url('', __FILE__).'/../../assets/admin/css/jquery.fancybox.min.css');
    wp_enqueue_style('wcp_style', plugins_url('', __FILE__).'/../../assets/admin/css/admin-myparcel.css');
    wp_enqueue_script(
        'fancybox',
        plugins_url('', __FILE__).'/../../assets/admin/js/jquery.fancybox.min.js',
        ['jquery'],
        '',
        false
    );
    wp_register_script(
        'wcp_partial_ship_script',
        plugins_url('', __FILE__).'/../../assets/admin/js/admin-myparcel.js',
        ['fancybox'],
        '',
        true
    );
    wp_enqueue_script('wcp_partial_ship_script');
}

/**
 * @param        $column
 * @param int    $orderId
 * @param object $the_order
 */
function renderOrderColumnContent($column, $orderId, $the_order)
{
    switch ($column) {
        case 'order_type' :
            $post = get_post($orderId);
            if ($post->post_type === 'shop_order') {
                $getOrderMeta = get_post_meta($orderId, GET_META_MYPARCEL_SHIPMENT_KEY, true);
                if (isset($getOrderMeta) && !empty($getOrderMeta)) {
                    echo "<span style='color:green;'>".MYPARCEL_API_TEXT."<input type='hidden' class='myparcel' value='".$orderId."'/></span>";
                    break;
                }
            }
            foreach ($the_order->get_items('shipping') as $itemId => $shippingItemObj) {
                $orderItemName   = $shippingItemObj->get_method_id();
                $myparcelShipKey = get_post_meta($orderId, GET_META_MYPARCEL_SHIPMENT_KEY, true);
                if (isset($myparcelShipKey) && !empty($myparcelShipKey)) {
                    echo "<span style='color:green;'>".MYPARCEL_API_TEXT."<input type='hidden' class='myparcel' value='".$orderId."'/></span>";
                    break;
                }
            }
            break;

        case 'shipped_status' :
            $order                = wc_get_order($orderId);
            $items                = $order->get_items();
            $orderShipmentDetails = json_decode(
                get_post_meta($orderId, GET_META_MYPARCEL_ORDER_SHIPMENT_TEXT, true),
                true
            );
            $orderShipmentStatus  = "";
            if (!empty($orderShipmentDetails)) {
                $totalCount     = count($items);
                $shipOrderCount = 0;
                foreach ($orderShipmentDetails as $orderShipmentDetail) {
                    $remainQty = $orderShipmentDetail['remain_qty'];
                    if ($remainQty === 0 && $orderShipmentDetail['flagStatus'] === 1) {
                        $shipOrderCount++;
                    } else {
                        if ($remainQty !== 0 && $orderShipmentDetail['flagStatus'] === 1) {
                            $orderShipmentStatus = "<mark class='order-status partial-shipped-color'><span>".MYPARCEL_PARTIALLY_SHIPPED_TEXT."</span></mark>";
                            break;
                        } else {
                            if ($remainQty === 0 && $orderShipmentDetail['flagStatus'] === 0) {
                                $orderShipmentStatus = "<mark class='order-status partial-shipped-color'><span>".MYPARCEL_PARTIALLY_SHIPPED_TEXT."</span></mark>";
                                break;
                            }
                        }
                    }
                }
                $orderShipmentStatus = ($totalCount === $shipOrderCount) ? "<mark class='order-status status-completed'><span>".MYPARCEL_FULLY_SHIPPED_TEXT."</span></mark>" : (($orderShipmentStatus === "" && $shipOrderCount === 0) ? "" : "<mark class='order-status partial-shipped-color'><span>".MYPARCEL_PARTIALLY_SHIPPED_TEXT."</span></mark>");
            } else {
                if (!empty(get_post_meta($orderId, GET_META_MYPARCEL_SHIPMENT_KEY, true))) {
                    $orderShipmentStatus = "<mark class='order-status status-completed'><span>".MYPARCEL_FULLY_SHIPPED_TEXT."</span></mark>";
                }
            }
            echo $orderShipmentStatus;
            break;

        case 'shipped_label' :
            if (!$orderId) {
                return;
            } // Exit
            getShipmentFiles($orderId);
            break;

        case 'get_shipment_status' :
            if (!$orderId) {
                return;
            } // Exit
            $shipmentData = getShipmentCurrentStatus($orderId);
            if (!empty($shipmentData)) {
                $shipmentValues = json_decode($shipmentData);
                ?>
              <div class="order-status status-completed" id="welcomeShipment" title="<?php echo $shipmentValues->description; ?>">
                <span><?php echo ucfirst($shipmentValues->name); ?></span>
              </div>
                <?php
            }
            break;
    }
}

function getAuthToken()
{
    $clientKey       = get_option('client_key');
    $clientSecretKey = get_option('client_secret_key');
    if (!empty($clientKey) && !empty($clientSecretKey)) {
        $data              = [
            "grant_type"    => "client_credentials",
            "client_id"     => $clientKey,
            "client_secret" => $clientSecretKey,
            "scope"         => "*",
        ];
        $data_string       = json_encode($data);
        $url               = MYPARCEL_WEBHOOK_ACCESS_TOKEN;
        $authorization     = '';
        $result            = createWebhookCurlRequest($url, $data_string, $authorization);
        $getToken          = json_decode($result);
        $myparcelWebhookId = get_option(MYPARCEL_WEBHOOK_OPTION_ID);
        if (empty($myparcelWebhookId)) {
            registerMyParcelWebhook($getToken->access_token);
        }
    }

}

/**
 * @param $accessToken
 */
function registerMyParcelWebhook($accessToken)
{
    $webhookUrl      = plugins_url('', dirname(__FILE__)).'/webhook.php';
    $webhookname     = getDefaultShopId().'-myparcelcom';
    $data            = [
        "data" =>
            [
                "type"          => "hooks",
                "attributes"    =>
                    [
                        "name"    => $webhookname,
                        "order"   => 100,
                        "active"  => true,
                        "trigger" => [
                            "resource_type"   => "shipment-statuses",
                            "resource_action" => "create",
                        ],
                        "action"  => [
                            "action_type" => "send-resource",
                            "values"      => [
                                [
                                    "url"      => $webhookUrl,
                                    "includes" => [
                                        "status",
                                        "shipment",
                                    ],
                                ],
                            ],
                        ],
                    ],
                "relationships" => [
                    "owner" => [
                        "data" => [
                            "type" => "shops",
                            "id"   => getDefaultShopId(),
                        ],
                    ],
                ],

            ],
    ];
    $data_string     = json_encode($data);
    $authorization   = "Authorization: Bearer $accessToken";
    $url             = MYPARCEL_WEBHOOK_URL;
    $result          = createWebhookCurlRequest($url, $data_string, $authorization);
    $webhookResponse = json_decode($result);
    if (get_option(MYPARCEL_WEBHOOK_OPTION_ID) !== false) {
        update_option(MYPARCEL_WEBHOOK_OPTION_ID, $webhookResponse->data->id);
    } else {
        $deprecated = null;
        $autoload   = 'no';
        add_option(MYPARCEL_WEBHOOK_OPTION_ID, $webhookResponse->data->id, $deprecated, $autoload);
    }
}

/**
 * @return string
 */
function getDefaultShopId()
{
    $getAuth       = new MyParcel_API();
    $api           = $getAuth->apiAuthentication();
    $shop          = $api->getDefaultShop();
    $defaultShopId = $shop->getId();

    return !empty($defaultShopId) ? $defaultShopId : MYPARCEL_DEFAULT_SHOP_ID;
}

/**
 * @param $post_id
 *
 * @throws Exception
 */
function getShipmentFiles($post_id)
{
    $getOrderMetaData = get_post_meta($post_id, GET_META_SHIPMENT_TRACKING_KEY, true);
    $getOrderMeta     = json_decode($getOrderMetaData);
    if (!$getOrderMeta) {
        return;
    } // Exit
    $sslOptions         = [
        "ssl" => [
            "verify_peer"      => false,
            "verify_peer_name" => false,
        ],
    ];
    $logFileContent     = plugins_url('', dirname(__FILE__)).'/request.log';
    $getShipmentContent = file_get_contents($logFileContent, false, stream_context_create($sslOptions));
    if (!isset($getOrderMeta->trackingKey)) {
        return;
    }
    $isInRegex = "/$getOrderMeta->trackingKey/";
    // preg_match returns true or false.
    if (preg_match($isInRegex, $getShipmentContent) && !empty($getOrderMeta->trackingKey)) {
        $getAuth        = new MyParcel_API();
        $shipment       = new Shipment();
        $api            = $getAuth->apiAuthentication();
        $shipment       = $api->getShipment($getOrderMeta->trackingKey);
        $getRegisterAt  = $shipment->getRegisterAt();
        $shipmentStatus = $shipment->getShipmentStatus();
        $status         = $shipmentStatus->getStatus();
        if (!empty($getRegisterAt) && ($status->getCode() === MYPARCEL_SHIPMENT_REGISTERED)) {
            $getAuth  = new MyParcel_API();
            $file     = new File();
            $api      = $getAuth->apiAuthentication();
            $shipment = $api->getShipment($getOrderMeta->trackingKey);
            $labels   = $shipment->getFiles(File::DOCUMENT_TYPE_LABEL);
            if (!empty($labels)) {
                foreach ($labels as $label) {
                    $label = $label->getBase64Data('application/pdf');
                    echo '<p><a class="button download-label" download="label.pdf" href="data:application/octet-stream;base64,'.$label.'"><i class="fa fa-file-pdf-o" style="font-size:20px;color:red"></i></a></p>';
                    ?>
                    <?php
                }
                ?>
              <script type="text/javascript">
                jQuery(document).ready(function ($) {
                  let a = $('.download-label')
                  a.click()
                })
              </script>
            <?php }
        } else {
            $shipment->setRegisterAt(new \DateTime());
            $updateShipmentResp = $api->updateShipment($shipment);
        }
    }
}

/**
 * @param      $url
 * @param      $data_string
 * @param null $authorization
 *
 * @return bool|string
 */
function createWebhookCurlRequest($url, $data_string, $authorization = null)
{
    switch ($url) {
        case MYPARCEL_WEBHOOK_URL:
            $httpHeader = [
                'Content-Type: application/json',
                $authorization,
            ];
            break;
        case MYPARCEL_WEBHOOK_ACCESS_TOKEN:
            $httpHeader = [
                'Content-Type: application/json',
            ];
            break;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
    }
    curl_close($ch);

    return $result;
}

/**
 * @param $post_id
 *
 * @return array|false|string|void
 */
function getShipmentCurrentStatus($post_id)
{
    $shipmentData     = [];
    $getOrderMetaData = get_post_meta($post_id, GET_META_SHIPMENT_TRACKING_KEY, true);
    $getOrderMeta     = json_decode($getOrderMetaData);
    if (!$getOrderMeta) {
        return;
    } // Exit
    $getAuth  = new MyParcel_API();
    $shipment = new Shipment();
    $api      = $getAuth->apiAuthentication();
    if (!empty($getOrderMeta->trackingKey)) {
        $shipment                    = $api->getShipment($getOrderMeta->trackingKey);
        $shipmentStatus              = $shipment->getShipmentStatus();
        $status                      = $shipmentStatus->getStatus();
        $shipmentData['name']        = $status->getName();
        $shipmentData['description'] = $status->getDescription();
        $shipmentData                = json_encode($shipmentData);

        return $shipmentData;
    }
}
