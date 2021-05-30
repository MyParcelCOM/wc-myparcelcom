<?php declare(strict_types=1);

/**
 * @return void
 */
function adminLoadJsCss()
{
    ?>
  <script type="text/javascript">
    var ajaxUrl = "<?php echo admin_url('admin-ajax.php') ?>"
  </script>
    <?php
    $screen = get_current_screen();
    if ('shop_order' === $screen->id) {
        enqueueJsAndCssFile();
    }
    if ('edit-shop_order' === $screen->id) {
        enqueueJsAndCssFile();
        wp_enqueue_style('bootstrap-cdn', plugins_url('', __FILE__).'/../assets/admin/css/bootstrap.min.css');
        wp_enqueue_style('bootstrap-cdn-min', plugins_url('', __FILE__).'/../assets/admin/css/bootstrap-theme.min.css');
        wp_enqueue_script('bootstrap-cdn-jquey', plugins_url('', __FILE__).'/../assets/admin/js/bootstrap.min.js');
        if (!session_id()) {
            session_start();
        }
        if (!empty($_SESSION['errormessage'])) {
            $errMessage = $_SESSION['errormessage'];
            ?>
          <div id="export-wrong-cred" class="notice-error notice is-dismissible">
            <p><?php _e($errMessage, 'woocommerce'); ?></p>
          </div>
            <?php
        }
        unset($_SESSION['errormessage']);
    }
}

add_action('admin_enqueue_scripts', 'adminLoadJsCss', 999);

/**
 * @param object $order
 * @return void
 */
function orderItemHeaders($order)
{
    $orderId = $order->get_id();
    if (isMyParcelOrder($orderId)) {
        echo '<th class="partial_item_head">'.__('Shipped Qty', 'partial-shipment').'</th>';
        echo '<th class="partial_item_head">Shipping Status</th>';
        echo '<th class="partial_item_head">Remaining Quantity</th>';
    }
}

add_action('woocommerce_admin_order_item_headers', 'orderItemHeaders', 10, 1);

/**
 * @param object  $product
 * @param object  $item
 * @param integer $itemId
 * @return void
 */
function orderItemValues($product, $item, $itemId)
{
    if (isMyParcelOrder($item->get_order_id())) {
        if ($product) {
            $itemQuantity = $item->get_quantity();
            $orderId      = $item->get_order_id();
            $itemId       = $item->get_id();
            $shipped      = get_post_meta($orderId, GET_META_MYPARCEL_ORDER_SHIPMENT_TEXT, true);
            $shipped      = (!empty($shipped)) ? json_decode($shipped, true) : '';

            $myParcelShipmentNormalOrder = get_post_meta($orderId, '_my_parcel_shipment_for_normal_order', true);

            $tdHtml = '<a href="javascript:void(0);" class="partial-anchor-top partial-anchor-top-'.$itemId.'" title="Not Shipped">';
            $tdHtml .= '<span class="not-shipped-color ship-status" title="Not Shipped"> Not Shipped - '.$itemQuantity.'</span>';
            $tdHtml .= '</a>';

            $qtyHtml = '<input type="text" name="ship_qty" class="ship_qty ship_qty_'.$itemId.'" value="'.$itemQuantity.'" data-qty="'.$itemQuantity.'" data-old-qty="0" data-flag-id="0" data-rqty="'.$itemQuantity.'" data-item-id="'.$itemId.'" data-order-id="'.$orderId.'" style="width: 43px;"/>';

            $remainHtml = '<a href="javascript:void(0);" class="partial-anchor-remain-'.$itemId.'"><span class="remain-qty">'.$itemQuantity.'</span></a>';

            if ($myParcelShipmentNormalOrder) {
                $tdHtml = '<a href="javascript:void(0);" class="partial-anchor-top partial-anchor-top-'.$itemId.'" title="Not Shipped">';
                $tdHtml .= '<span class="shipped-color ship-status" title="Not Shipped"> Shipped </span>';
                $tdHtml .= '</a>';

                $qtyHtml = '<input type="text" name="ship_qty" class="ship_qty ship_qty_'.$itemId.'" value="'.$itemQuantity.'" data-qty="'.$itemQuantity.'" data-old-qty="0" data-flag-id="0" data-rqty="'.$itemQuantity.'" data-item-id="'.$itemId.'" data-order-id="'.$orderId.'" style="width: 43px;"/>';

                echo '<td class="partital-td-item"><span class="text-span">'.$qtyHtml.' <i class="fa fa-truck fa-sm" aria-hidden="true"></i></span> <input type="button" class="btn btn-success btn-quanity-update" id="update-quantity-'.$itemId.'" value="Update Quantity"></td>';
                echo '<td class="partial-status-td" width="1%">'.$tdHtml.'</td>';
                echo '<td class="remain-status-td" width="1%"> 0 </td>';

            } else {
                if (!empty($orderId)) {
                    if (!empty($shipped)) {
                        $key = array_search($itemId, array_column($shipped, 'item_id'));
                        prepareHtmlForUpdateQuantity(
                            $shipped,
                            $key,
                            $itemQuantity,
                            $orderId,
                            $itemId,
                            $qtyHtml,
                            $tdHtml,
                            $remainHtml
                        );
                    }
                    echo '<td class="partital-td-item"><span class="text-span">'.$qtyHtml.' <i class="fa fa-truck fa-sm" aria-hidden="true"></i></span> <input type="button" class="btn btn-success btn-quanity-update" id="update-quantity-'.$itemId.'" value="Update Quantity"></td>';
                    echo '<td class="partial-status-td" width="1%">'.$tdHtml.'</td>';
                    echo '<td class="remain-status-td" width="1%">'.$remainHtml.'</td>';
                }
            }
        } else {
            echo '<td colspan="3"></td>';
        }
    }
}

add_action('woocommerce_admin_order_item_values', 'orderItemValues', 10, 3);

/**
 * @return object
 */
function orderSetShipped(): object
{
    $orderId      = isset($_POST['order_id']) ? $_POST['order_id'] : 0;
    $itemId       = $_POST['item_id'];
    $qty          = $_POST['qty'];
    $shipQty      = $_POST['ship_quantity'];
    $productId    = $_POST['productId'];
    $flagStatus   = $_POST['flagStatus'];
    $order        = new WC_Order($orderId);
    $weight       = getWeightByProductId($productId);
    $shipments    = get_post_meta($orderId, GET_META_MYPARCEL_ORDER_SHIPMENT_TEXT, true);
    $shipments    = (!empty($shipments)) ? json_decode($shipments, true) : [];
    $itemIds      = (!empty($shipments)) ? array_column($shipments, 'item_id') : [];
    $totalShipQty = 0;

    if (!empty($shipments)) {
        foreach ($shipments as $key => $shipment) {
            if ($itemId == $shipment['item_id']) {
                $totalShipQty    = (int)$shipQty + (int)$shipment['total_shipped'];
                $remainQty       = $qty - $totalShipQty;
                $shipments[$key] = setOrderShipment(
                    $orderId,
                    $itemId,
                    $shipQty,
                    $totalShipQty,
                    $qty,
                    $weight,
                    $remainQty,
                    $flagStatus,
                    "shipped"
                );
            }
        }
        update_post_meta($orderId, GET_META_MYPARCEL_ORDER_SHIPMENT_TEXT, json_encode($shipments));
    } else {
        if (!empty($itemIds) && !in_array($itemId, $itemIds)) {
            $totalShipQty = $shipQty;
            $remainQty    = $qty - $totalShipQty;
            $shipments[]  = setOrderShipment(
                $orderId,
                $itemId,
                $shipQty,
                $totalShipQty,
                $qty,
                $weight,
                $remainQty,
                $flagStatus,
                "shipped"
            );

            update_post_meta($orderId, GET_META_MYPARCEL_ORDER_SHIPMENT_TEXT, json_encode($shipments));
        }
    }

    echo json_encode(
        [
            'order_id'   => $orderId,
            'item_id'    => $itemId,
            'shipped'    => $totalShipQty,
            'qty'        => $qty,
            'weight'     => $weight,
            'remain_qty' => $remainQty,
            'flagStatus' => $flagStatus,
        ]
    );
    exit;
}

add_action('wp_ajax_order_set_shipped', 'orderSetShipped');
