<?php

declare(strict_types=1);

use MyParcelCom\ApiSdk\LabelCombiner;
use MyParcelCom\ApiSdk\LabelCombinerInterface;
use MyParcelCom\ApiSdk\Resources\File;
use MyParcelCom\ApiSdk\Resources\ShipmentItem;
use MyParcelCom\ApiSdk\Resources\Shop;

/**
 * @param $orderId
 * @return array
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

function getShipmentItems($orderId, $currency, $originCountryCode)
{
    $items = getOrderItems($orderId);
    $shipmentItems = [];

    foreach ($items as $item) {
        $product = $item->get_product();
        $sku = $product->get_sku() ?: $product->get_id();
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
 * @return array
 */
function getMyParcelShopList()
{
    $shops = [];
    if (get_option('client_key') && get_option('client_secret_key')) {
        $getAuth = new MyParcelApi();
        $api = $getAuth->apiAuthentication();
        if ($api) {
            $shops = $api->getShops()->limit(100)->get();
            usort($shops, function ($a, $b) {
                return strcmp(strtolower($a->getName()), strtolower($b->getName()));
            });
        }
    }

    return $shops;
}

/**
 * @desc setting page html
 */
function prepareHtmlForSettingPage()
{
    ?>
  <div class="wrap">
    <h1><?php echo MYPARCEL_API_SETTING_TEXT; ?></h1>
    <table class="form-table">
      <tr valign="top">
        <th scope="row"><label>Current version</label></th>
        <td><?php echo MYPARCEL_PLUGIN_VERSION; ?></td>
      </tr>
      <tr valign="top">
        <th scope="row"><label>MyParcel.com support</label></th>
        <td>
          <a href="<?php echo MYPARCEL_SUPPORT_TEXT_AND_URL; ?>" target="_blank"><?php echo MYPARCEL_SUPPORT_TEXT_AND_URL; ?></a>
        </td>
      </tr>
    </table>
    <form method="post" action="options.php" id="myparcelcom-settings-form">
        <?php settings_fields('myplugin_options_group'); ?>
      <table class="form-table">
        <tbody>
          <tr>
            <th scope="row">Activate testmode</th>
            <td>
              <fieldset>
                <legend class="screen-reader-text"><span></span></legend>
                <label for="users_can_register">
                  <input type="checkbox" id="act_test_mode" name="act_test_mode"
                         value="1" <?php checked(1, (int) get_option('act_test_mode')); ?>>
                </label>
              </fieldset>
              <p class="description">
                If enabled, the plugin wil communicate with the MyParcel.com sandbox.<br>
                Make sure to use the client ID and secret from the correct environment (production / sandbox).
              </p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><label for="client_key">Client ID *</label></th>
            <td>
              <input type="text" id="client_key" class="regular-text" name="client_key"
                     value="<?php echo get_option('client_key'); ?>"/>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><label for="client_secret_key">Client secret *</label>
            </th>
            <td>
              <input type="password" id="client_secret_key" class="regular-text" name="client_secret_key"
                     value="<?php echo get_option('client_secret_key'); ?>"/>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><label for="myparcel_shopid">Default shop</label></th>
            <td>
              <select class="regular-text" id="myparcel_shopid" name="myparcel_shopid">
                <option value="">Please enter your client ID and secret</option>
              </select>
              <script>const initialShop = '<?php echo get_option('myparcel_shopid'); ?>'</script>
              <p class="description">Please select the related MyParcel.com shop for this WordPress shop.</p>
            </td>
          </tr>
        </tbody>
      </table>
        <?php submit_button('Save changes'); ?>
    </form>
  </div>
    <?php
}

/**
 * @param        $column
 * @param int    $orderId
 * @param object $the_order
 */
function renderOrderColumnContent($column, $orderId, $the_order)
{
    switch ($column) {
        case 'shipped_label' :
            if (!$orderId) {
                return;
            }
            getShipmentFiles($orderId);
            break;

        case 'get_shipment_status' :
            if (!$orderId) {
                return;
            }
            $shipmentData = getShipmentCurrentStatus($orderId);
            if (!empty($shipmentData)) {
                $shipmentValues = json_decode($shipmentData);
                ?>
              <div class="order-status status-completed" title="<?php echo $shipmentValues->description; ?>">
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
function getAuthTokenAndRegisterWebhook()
{
    $clientKey = get_option('client_key');
    $clientSecretKey = get_option('client_secret_key');
    if ($clientKey && $clientSecretKey) {
        $dataString = json_encode([
            'grant_type'    => 'client_credentials',
            'client_id'     => $clientKey,
            'client_secret' => $clientSecretKey,
            'scope'         => '*',
        ]);
        $result = createCurlRequest(MYPARCEL_WEBHOOK_ACCESS_TOKEN, $dataString);
        if (!empty($result)) {
            $getToken = json_decode($result);
            if (isset($getToken->access_token)) {
                registerMyParcelWebHook($getToken->access_token);
            }
        }
    }
}

/**
 * @param $accessToken
 */
function registerMyParcelWebHook($accessToken)
{
    $shop            = getSelectedShop();
    $webHookUrl      = plugins_url('', dirname(__FILE__)).'/webhook.php';
    $webHookName     = getRegisteredShopId().'-'.$shop->getName();
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
    $result          = createCurlRequest($url, $dataString, $authorization);
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
function getRegisteredShopId(): string
{
    return get_option('myparcel_shopid');
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
    }
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
            $label    = "myparcelcom-".date('Ymdhis')."-label.pdf";
            if (!empty($labels)) {
                foreach ($labels as $label) {
                    $label = $label->getBase64Data('application/pdf');
                    echo '<p><a class="button download-label" download="'.$label.'" href="data:application/octet-stream;base64,'.$label.'">download</a></p>';
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
            $shipment->setRegisterAt(0);
            $api->updateShipment($shipment);
        }
    }
}

/**
 * @param      $url
 * @param      $dataString
 * @param null $authorization
 * @return bool|string
 */
function createCurlRequest($url, $dataString, $authorization = null)
{
    $httpHeader = array_filter([
        'Content-Type: application/json',
        $authorization,
    ]);

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
 * @return array|false|string|void
 */
function getShipmentCurrentStatus($post_id)
{
    $shipmentData     = [];
    $getOrderMetaData = get_post_meta($post_id, GET_META_SHIPMENT_TRACKING_KEY, true);
    $getOrderMeta     = json_decode($getOrderMetaData);
    if (!$getOrderMeta) {
        return;
    }
    $getAuth  = new MyParcelApi();
    $api      = $getAuth->apiAuthentication();
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
    $getAuth           = new MyParcelApi();
    $api               = $getAuth->apiAuthentication();
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
    $getAuth = new MyParcelApi();
    $api = $getAuth->apiAuthentication();
    $shops = $api->getShops()->limit(100)->get();
    foreach ($shops as $shop) {
        if ($shop->getId() == getRegisteredShopId()) {
            return $shop;
        }
    }
    return $shops[0];
}
