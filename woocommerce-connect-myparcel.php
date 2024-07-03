<?php

declare(strict_types=1);

/**
 * Plugin Name: MyParcel.com
 * Plugin URI: https://help.myparcel.com/home/integrations-1#Integrations-WooCommerce
 * Description: This plugin enables you to export WooCommerce orders to MyParcel.com.
 * Version: 3.0.0
 * Author: MyParcel.com
 * Author URI: https://www.myparcel.com
 * Requires at least:
 * Tested up to:
 * Requires PHP: 8.0
 *
 * @package WooCommerceConnectMyParcel
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

$wooVersionMessage = '<p>Please install <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a> to use the MyParcel.com plugin.</p>';

if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    global $pagenow;
    if ('plugins.php' === $pagenow) {
        add_action('admin_notices', function () use ($wooVersionMessage) {
            echo '<div class="notice notice-error is-dismissible">' . $wooVersionMessage . '</div>';
        });
    }
} else {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
    require_once dirname(__FILE__) . '/includes/myparcel-api.php';
    require_once dirname(__FILE__) . '/includes/common/myparcel-constant.php';
    require_once dirname(__FILE__) . '/includes/common/common-functions.php';
    require_once dirname(__FILE__) . '/includes/myparcel-hooks.php';
    require_once dirname(__FILE__) . '/includes/myparcel-shipment-hooks.php';
    require_once dirname(__FILE__) . '/includes/myparcel-settings.php';
}
