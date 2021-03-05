<?php declare(strict_types=1);

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
    add_option('ship_exists', '0');
    add_option('act_test_mode', '0');
    add_option('myparcel_shopid', '');

    register_setting('myplugin_options_group', 'client_key');
    register_setting('myplugin_options_group', 'client_secret_key');
    register_setting('myplugin_options_group', 'ship_exists');
    register_setting('myplugin_options_group', 'act_test_mode');
    register_setting('myplugin_options_group', 'myparcel_shopid');
    register_setting('myplugin_options_group', 'checkValidation', 'validationCallBack');
}

add_action('admin_init', 'registerSettings');

/**
 *
 * @return bool
 */
function validationCallBack(): bool
{
    $error          = false;
    $clientKey      = get_option('client_key');
    $secretKey      = get_option('client_secret_key');
    $myparcelshopId = get_option('myparcel_shopid');
    if (empty($clientKey) || empty($secretKey)) {
        $error = true;
    }
    if ($error) {
        add_settings_error(
            'show_message',
            esc_attr('settings_updated'),
            __('Settings NOT saved. Please fill all the required fields.'),
            'error'
        );
        add_action('admin_notices', 'printErrors');
        updateOption();

        return false;
    } else {

        add_settings_error(
            'show_message',
            esc_attr('settings_updated'),
            __(MYPARCEL_SETTING_SAVE_TEXT),
            'updated'
        );
        add_action('admin_notices', 'printErrors');

        return true;
    }
}

/**
 *
 * Action called to register webhook token
 */
add_action(
    'update_option_myparcel_shopid',
    function ($old_value, $new_value) {
        if ($old_value != $new_value) {
            if (function_exists('getAuthToken')) {
                getAuthToken();
            }
        }
    },
    10,
    2
);

/**
 *
 * @return void
 */
function printErrors()
{
    settings_errors('show_message');
}

/**
 *
 * @return void
 */
function updateOption()
{
    update_option('client_key', '');
    update_option('client_secret_key', '');
    update_option('ship_exists', '0');
    update_option('myparcel_shopid', '');
}

/**
 *
 * @return void
 */
function addSettingMenu()
{
    add_options_page(
        'API Setting',
        MYPARCEL_API_SETTING_TEXT,
        'manage_options',
        'api_setting',
        'settingPage'
    );
}

add_action('admin_menu', 'addSettingMenu');

/**
 *
 * @return void
 */
function settingPage()
{
    global $woocommerce;
    prepareHtmlForSettingPage();
}


