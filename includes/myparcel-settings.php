<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Register our scripts and make sure they are only injected when viewing our settings page.
 */
function settingPageJsCss(): void
{
    if (get_current_screen()->id === 'settings_page_myparcelcom_settings') {
        $assetsPath = plugins_url('', __FILE__) . '/../assets';
        wp_enqueue_script('myparcelcom_validation_js', $assetsPath . '/admin/js/jquery.validate.js');
        wp_enqueue_script('myparcelcom_setting_page_js', $assetsPath . '/admin/js/setting-page.js');
    }
}

add_action('admin_enqueue_scripts', 'settingPageJsCss', 999);

/**
 * Register options to have default values upon installation and be presented on the generic /wp-admin/options.php page.
 */
function registerSettings(): void
{
    add_option('client_key', '');
    add_option('client_secret_key', '');
    add_option('act_test_mode', '0');
    add_option(MYPARCEL_SHOP_ID, '');

    register_setting('myparcelcom', 'client_key');
    register_setting('myparcelcom', 'client_secret_key');
    register_setting('myparcelcom', 'act_test_mode');
    register_setting('myparcelcom', MYPARCEL_SHOP_ID);
}

add_action('admin_init', 'registerSettings');

/**
 * Retrieve a list of shops using the passed credentials (which might differ from the saved config in the database).
 */
function getShopsForClient()
{
    $shopData = [];

    try {
        $api = MyParcelApi::createSingletonFromConfig([
            'client_key'        => $_POST['client_key'],
            'client_secret_key' => $_POST['client_secret_key'],
            'act_test_mode'     => $_POST['act_test_mode'],
        ]);
        $shops = $api->getShops()->limit(100)->get();
        usort($shops, function ($a, $b) {
            return strcmp(strtolower($a->getName()), strtolower($b->getName()));
        });
        foreach ($shops as $shop) {
            $shopData[] = [
                'id'   => $shop->getId(),
                'name' => $shop->getName(),
            ];
        }
    } catch (Exception $e) {}

    echo json_encode($shopData);
    exit;
}

add_action('wp_ajax_myparcelcom_get_shops_for_client', 'getShopsForClient');

/**
 * After the "myparcel_shopid" option has been changed, we need to (re-)register the webhook to receive status updates.
 */
add_action(
    'update_option_myparcel_shopid',
    function ($old_value, $new_value) {
        if ($old_value !== $new_value) {
            registerMyParcelWebHook();
        }
    },
    10,
    2,
);

/**
 * Register the webhook to receive status updates.
 */
function registerMyParcelWebHook()
{
    $api = MyParcelApi::createSingletonFromConfig();

    try {
        $api->doRequest('/hooks/' . get_option(MYPARCEL_WEBHOOK_ID), 'delete');
    } catch (Throwable) {
        // Assume the hook could not be found because it has already been deleted.
    }

    $secret = md5(uniqid((string) rand(), true));
    $body = [
        'data' => [
            'type'          => 'hooks',
            'attributes'    => [
                'name'    => 'WooCommerce shipment status update',
                'order'   => 100,
                'active'  => true,
                'trigger' => [
                    'resource_type'   => 'shipment-statuses',
                    'resource_action' => 'create',
                ],
                'action'  => [
                    'action_type' => 'send-resource',
                    'values'      => [
                        [
                            'url'      => rest_url('/myparcelcom/webhook'),
                            'secret'   => $secret,
                            'includes' => [
                                'status',
                                'shipment',
                            ],
                        ],
                    ],
                ],
            ],
            'relationships' => [
                'owner' => [
                    'data' => [
                        'type' => 'shops',
                        'id'   => get_option(MYPARCEL_SHOP_ID),
                    ],
                ],
            ],
        ],
    ];

    try {
        $response = $api->doRequest('/hooks', 'post', $body);
        $responseJson = json_decode((string) $response->getBody(), true);

        update_option(MYPARCEL_WEBHOOK_ID, $responseJson['data']['id']);
        update_option(MYPARCEL_WEBHOOK_SECRET, $secret);
    } catch (Exception) {}
}

/**
 * Inject a menu item in the "Settings" tab of the WordPress admin.
 */
function addSettingMenu()
{
    add_options_page(
        'MyParcel.com Settings',
        'MyParcel.com',
        'manage_options',
        'myparcelcom_settings',
        'prepareHtmlForSettingPage',
    );
}

add_action('admin_menu', 'addSettingMenu');

/**
 * Output the HTML content of the settings page.
 */
function prepareHtmlForSettingPage()
{
    $pluginData = get_plugin_data(plugin_dir_path(__FILE__) . '../woocommerce-connect-myparcel.php', false, false);
    ?>
  <div class="wrap">
    <h1><?php echo $pluginData['Name']; ?> Settings</h1>
    <p><?php echo $pluginData['Description']; ?></p>
    <table class="form-table">
      <tr>
        <th scope="row">Current version</th>
        <td><?php echo $pluginData['Version']; ?></td>
      </tr>
      <tr>
        <th scope="row">Support</th>
        <td>
          <a href="<?php echo $pluginData['PluginURI']; ?>" target="_blank"><?php echo $pluginData['PluginURI']; ?></a>
        </td>
      </tr>
    </table>
    <form method="post" action="options.php" id="myparcelcom-settings-form">
      <?php settings_fields('myparcelcom'); ?>
      <table class="form-table">
        <tbody>
        <tr>
          <th scope="row"><label for="act_test_mode">Activate testmode</label></th>
          <td>
            <fieldset>
              <input
                type="checkbox"
                id="act_test_mode"
                name="act_test_mode"
                value="1"
                <?php checked(1, (int) get_option('act_test_mode')); ?>
              >
            </fieldset>
            <p class="description">
              If enabled, the plugin wil communicate with the MyParcel.com sandbox.<br>
              Make sure to use the client ID and secret from the correct environment (production / sandbox).
            </p>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="client_key">Client ID *</label></th>
          <td>
            <input
              type="text"
              id="client_key"
              class="regular-text"
              name="client_key"
              value="<?php echo get_option('client_key'); ?>"
            />
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="client_secret_key">Client secret *</label>
          </th>
          <td>
            <input
              type="password"
              id="client_secret_key"
              class="regular-text"
              name="client_secret_key"
              value="<?php echo get_option('client_secret_key'); ?>"
            />
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="myparcel_shopid">Default shop *</label></th>
          <td>
            <select class="regular-text" id="myparcel_shopid" name="myparcel_shopid">
              <option value="">Please enter your client ID and secret</option>
            </select>
            <script>const initialShop = '<?php echo get_option(MYPARCEL_SHOP_ID); ?>'</script>
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
