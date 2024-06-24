<?php

declare(strict_types=1);

use MyParcelCom\ApiSdk\LabelCombiner;
use MyParcelCom\ApiSdk\LabelCombinerInterface;
use MyParcelCom\ApiSdk\Resources\File;
use MyParcelCom\ApiSdk\Resources\ShipmentItem;
use MyParcelCom\ApiSdk\Resources\Shop;

/**
 * @param $orderId
 * @return WC_Order_Item[]
 */
function getOrderItems($orderId)
{
    $order = wc_get_order($orderId);

    return $order->get_items();
}

/**
 * @param int $postId
 * @return float|int
 */
function getTotalWeightByPostID($postId)
{
    $items = getOrderItems($postId);
    $totalWeight = 0;

    foreach ($items as $item) {
        if ($item->get_product()->get_weight()) {
            $totalWeight += floatval($item->get_product()->get_weight()) * $item->get_quantity();
        }
    }

    return $totalWeight;
}

/**
 * @return ShipmentItem[]
 */
function getShipmentItems($orderId, $currency, $originCountryCode)
{
    $items = getOrderItems($orderId);
    $shipmentItems = [];

    foreach ($items as $item) {
        $product = $item->get_product();
        $sku = $product->get_sku() ?: (string) $product->get_id();
        $imageUrl = $product->get_image_id() ? wp_get_attachment_image_url($product->get_image_id(), 'medium') : null;
        $itemValue = (int) round(floatval($product->get_price()) * 100);
        $itemWeight = $product->get_weight() ? (int) round(floatval($product->get_weight()) * 1000) : null;
        $hsCode = get_post_meta($product->get_id(), 'myparcel_hs_code', true) ?: null;
        $productCountry = get_post_meta($product->get_id(), 'myparcel_product_country', true);

        $shipmentItems[] = (new ShipmentItem())
            ->setSku($sku)
            ->setDescription($product->get_name())
            ->setImageUrl($imageUrl)
            ->setItemValue($itemValue)
            ->setCurrency($currency)
            ->setQuantity($item->get_quantity())
            ->setHsCode($hsCode)
            ->setItemWeight($itemWeight)
            ->setOriginCountryCode($productCountry ?: $originCountryCode);
    }

    return $shipmentItems;
}

/**
 * @param int   $postId
 * @param mixed $shipKey
 */
function updateShipmentKey($postId, $shipKey = null)
{
    if (!empty($shipKey)) {
        update_post_meta($postId, GET_META_MYPARCEL_SHIPMENT_KEY, $shipKey); //Update the shipment key on database
    } else {
        add_post_meta($postId, GET_META_MYPARCEL_SHIPMENT_KEY, uniqid()); //Update the shipment key on database
    }
}

function getRegisteredShopId(): string
{
    return get_option('myparcel_shopid');
}

/**
 * @param $post_id
 * @return array|false|string|void
 */
function getShipmentCurrentStatus($post_id)
{
    $shipmentData     = [];
    $getOrderMetaData = get_post_meta($post_id, GET_META_SHIPMENT_TRACKING_KEY, true);
    if (!$getOrderMetaData) {
        return;
    }
    $getOrderMeta     = json_decode($getOrderMetaData);
    if (!$getOrderMeta) {
        return;
    }
    $api = MyParcelApi::createSingletonFromConfig();
    if (!empty($getOrderMeta->trackingKey)) {
        try {
            $shipment                    = $api->getShipment($getOrderMeta->trackingKey);
            $shipmentStatus              = $shipment->getShipmentStatus();
            $status                      = $shipmentStatus->getStatus();
            $shipmentData['name']        = $status->getName();
            $shipmentData['description'] = $status->getDescription();
            $shipmentData                = json_encode($shipmentData);
        } catch (\Exception $e) {
        }

        return $shipmentData;
    }
}

add_action('manage_posts_extra_tablenav', 'admin_order_list_top_bar_button', 20, 1);
function admin_order_list_top_bar_button($which)
{
    global $typenow;
    if ('shop_order' === $typenow && 'top' === $which) {
        ?>
      <div id="labelModal" tabindex="-1" aria-hidden="true" style="display:none">
        <div class="modal-content">
          <div class="modal-header">
            <div class="row">
              <div class="col-lg-6" id="printer-orientation">
                <label class="container">
                  <input type="radio" name="selectorientation" class="toggle" value="2" checked="checked"> A6 - label printer
                </label>
                <br>
                <label class="container">
                  <input type="radio" name="selectorientation" class="toggle" value="1"> A4 - default printer
                </label>
              </div>
            </div>
          </div>
          <div class="modal-body">
            <div class="row cntnr" id="orientation1" style="display:none">
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
              <div class="break"></div>
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
            <div class="row cntnr" id="orientation2">
            </div>
          </div>
          <div class="modal-footer">
            <div id="loadingmessage" style="display:none">
                <?php $loader = plugins_url('', __FILE__) . '/../../assets/images/ajax-loader.gif'; ?>
              <img class="img-responsive center-block" src="<?php echo $loader; ?>"/>
            </div>
            <div class="alert" style="display: none;">
              <p>Label is not available - please export the order first.</p>
            </div>
            <button type="button" class="button" id="download-pdf">Download</button>
          </div>
        </div>
      </div>
        <?php
    }
}

add_action('wp_ajax_myparcelcom_download_pdf', 'downloadPdf');
function downloadPdf()
{
    define('LOCATION_TOP', 1);
    define('LOCATION_BOTTOM', 2);
    define('LOCATION_RIGHT', 4);
    define('LOCATION_LEFT', 8);
    define('LOCATION_TOP_LEFT', LOCATION_TOP | LOCATION_LEFT);
    define('LOCATION_TOP_RIGHT', LOCATION_TOP | LOCATION_RIGHT);
    define('LOCATION_BOTTOM_LEFT', LOCATION_BOTTOM | LOCATION_LEFT);
    define('LOCATION_BOTTOM_RIGHT', LOCATION_BOTTOM | LOCATION_RIGHT);

    $selectOrientation = intval($_POST['selectOrientation']);
    $orderIds          = $_POST['orderIds'];
    $labelPrinter      = intval($_POST['labelPrinter']);
    $api               = MyParcelApi::createSingletonFromConfig();
    $shipments         = [];

    foreach ($orderIds as $orderId) {
        $getShipmentKey = get_post_meta($orderId, GET_META_SHIPMENT_TRACKING_KEY, true);
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
    if (empty($files)) {
        wp_die('Failed');
    }
    if (count($files) === 1) {
        echo $files[0]->getBase64Data();
    } else {
        $combinedFile = (new LabelCombiner())->combineLabels(
            $files,
            labelPrinter($labelPrinter),
            getOrientation($selectOrientation)
        );
        echo $combinedFile->getBase64Data();
    }

    wp_die(); // this is required to terminate immediately and return a proper response
}

/**
 * @param $selectOrientation
 * @return int
 */
function getOrientation($selectOrientation)
{
    switch ($selectOrientation) {
        case 1:
            return LOCATION_TOP_LEFT;
        case 2:
            return LOCATION_TOP_RIGHT;
        case 3:
            return LOCATION_BOTTOM_LEFT;
        default:
            return LOCATION_BOTTOM_RIGHT;
    }
}

/**
 * @param $labelPrinter
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
 * @return Shop
 */
function getSelectedShop()
{
    $api = MyParcelApi::createSingletonFromConfig();
    $shops = $api->getShops()->limit(100)->get();
    foreach ($shops as $shop) {
        if ($shop->getId() == getRegisteredShopId()) {
            return $shop;
        }
    }
    return $shops[0];
}
