<?php

declare(strict_types=1);

use MyParcelCom\ApiSdk\MyParcelComApiInterface;

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
        wp_enqueue_script('myparcelcom_setting_page_js', $assetsPath . '/admin/js/setting-page.js?v=3.1.0');
    }
}

add_action('admin_enqueue_scripts', 'settingPageJsCss', 999);

/**
 * Register options to have default values upon installation and be presented on the generic /wp-admin/options.php page.
 */
function registerSettings(): void
{
    // Migrate old settings to the new prefixed values (we check for "act_test_mode" because that option was always set)
    if (get_option(MYPARCEL_TEST_MODE) === false && get_option(MYPARCEL_LEGACY_TEST_MODE) !== false) {
        update_option(MYPARCEL_CLIENT_ID, get_option(MYPARCEL_LEGACY_CLIENT_KEY));
        update_option(MYPARCEL_CLIENT_SECRET, get_option(MYPARCEL_LEGACY_CLIENT_SECRET));
        update_option(MYPARCEL_TEST_MODE, get_option(MYPARCEL_LEGACY_TEST_MODE));
        update_option(MYPARCEL_SHOP_ID, get_option(MYPARCEL_LEGACY_SHOP_ID));

        // NOTE: do not delete the legacy options, since customers with cache plugins experienced a crash after install.

        add_action('admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>MyParcel.com plugin settings have successfully been migrated.</p>';
            echo '</div>';
        });
    }

    // Try to register the webhook if no webhook_id is set at all (for shop owners who upgrade from v2.x to v3.x)
    if (get_option(MYPARCEL_WEBHOOK_ID) === false && get_option(MYPARCEL_SHOP_ID) !== false) {
        registerMyParcelWebHook();

        add_action('admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>MyParcel.com plugin webhook successfully set up.</p>';
            echo '</div>';
        });
    }

    register_setting('myparcelcom', MYPARCEL_CLIENT_ID);
    register_setting('myparcelcom', MYPARCEL_CLIENT_SECRET);
    register_setting('myparcelcom', MYPARCEL_TEST_MODE);
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
            MYPARCEL_CLIENT_ID     => $_POST[MYPARCEL_CLIENT_ID],
            MYPARCEL_CLIENT_SECRET => $_POST[MYPARCEL_CLIENT_SECRET],
            MYPARCEL_TEST_MODE     => $_POST[MYPARCEL_TEST_MODE],
        ]);
        $shops = $api->getResourcesFromUri(MyParcelComApiInterface::PATH_SHOPS);
        usort($shops, function ($a, $b) {
            return strcmp(strtolower($a->getName()), strtolower($b->getName()));
        });
        foreach ($shops as $shop) {
            $shopData[] = [
                'id'   => $shop->getId(),
                'name' => $shop->getName(),
            ];
        }
    } catch (Exception) {}

    echo json_encode($shopData);
    exit;
}

add_action('wp_ajax_myparcelcom_get_shops_for_client', 'getShopsForClient');

/**
 * After the "myparcelcom_shop_id" option has changed, we need to (re-)register the webhook to receive status updates.
 */
add_action(
    'update_option_myparcelcom_shop_id',
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
    } catch (Exception) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>MyParcel.com plugin webhook failed to set up.</p>';
            echo '</div>';
        });
    }
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
 * Add a "Settings" link to our plugin on the Plugins page, which leads to our "Settings" tab of the WordPress admin.
 */

function addSettingLink($actions)
{
    return array_merge([
        '<a href="' . admin_url( 'options-general.php?page=myparcelcom_settings' ) . '">Settings</a>',
    ], $actions);
}

add_filter('plugin_action_links_wc-myparcelcom/woocommerce-connect-myparcel.php', 'addSettingLink');

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
          <th scope="row"><label for="<?php echo MYPARCEL_TEST_MODE; ?>">Activate testmode</label></th>
          <td>
            <fieldset>
              <input
                type="checkbox"
                id="<?php echo MYPARCEL_TEST_MODE; ?>"
                name="<?php echo MYPARCEL_TEST_MODE; ?>"
                value="1"
                <?php checked(1, (int) get_option(MYPARCEL_TEST_MODE)); ?>
              >
            </fieldset>
            <p class="description">
              If enabled, the plugin wil communicate with the MyParcel.com sandbox.<br>
              Make sure to use the client ID and secret from the correct environment (production / sandbox).
            </p>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="<?php echo MYPARCEL_CLIENT_ID; ?>">Client ID *</label></th>
          <td>
            <input
              type="text"
              id="<?php echo MYPARCEL_CLIENT_ID; ?>"
              class="regular-text"
              name="<?php echo MYPARCEL_CLIENT_ID; ?>"
              value="<?php echo get_option(MYPARCEL_CLIENT_ID); ?>"
            />
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="<?php echo MYPARCEL_CLIENT_SECRET; ?>">Client secret *</label>
          </th>
          <td>
            <input
              type="password"
              id="<?php echo MYPARCEL_CLIENT_SECRET; ?>"
              class="regular-text"
              name="<?php echo MYPARCEL_CLIENT_SECRET; ?>"
              value="<?php echo get_option(MYPARCEL_CLIENT_SECRET); ?>"
            />
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="<?php echo MYPARCEL_SHOP_ID; ?>">Default shop *</label></th>
          <td>
            <select class="regular-text" id="<?php echo MYPARCEL_SHOP_ID; ?>" name="<?php echo MYPARCEL_SHOP_ID; ?>">
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
