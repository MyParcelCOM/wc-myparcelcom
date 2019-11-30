<?php
require_once("../../../../wp-load.php");
$responseData = file_get_contents("php://input");
$option_name  = MYPARCEL_WEBHOOK_RESPONSE;
if (get_option($option_name) !== false) {
    update_option($option_name, $responseData);
} else {
    $deprecated = null;
    $autoload   = 'no';
    add_option($option_name, $responseData, $deprecated, $autoload);
}
?>
