<?php

declare(strict_types=1);

/**
 * Plugin Name: MyParcel.com
 * Plugin URI: https://help.myparcel.com/home/integrations-1#Integrations-WooCommerce
 * Description: This plugin enables you to export WooCommerce orders to MyParcel.com.
 * Version: 3.0.5
 * Author: MyParcel.com
 * Author URI: https://www.myparcel.com
 * Requires at least:
 * Tested up to:
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 * WC requires at least: 7.1
 * WC tested up to: 8.0
 * WC HPOS compatibility: yes
 *
 * @package WooCommerceConnectMyParcel
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once dirname(__FILE__) . '/vendor/autoload.php';
require_once dirname(__FILE__) . '/includes/myparcel-api.php';
require_once dirname(__FILE__) . '/includes/common/myparcel-constant.php';
require_once dirname(__FILE__) . '/includes/common/common-functions.php';
require_once dirname(__FILE__) . '/includes/myparcel-hooks.php';
require_once dirname(__FILE__) . '/includes/myparcel-shipment-hooks.php';
require_once dirname(__FILE__) . '/includes/myparcel-settings.php';
