<?php

declare(strict_types=1);

/**
 * Plugin Name: MyParcel.com
 * Plugin URI: https://myparcel-com.odoo.com/en/woocommerce
 * Description: This plugin enables you to export WooCommerce orders to MyParcel.com.
 * Version: 2.1.5
 * Author: MyParcel.com
 * Author URI: https://www.myparcel.com
 * Requires at least:
 * Tested up to:
 *
 * @package WooCommerceConnectMyParcel
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!defined('MY_PARCEL_PLUGIN')) {
    define('MY_PARCEL_PLUGIN', __FILE__);
}
if (!defined('MY_PARCEL_PLUGIN_NAME')) {
    define('MY_PARCEL_PLUGIN_NAME', 'MyParcel.com');
}

$phpVersion = (float) phpversion();
$phpVersionMessage = '<p>' . MY_PARCEL_PLUGIN_NAME . ' plugin needs PHP 7.1 or higher.</p>';
$wooVersionMessage = '<p>Please install <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a> to use the ' . MY_PARCEL_PLUGIN_NAME . ' plugin.</p>';

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    global $pagenow;
    if ('plugins.php' === $pagenow) {
        add_action('admin_notices', function () use ($wooVersionMessage) {
            echo '<div class="notice notice-error is-dismissible">' . $wooVersionMessage . '</div>';
        });

        if ($phpVersion < 7.0) {
            add_action('admin_notices', function () use ($phpVersionMessage) {
                echo '<div class="notice notice-error is-dismissible">' . $phpVersionMessage . '</div>';
            });
        }
    }
} else {
    if ($phpVersion < 7.0) {
        add_action('admin_notices', function () use ($phpVersionMessage) {
            echo '<div class="notice notice-error is-dismissible">' . $phpVersionMessage . '</div>';
        });
    } else {
        include_once dirname(__FILE__) . '/includes/vendor/autoload.php';
        include_once dirname(__FILE__) . '/includes/myparcel-api.php';
        include_once dirname(__FILE__) . '/includes/common/myparcel-constant.php';
        include_once dirname(__FILE__) . '/includes/common/common-functions.php';
        include_once dirname(__FILE__) . '/includes/myparcel-hooks.php';
        include_once dirname(__FILE__) . '/includes/myparcel-shipment-hooks.php';
        include_once dirname(__FILE__) . '/includes/myparcel-settings.php';
    }
}
