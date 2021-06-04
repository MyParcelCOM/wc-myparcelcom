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
    if (!empty($_SESSION['errormessage'])) {
        ?>
      <div class="notice-error notice is-dismissible">
        <p><?php _e($_SESSION['errormessage'], 'woocommerce'); ?></p>
      </div>
        <?php
    }
    unset($_SESSION['errormessage']);
}

add_action('admin_enqueue_scripts', 'adminLoadJsCss', 999);
