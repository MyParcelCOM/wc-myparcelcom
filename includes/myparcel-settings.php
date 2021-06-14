<?php

declare(strict_types=1);

/**
 * @return void
 */
function settingPageJsCss()
{
    wp_enqueue_script(
        'validation',
        plugins_url('', __FILE__).'/../assets/admin/js/jquery.validate.js',
        '',
        '',
        false
    );

    wp_register_script(
        'setting_page_js',
        plugins_url('', __FILE__).'/../assets/admin/js/setting-page.js',
        '',
        '',
        true
    );
    wp_enqueue_script('setting_page_js');
}

add_action('admin_enqueue_scripts', 'settingPageJsCss', 999);

/**
 * Register setting in setting panel
 *
 * @return void
 */
function registerSettings()
{
    add_option('client_key', '');
    add_option('client_secret_key', '');
    add_option('act_test_mode', '0');
    add_option('myparcel_shopid', '');

    register_setting('myplugin_options_group', 'client_key');
    register_setting('myplugin_options_group', 'client_secret_key');
    register_setting('myplugin_options_group', 'act_test_mode');
    register_setting('myplugin_options_group', 'myparcel_shopid');
}

add_action('admin_init', 'registerSettings');

add_action('wp_ajax_myparcelcom_get_shops_for_client', 'getShopsForClient');
function getShopsForClient()
{
    $getAuth = new MyParcelApi();
    $api = $getAuth->doAuthentication($_POST['client_key'], $_POST['client_secret_key'], $_POST['act_test_mode']);

    $shopData = [];
    if ($api) {
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
    }

    echo json_encode($shopData);
    exit;
}

/**
 * Action called to register webhook token
 */
add_action(
    'update_option_myparcel_shopid',
    function ($old_value, $new_value) {
        if ($old_value != $new_value) {
            if (function_exists('getAuthTokenAndRegisterWebhook')) {
                getAuthTokenAndRegisterWebhook();
            }
        }
    },
    10,
    2
);

function addSettingMenu()
{
    add_options_page(
        'API Setting',
        MYPARCEL_API_SETTING_TEXT,
        'manage_options',
        'myparcelcom_settings',
        'prepareHtmlForSettingPage'
    );
}

add_action('admin_menu', 'addSettingMenu');
