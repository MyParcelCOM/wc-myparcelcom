<?php declare(strict_types=1);

/**
 * Plugin Name: MyParcel.com
 * Plugin URI:  https://www.myparcel.com
 * Description: This plugin enables you to choose MyParcel.com shipping methods on WooCommerce.
 * Version: 1.0
 * Author: Larkdesk
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
    define('MY_PARCEL_PLUGIN_NAME', 'MyParcel WooCommerce Connect');
}

/**
 * Check if WooCommerce is active
 */
$errorMessage = '';

if (!(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))))) {

    global $pagenow;
    $errorMessage.='<div class="notice notice-error is-dismissible">
            <p>Please install <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin for '. MY_PARCEL_PLUGIN_NAME .'.</p>
        </div>';

    if ('plugins.php' === $pagenow) {
        add_action(
            'admin_notices',
            function() use ($errorMessage) {
                $return = $errorMessage;
                echo $return;
            }
        );
    }
}

/**
 * Check if WooCommerce is active
 */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    add_action('woocommerce_shipping_init', 'myparcel_shipping_method');
    function myparcel_shipping_method()
    {
        if (!class_exists('MyParcel_API')) {
            include_once dirname( __FILE__ ) . '/includes/vendor/autoload.php';
            include_once dirname( __FILE__ ) . '/includes/MyParcel_API.php';
        }
        
    }

    include_once dirname( __FILE__ ) . '/includes/common/myparcel-constant.php';
    include_once dirname( __FILE__ ) . '/includes/common/common-functions.php';
    include_once dirname( __FILE__ ) . '/includes/myparcel-hooks.php';
    include_once dirname( __FILE__ ) . '/includes/myparcel-shipment-hooks.php';
    include_once dirname( __FILE__ ) . '/includes/myparcel-settings.php';
}