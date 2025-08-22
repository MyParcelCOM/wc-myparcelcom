<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use MyParcelCom\ApiSdk\LabelCombiner;
use MyParcelCom\ApiSdk\LabelCombinerInterface;
use MyParcelCom\ApiSdk\Resources\File;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\ShipmentItem;
use MyParcelCom\ApiSdk\Resources\Shop;

/**
 * @return WC_Order_Item[]
 */
function getOrderItemsByOrderId(int $orderId): array
{
    $order = wc_get_order($orderId);

    return $order->get_items();
}

/**
 * @param int $orderId
 * @return float
 */
function getTotalWeightByOrderID(int $orderId): float
{
    $items = getOrderItemsByOrderId($orderId);
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
function getShipmentItems($orderId, $currency, $originCountryCode): array
{
    $order = wc_get_order($orderId);
    $items = getOrderItemsByOrderId($orderId);
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
 * Render the hidden label dialog on the order overview table. JavaScript monitoring the mass action will show this.
 */
function admin_order_list_top_bar_button(string $which): void
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
              <p>Something went wrong, please check your WordPress error log and contact support.</p>
            </div>
            <button type="button" class="button" id="download-pdf">Download</button>
          </div>
        </div>
      </div>
        <?php
    }
}

add_action('manage_posts_extra_tablenav', 'admin_order_list_top_bar_button', 20, 1);

/**
 * Handle the "print_label_shipment" action after the label dialog is shown to select the print position.
 */
function downloadPdf(): void
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
        $order = wc_get_order($orderId);
        $shipmentId =  $order->get_meta(MYPARCEL_SHIPMENT_ID);

        // If no shipment ID is found, we check the legacy meta, which is used by our v2.x plugin.
        if (empty($shipmentId)) {
            $legacyMeta = $order->get_meta(MYPARCEL_LEGACY_SHIPMENT_META);
            if (!empty($legacyMeta)) {
                $legacyData = json_decode($legacyMeta, true);
                $shipmentId = $legacyData[MYPARCEL_LEGACY_SHIPMENT_ID];
            }
        }

        if (!empty($shipmentId)) {
           try {
               $shipments[] = $api->getShipment($shipmentId);
           } catch (Exception) {
               wp_die("Error: no shipment found for order {$orderId} - shipment might be deleted. {$shipmentId}");
           }
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
        $message = empty($shipmentId) ? 'please export this order first,' : "shipment might be voided. {$shipmentId}";
        wp_die("Error: label is not available - {$message}");
    }
    if (count($files) === 1) {
        echo $files[0]->getBase64Data();
    } else {
        $pageSize = (!empty($labelPrinter) && ($labelPrinter === 1))
            ? LabelCombinerInterface::PAGE_SIZE_A4
            : LabelCombinerInterface::PAGE_SIZE_A6;
        $startLocation = match ($selectOrientation) {
            1       => LOCATION_TOP_LEFT,
            2       => LOCATION_TOP_RIGHT,
            3       => LOCATION_BOTTOM_LEFT,
            default => LOCATION_BOTTOM_RIGHT,
        };
        echo (new LabelCombiner())
            ->combineLabels($files, $pageSize, $startLocation)
            ->getBase64Data();
    }

    wp_die(); // this is required to terminate immediately and return a proper response
}

add_action('wp_ajax_myparcelcom_download_pdf', 'downloadPdf');

/**
 * @return Shop
 */
function getSelectedShop()
{
    $api = MyParcelApi::createSingletonFromConfig();

    return $api->getResourceById(ResourceInterface::TYPE_SHOP, get_option(MYPARCEL_SHOP_ID));
}
