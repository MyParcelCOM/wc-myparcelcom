<?php //declare(strict_types=1);
/**
 * Plugin Name: MyParcel.com
 * Plugin URI:  https://www.myparcel.com
 * Description: This plugin enables you to choose MyParcel.com shipping methods on WooCommerce.
 * Version: 1.2.1
 * Author: MyParcel.com
 * Requires at least:
 * Tested up to:
 *
 * @package WooCommerceConnectMyParcel
 */
/**
 *  checking if direct access of the file.
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
/**
 * Check if WooCommerce is active
 */
$errorMessage = '';
$ver = (float)phpversion();
$errorVersionMessage ='<div class="notice notice-error is-dismissible">
            <p>'.MY_PARCEL_PLUGIN_NAME.' Supports php 7.1 and higher vesion .</p>
        </div>';

if (!(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))))) {
    global $pagenow;
    $errorMessage .= '<div class="notice notice-error is-dismissible">
            <p>Please install <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin for '.MY_PARCEL_PLUGIN_NAME.'.</p>
        </div>';
    if ('plugins.php' === $pagenow) {
        add_action(
            'admin_notices',
            function () use ($errorMessage) {
                $return = $errorMessage;
                echo $return;
            }
        );

        if ($ver < 7.0) {
            add_action(
                'admin_notices',
                function () use ($errorVersionMessage) {
                    $return = $errorVersionMessage;
                    echo $return;
                }
            );
        }

    }
}
/**
 * Check if WooCommerce is active
 */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    if ($ver < 7.0) {
        add_action(
            'admin_notices',
            function () use ($errorVersionMessage) {
                $return = $errorVersionMessage;
                echo $return;
            }
        );
    } else {
        if (!class_exists('MyParcel_API')) {
            include_once dirname(__FILE__).'/includes/vendor/autoload.php';
            include_once dirname(__FILE__).'/includes/myparcel-api.php';
        }
        include_once dirname(__FILE__).'/includes/common/myparcel-constant.php';
        include_once dirname(__FILE__).'/includes/common/common-functions.php';
        include_once dirname(__FILE__).'/includes/myparcel-hooks.php';
        include_once dirname(__FILE__).'/includes/myparcel-shipment-hooks.php';
        include_once dirname(__FILE__).'/includes/myparcel-settings.php';
    }
}
