<?php declare(strict_types=1);

add_action('admin_enqueue_scripts', 'adminLoadJsCss', 999);

/**
 *
 * @return void
 */
function adminLoadJsCss(): void
{
    ?>
    <script type="text/javascript">
        var ajaxUrl = "<?php echo admin_url('admin-ajax.php') ?>";
    </script>
    <?php
    $screen = get_current_screen();
    if ('shop_order' == $screen->id) {
        wp_enqueue_style('font-awesome-icon', plugins_url('', __FILE__) . '/../assets/admin/css/font-awesome.css');
        wp_enqueue_style('fancybox', plugins_url('', __FILE__) . '/../assets/admin/css/jquery.fancybox.min.css');
        wp_enqueue_style('wcp_style', plugins_url('', __FILE__) . '/../assets/admin/css/admin-myparcel.css');
        wp_enqueue_script('fancybox', plugins_url('', __FILE__) . '/../assets/admin/js/jquery.fancybox.min.js', array('jquery'), '', false);
        wp_register_script('wcp_partial_ship_script', plugins_url('', __FILE__) . '/../assets/admin/js/admin-myparcel.js', array('fancybox'), '', true);
        wp_enqueue_script('wcp_partial_ship_script');
    }
    if ('edit-shop_order' == $screen->id) {
        if (!session_id())
            session_start();
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

add_action('woocommerce_admin_order_item_headers', 'orderItemHeaders', 10, 1);

/**
 * @param object $order
 *
 * @return void
 */
function orderItemHeaders($order): void
{
    $orderId = $order->get_id();
    if (isMyParcelOrder($orderId)) {
        echo '<th class="partial_item_head">' . __('Shipped Qty', 'partial-shipment') . '</th>';
        echo '<th class="partial_item_head">Shipping Status</th>';
        echo '<th class="partial_item_head">Remaining Quantity</th>';
    }
}

add_action('woocommerce_admin_order_item_values', 'orderItemValues', 10, 3);

/**
 * @param object $product
 * @param object $item
 * @param integer $itemId
 *
 * @return void
 */
function orderItemValues($product, $item, $itemId): void
{
    if (isMyParcelOrder($item->get_order_id())) {
        if ($product) {
            $itemQuantity = $item->get_quantity();
            $orderId = $item->get_order_id();
            $itemId = $item->get_id();
            $shipped = get_post_meta($orderId, '_my_parcel_order_shipment', true);
            $shipped = (!empty($shipped)) ? json_decode($shipped, true) : '';

            $myParcelShipmentNormalOrder = get_post_meta($orderId, '_my_parcel_shipment_for_normal_order', true);

            $tdHtml = '<a href="javascript:void(0);" class="partial-anchor-top partial-anchor-top-' . $itemId . '" title="Not Shipped">';
            $tdHtml .= '<span class="not-shipped-color ship-status" title="Not Shipped"> Not Shipped - ' . $itemQuantity . '</span>';
            $tdHtml .= '</a>';
            $qtyHtml = '<input type="text" name="ship_qty" class="ship_qty ship_qty_' . $itemId . '" value="' . $itemQuantity . '" data-qty="' . $itemQuantity . '" data-old-qty="0" data-flag-id="0" data-rqty="' . $itemQuantity . '" data-item-id="' . $itemId . '" data-order-id="' . $orderId . '" style="width: 43px;"/>';
            $remainHtml = '<a href="javascript:void(0);" class="partial-anchor-remain-' . $itemId . '"><span class="remain-qty">' . $itemQuantity . '</span></a>';
            if ($myParcelShipmentNormalOrder) {
                $tdHtml = '<a href="javascript:void(0);" class="partial-anchor-top partial-anchor-top-' . $itemId . '" title="Not Shipped">';
                $tdHtml .= '<span class="shipped-color ship-status" title="Not Shipped"> Shipped </span>';
                $tdHtml .= '</a>';
                $qtyHtml = '<input type="text" name="ship_qty" class="ship_qty ship_qty_' . $itemId . '" value="' . $itemQuantity . '" data-qty="' . $itemQuantity . '" data-old-qty="0" data-flag-id="0" data-rqty="' . $itemQuantity . '" data-item-id="' . $itemId . '" data-order-id="' . $orderId . '" style="width: 43px;"/>';
                $remainHtml = '<a href="javascript:void(0);" class="partial-anchor-remain-' . '0' . '"><span class="remain-qty">' . $itemQuantity . '</span></a>';
                echo '<td class="partital-td-item"><span class="text-span">' . $qtyHtml . ' <i class="fa fa-truck fa-sm" aria-hidden="true"></i></span> <input type="button" class="btn btn-success btn-quanity-update" id="update-quantity-' . $itemId . '" value="Update Quantity"></td>';
                echo '<td class="partial-status-td" width="1%">' . $tdHtml . '</td>';
                echo '<td class="remain-status-td" width="1%">' . '0' . '</td>';

            } else {
                if ($orderId) {
                    if (!empty($shipped)) {
                        $key = array_search($itemId, array_column($shipped, 'item_id'));
                        prepareHtmlForUpdateQuantity($shipped, $key, $itemQuantity, $orderId, $itemId, $qtyHtml, $tdHtml, $remainHtml);
                    }
                    echo '<td class="partital-td-item"><span class="text-span">' . $qtyHtml . ' <i class="fa fa-truck fa-sm" aria-hidden="true"></i></span> <input type="button" class="btn btn-success btn-quanity-update" id="update-quantity-' . $itemId . '" value="Update Quantity"></td>';
                    echo '<td class="partial-status-td" width="1%">' . $tdHtml . '</td>';
                    echo '<td class="remain-status-td" width="1%">' . $remainHtml . '</td>';
                }
            }
            // if ($orderId) {
            // }
        } else {
            echo '<td></td>';
            echo '<td></td>';
            echo '<td></td>';
        }
    }
}

add_action('wp_ajax_order_set_shipped', 'orderSetShipped');

/**
 *
 * @return object
 */
function orderSetShipped(): object
{
    $orderId = isset($_POST['order_id']) ? $_POST['order_id'] : 0;
    $itemId = $_POST['item_id'];
    $qty = $_POST['qty'];
    $shipQty = $_POST['ship_quantity'];
    $productId = $_POST['productId'];
    $flagStatus = $_POST['flagStatus'];
    $order = new WC_Order($orderId);
    $items = $order->get_items();
    $weight = getWeightByProductId($productId);

    $shipmentArrs = get_post_meta($orderId, '_my_parcel_order_shipment', true);
    $shipmentArrs = (!empty($shipmentArrs)) ? json_decode($shipmentArrs, true) : array();

    $itemIdArr = (!empty($shipmentArrs)) ? array_column($shipmentArrs, 'item_id') : array();

    $shipmentNewArr = array();
    $shipmentNewAr = array();
    $totalShipQty = 0;
    if (!empty($shipmentArrs)) {
        if (!empty($itemIdArr) && !in_array($itemId, $itemIdArr)) {
            $totalShipQty = $shipQty;
            $remainQty = $qty - $totalShipQty;
            $shipmentNewAr = setOrderShipment($orderId, $itemId, $shipQty, $totalShipQty, $qty, "shipped", $weight, $remainQty, $flagStatus);
            $shipmentArrs[] = $shipmentNewAr;
        } else {
            foreach ($shipmentArrs as $key => $shipmentArr) {
                if ($itemId == $shipmentArr['item_id']) {
                    $totalShipQty = (int)$shipQty + (int)$shipmentArr['total_shipped'];
                    $remainQty = $qty - $totalShipQty;
                    $shipmentNewAr = setOrderShipment($orderId, $itemId, $shipQty, $totalShipQty, $qty, "shipped", $weight, $remainQty, $flagStatus);
                    $shipmentArrs[$key] = $shipmentNewAr;
                }
            }
        }
        update_post_meta($orderId, '_my_parcel_order_shipment', json_encode($shipmentArrs));
    } else {
        $totalShipQty = $shipQty;
        $remainQty = $qty - $totalShipQty;
        $shipmentNewAr = setOrderShipment($orderId, $itemId, $shipQty, $totalShipQty, $qty, "shipped", $weight, $remainQty, $flagStatus);
        $shipmentNewArr[] = $shipmentNewAr;
        update_post_meta($orderId, '_my_parcel_order_shipment', json_encode($shipmentNewArr));
    }
    echo json_encode(array('order_id' => $orderId, 'item_id' => $itemId, 'shipped' => $totalShipQty, 'qty' => $qty, 'weight' => $weight, 'remain_qty' => $remainQty, 'flagStatus' => $flagStatus));
    exit;
}

add_action('woocommerce_order_item_meta_end', 'orderItemShowPartialShipmentLabel', 999, 4);

/**
 * @param integer $itemId
 * @param object $item
 * @param object $order
 *
 * @return void
 */
function orderItemShowPartialShipmentLabel($itemId, $item, $order): void
{
    if (isMyParcelOrder($order->get_id())) {
        $qty = $item->get_quantity();
        $statusHtml = '<a href="javascript:void(0);" class="partial-anchor-top" title="Not Shipped">';
        $statusHtml .= '<span class="ship-status not-shipped-color" title="Not Shipped">Not Shipped- ' . $qty . '</span>';
        $statusHtml .= '</a>';
        $orderId = $order->get_id();
        $shipped = get_post_meta($orderId, '_my_parcel_order_shipment', true);

        if (!empty($shipped) || strlen($shipped) != 0) {
            $shipped = (!empty($shipped)) ? json_decode($shipped, true) : '';
            $key = array_search($itemId, array_column($shipped, 'item_id'));
            if (is_int($key)) {
                $ele = isset($shipped[$key]) ? $shipped[$key] : array();
                if (isset($ele['shipped']) && isset($ele['type']) && 'shipped' == $ele['type']) {
                    if ($ele['shipped'] == $qty) {
                        $statusHtml = '<a href="javascript:void(0);" class="partial-anchor-top" title="Shipped : ' . $ele['shipped'] . '/' . $qty . '">';
                        $statusHtml .= '<span class="ship-status new-shipped-color">Updated Shipping Qty ' . $shipped[$key]['shipped'] . '</span>';
                        $statusHtml .= '</a>';
                    } elseif (isset($ele['shipped']) && $ele['shipped'] > 0) {
                        $statusHtml = '<a href="javascript:void(0);" class="partial-anchor-top" title="Partially Shipped: ' . $ele['shipped'] . '/' . $qty . '">';
                        $statusHtml .= '<span class="ship-status partial-shipped-color">Partially Shipped - ' . $shipped[$key]['shipped'] . '</span>';
                        $statusHtml .= '</a>';
                    }
                }
            }
            echo $statusHtml;
        }
    }
}


