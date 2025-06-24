<?php

declare(strict_types=1);

/**
 * Plugin Name: MyParcel.com
 * Plugin URI: https://help.myparcel.com/home/integrations-1#Integrations-WooCommerce
 * Description: This plugin enables you to export WooCommerce orders to MyParcel.com.
 * Version: 3.1.0
 * Author: MyParcel.com
 * Author URI: https://www.myparcel.com
 * Requires at least:
 * Tested up to:
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 *
 * @package WooCommerceConnectMyParcel
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Declare HPOS compatibility.
add_action('before_woocommerce_init', function() {
    if (class_exists(FeaturesUtil::class)) {
        FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            __FILE__
        );
    }
});

require_once dirname(__FILE__) . '/vendor/autoload.php';
require_once dirname(__FILE__) . '/includes/myparcel-api.php';
require_once dirname(__FILE__) . '/includes/common/myparcel-constant.php';
require_once dirname(__FILE__) . '/includes/common/common-functions.php';
require_once dirname(__FILE__) . '/includes/myparcel-hooks.php';
require_once dirname(__FILE__) . '/includes/myparcel-shipment-hooks.php';
require_once dirname(__FILE__) . '/includes/myparcel-settings.php';
