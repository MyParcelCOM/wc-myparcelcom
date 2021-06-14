<?php

declare(strict_types=1);

function adminLoadJsCss()
{
    echo '<script>const myparcelAdminAjaxUrl = "' . admin_url('admin-ajax.php') . '"</script>';
    $screen = get_current_screen();

    if ('edit-shop_order' === $screen->id) {
        wp_enqueue_style('myparcelcom-orders', plugins_url('', __FILE__) . '/../assets/admin/css/admin-orders.css');
        wp_enqueue_script('jquery-ui-dialog');
        wp_register_script(
            'myparcelcom-orders',
            plugins_url('', __FILE__) . '/../assets/admin/js/admin-orders.js',
            ['jquery'],
            '',
            true
        );
        wp_enqueue_script('myparcelcom-orders');
    }
}

add_action('admin_enqueue_scripts', 'adminLoadJsCss', 999);
