<?php

declare(strict_types=1);

use MyParcelCom\ApiSdk\Resources\Shipment;
use MyParcelCom\ApiSdk\Resources\ShipmentItem;
use MyParcelCom\ApiSdk\Resources\File;
use MyParcelCom\ApiSdk\LabelCombiner;
use MyParcelCom\ApiSdk\Resources\Interfaces\FileInterface;
use MyParcelCom\ApiSdk\LabelCombinerInterface;

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
    $shipments                  = [];
    $shipments['order_id']      = $orderId;
    $shipments['item_id']       = $itemId;
    $shipments['shipped']       = $shipQty;
    $shipments['total_shipped'] = $totalShipQty;
    $shipments['qty']           = $qty;
    $shipments['type']          = $type;
    $shipments['weight']        = $weight;
    $shipments['remain_qty']    = $remainQty;
    $shipments['flagStatus']    = $flagStatus;

    return $shipments;
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
    $shippedItemArray   = [];
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
                    $shippedItemArray,
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
        "shippedItemArray"   => $shippedItemArray,
        "shippedItemsNewArr" => $shippedItemsNewArr,
        "shippedCount"       => $shippedCount,
    ];

    return $response;
}

/**
 * @param array  $shippedTrackingArray
 * @param string $shipmentTrackKey
 * @param array  $shippedItemArray
 * @param int    $postId
 */
function setShipmentTrackingMeta($shippedTrackingArray, $shipmentTrackKey, $shippedItemArray, $postId)
{
    $shipTrackingArray = [
        "trackingKey" => $shipmentTrackKey,
        "items"       => $shippedItemArray,
    ];
    array_push($shippedTrackingArray, $shipTrackingArray);
    $shippedTrackingArray = json_encode($shippedTrackingArray);
    update_post_meta($postId, GET_META_SHIPMENT_TRACKING_KEY, $shippedTrackingArray);
}

/**
 * @param int  $postId
 * @param null $shipKey
 */
function updateShipmentKey($postId, $shipKey = null)
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
 *
 * @return array
 */
function getMyParcelShopList()
{
    $getAuth = new MyParcelApi();
    $api     = $getAuth->apiAuthentication();
    $shops   = $api->getShops()->get();

    return $shops;
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
        <tr valign="top">
          <th scope="row"><label for="myparcel_shopid"><?php echo MYPARCEL_SHOPID; ?> </label>
          </th>
          <td>
            <select class="regular-text" id="myparcel_shopid" name="myparcel_shopid">
                <?php
                $shops = getMyParcelShopList();
                foreach ($shops as $shop) {
                    if (!empty(get_option('myparcel_shopid'))) {
                        echo "<option value='".$shop->getId()."' ".($shop->getId() == get_option(
                                'myparcel_shopid'
                            ) ? 'selected' : '').">".$shop->getName()."</option>";
                    } else {
                        echo "<option value='".$shop->getId()."' ".($shop->getId(
                            ) == MYPARCEL_DEFAULT_SHOP_ID ? 'selected' : '').">".$shop->getName()."</option>";
                    }
                }
                ?>
            </select>
            <p><?php echo MYPARCEL_SHOPID_HELPTEXT; ?></p>

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
          <td>2.0.1</td>
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

/**
 * Get Auth Token
 */
function getAuthToken()
{
    $clientKey       = get_option('client_key');
    $clientSecretKey = get_option('client_secret_key');
    if (!empty($clientKey) && !empty($clientSecretKey)) {
        $data          = [
            "grant_type"    => "client_credentials",
            "client_id"     => $clientKey,
            "client_secret" => $clientSecretKey,
            "scope"         => "*",
        ];
        $dataString    = json_encode($data);
        $url           = MYPARCEL_WEBHOOK_ACCESS_TOKEN;
        $authorization = '';
        $result        = createWebHookCurlRequest($url, $dataString, $authorization);
        if (!empty($result)) {
            $getToken = json_decode($result);
            registerMyParcelWebHook($getToken->access_token);
        }
    }
}

/**
 * @param $accessToken
 */
function registerMyParcelWebHook($accessToken)
{
    $webHookUrl      = plugins_url('', dirname(__FILE__)).'/webhook.php';
    $webHookName     = getRegisteredShopId().'-wpmyparcelcom';
    $data            = [
        "data" =>
            [
                "type"          => "hooks",
                "attributes"    =>
                    [
                        "name"    => $webHookName,
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
                                    "url"      => $webHookUrl,
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
                            "id"   => getRegisteredShopId(),
                        ],
                    ],
                ],

            ],
    ];
    $dataString      = json_encode($data);
    $authorization   = "Authorization: Bearer $accessToken";
    $url             = MYPARCEL_WEBHOOK_URL;
    $result          = createWebHookCurlRequest($url, $dataString, $authorization);
    $webHookResponse = json_decode($result);
    if (get_option(MYPARCEL_WEBHOOK_OPTION_ID) !== false) {
        update_option(MYPARCEL_WEBHOOK_OPTION_ID, $webHookResponse->data->id);
    } else {
        $deprecated = null;
        $autoload   = 'no';
        add_option(MYPARCEL_WEBHOOK_OPTION_ID, $webHookResponse->data->id, $deprecated, $autoload);
    }
}

/**
 * @return string
 */
function getDefaultShopId(): string
{
    $getAuth       = new MyParcelApi();
    $api           = $getAuth->apiAuthentication();
    $shop          = $api->getDefaultShop();
    $defaultShopId = $shop->getId();

    return !empty($defaultShopId) ? $defaultShopId : MYPARCEL_DEFAULT_SHOP_ID;
}

/**
 * @return string
 */
function getRegisteredShopId(): string
{
    $regsiteredShopId = get_option('myparcel_shopid');

    return $regsiteredShopId;
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
    if (!isset($getOrderMeta->trackingKey)) {
        return;
    }

    $webHookData = get_option(MYPARCEL_WEBHOOK_RESPONSE);
    if (!empty($webHookData)) {
        $getShipmentContent = json_decode($webHookData, true);
        $getShipmentData    = $getShipmentContent['included'];
        if (!empty($getShipmentData)) {
            $id = array_column($getShipmentData, 'id');
            if (in_array($getOrderMeta->trackingKey, $id)) {
                update_post_meta($post_id, MYPARCEL_RESPONSE_META, 1);
            }
        }
    }
    $webHookResponseMeta = get_post_meta($post_id, MYPARCEL_RESPONSE_META, true);
    if (($webHookResponseMeta == 1) && !empty($getOrderMeta->trackingKey)) {
        $getAuth        = new MyParcelApi();
        $shipment       = new Shipment();
        $api            = $getAuth->apiAuthentication();
        $shipment       = $api->getShipment($getOrderMeta->trackingKey);
        $getRegisterAt  = $shipment->getRegisterAt();
        $shipmentStatus = $shipment->getShipmentStatus();
        $status         = $shipmentStatus->getStatus();
        if (!empty($getRegisterAt) && ($status->getCode() === MYPARCEL_SHIPMENT_REGISTERED)) {
            $getAuth  = new MyParcelApi();
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
 * @param      $dataString
 * @param null $authorization
 *
 * @return bool|string
 */
function createWebHookCurlRequest($url, $dataString, $authorization = null)
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
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
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
    $getAuth  = new MyParcelApi();
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


add_action('manage_posts_extra_tablenav', 'admin_order_list_top_bar_button', 20, 1);
function admin_order_list_top_bar_button($which)
{
    global $typenow;
    if ('shop_order' === $typenow && 'top' === $which) {
        ?>
      <!-- Button trigger modal -->
      <button type="button" id="primary-modal" class="btn btn-primary" data-toggle="modal" data-target="#labelModal" title="Print Selected">
        <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
      </button>
      <!-- Modal -->
      <div class="modal fade" id="labelModal" tabindex="-1" role="dialog" aria-labelledby="labelModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="labelModalLabel">Label position</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <div class="row">
                <div class="col-lg-6" id="printer-orintation">
                  <label class="container">
                    <input type="radio" checked="checked" name="selectorientation" class="toggle" value="1"> A4 - default printer
                  </label>
                  <label class="container">
                    <input type="radio" name="selectorientation" class="toggle" value="2"> A6 - label printer
                  </label>
                </div>
              </div>
            </div>
            <div class="modal-body">
              <div class="row cntnr" id="orientation1" style="margin: 0px;">
                <div class="col-lg-6">
                  <label class="container radio-inline">
                    <input type="radio" checked="checked" name="radio" class="toggle" value="1"> 1
                  </label>
                </div>
                <div class="col-lg-6">
                  <label class="container radio-inline">
                    <input type="radio" name="radio" class="toggle" value="2"> 2
                  </label>
                </div>
                <div class="col-lg-6">
                  <label class="container radio-inline">
                    <input type="radio" name="radio" class="toggle" value="3"> 3
                  </label>
                </div>
                <div class="col-lg-6">
                  <label class="container radio-inline">
                    <input type="radio" name="radio" class="toggle" value="4"> 4
                  </label>
                </div>
              </div>
              <div class="row cntnr" id="orientation2" style="display: none;">
              </div>
            </div>
            <div class="modal-footer">
              <div id='loadingmessage' style='display:none'>
                  <?php $loader = plugins_url('', __FILE__).'/../../assets/images/ajax-loader.gif'; ?>
                <img class="img-responsive center-block" src="<?php echo $loader; ?>"/>
              </div>
              <div class="alert alert-danger alert-dismissible fade in text-center" role="alert" style="display: none;">
                Label is not available.
              </div>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary" id="download-pdf">Download</button>
            </div>
          </div>
        </div>
      </div>
      <style type="text/css">
        .modal-dialog {
          width: 30%;
          margin: 0 auto;
        }

        .modal-content {
          height: auto;
          min-height: 100%;
          border-radius: 0;
        }

        .cntnr .col-lg-6 {
          border: 1px solid grey;
          padding: 50px;
        }

        label.container input[type=radio] {
          height: 16px;
        }

        .modal-content {
          top: 35px;
        }
      </style>
      <script type="text/javascript">
        jQuery(document).ready(function ($) {
          var selectVal = $('#printer-orintation input[name=\'selectorientation\']:checked').val()
          $('#printer-orintation input[name=\'selectorientation\']').click(function () {
            selectVal = $(this).val()
            $('div.cntnr').hide()
            $('#orientation' + selectVal).show()
          })
          $('#download-pdf').click(function (e) {
            var selected = []
            e.preventDefault()
            $('#loadingmessage').show()  // show the loading message.
            $('.wp-list-table #the-list tr input[name=\'post[]\']:checked').map(function () {
              if ($('.wp-list-table #the-list tr input[name=\'post[]\']').is(':checked')) {
                var idx = $.inArray($(this).val(), selected)
                if (idx == -1) {
                  selected.push($(this).val())
                }
              } else {
                selected.splice($(this).val())
              }
            }) // <----
            var selectOrientation = $('input[name=\'radio\']:checked').val()
            if (selectOrientation) {
              var data = {
                'action': 'my_action',
                'selectOrientation': selectOrientation,
                'orderIds': selected,
                'labelPrinter': selectVal
              }
              var templateUrl = '<?= get_site_url(); ?>'
              var ajaxScript = {ajax_url: templateUrl + '/wp-admin/admin-ajax.php'}
              // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
              jQuery.post(ajaxScript.ajax_url, data, function (response) {
                var checkFailed = '<?= MYPARCEL_FAILED_TEXT; ?>'
                if (response === checkFailed) {
                  $('.modal-footer .alert-danger').fadeTo(2000, 500).slideUp(500, function () {
                    $('.modal-footer .alert-danger').slideUp(500)
                  })
                  $('#loadingmessage').hide() // hide the loading message
                } else {
                  const linkSource = 'data:application/pdf;base64,' + response
                  const downloadLink = document.createElement('a')
                  const fileName = 'label.pdf'
                  downloadLink.href = linkSource
                  downloadLink.download = fileName
                  downloadLink.click()
                  $('#loadingmessage').hide() // hide the loading message
                  $('#labelModal').modal('hide')
                }
              })
            }
            return false
          })
        })
      </script>
        <?php
    }
}

add_action('wp_ajax_my_action', 'my_action');
function my_action()
{
    define(LOCATION_TOP, 1);
    define(LOCATION_BOTTOM, 2);
    define(LOCATION_RIGHT, 4);
    define(LOCATION_LEFT, 8);
    define(LOCATION_TOP_LEFT, LOCATION_TOP | LOCATION_LEFT);
    define(LOCATION_TOP_RIGHT, LOCATION_TOP | LOCATION_RIGHT);
    define(LOCATION_BOTTOM_LEFT, LOCATION_BOTTOM | LOCATION_LEFT);
    define(LOCATION_BOTTOM_RIGHT, LOCATION_BOTTOM | LOCATION_RIGHT);
    global $wpdb;
    $selectOrientation = intval($_POST['selectOrientation']);
    $orderIds          = $_POST['orderIds'];
    $labelPrinter      = intval($_POST['labelPrinter']);
    $getAuth           = new MyParcelApi();
    $labelCombiner     = new LabelCombiner();
    $api               = $getAuth->apiAuthentication();
    $shipments         = [];
    foreach ($orderIds as $orderId) {
        $getShipmentKey = get_post_meta($orderId, 'shipment_track_key', true);
        if (!empty($getShipmentKey)) {
            $getShipmentKey = json_decode($getShipmentKey);
            $shipments[]    = $api->getShipment($getShipmentKey->trackingKey);
        }
    }
    $files = [];
    if (!empty($shipments)) {
        foreach ($shipments as $shipment) {
            $files = array_merge(
                $files,
                $shipment->getFiles(File::DOCUMENT_TYPE_LABEL)
            );
        }
    }
    $combinedFile = $labelCombiner->combineLabels(
        $files,
        labelPrinter($labelPrinter),
        getOrientation($selectOrientation),
        20
    );
    echo $combinedFile->getBase64Data();
    wp_die(); // this is required to terminate immediately and return a proper response
}

/**
 * @param $selectOrientation
 *
 * @return mixed
 */
function getOrientation($selectOrientation)
{
    switch ($selectOrientation) {
        case 1:
            return LOCATION_TOP_LEFT;
            break;
        case 2:
            return LOCATION_TOP_RIGHT;
            break;
        case 3:
            return LOCATION_BOTTOM_LEFT;
            break;
        default:
            return LOCATION_BOTTOM_RIGHT;
            break;
    }
}

/**
 * @param $labelPrinter
 *
 * @return string
 */
function labelPrinter($labelPrinter)
{
    if (!empty($labelPrinter) && ($labelPrinter === 1)) {

        return LabelCombinerInterface::PAGE_SIZE_A4;
    } else {

        return LabelCombinerInterface::PAGE_SIZE_A6;
    }
}

/**
 * Get selected Shop
 *
 * @return array
 **/
function getSelectedShop()
{
    $getAuth = new MyParcelApi();
    $api     = $getAuth->apiAuthentication();
    $shops   = $api->getShops()->get();
    foreach ($shops as $shop) {
        if ($shop->getId() == getRegisteredShopId()) {
            $shopDetails = $shop;

            return $shopDetails;
        }
    }
}
