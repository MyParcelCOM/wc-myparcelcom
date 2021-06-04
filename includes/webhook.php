<?php
require_once("../../../../wp-load.php");
$responseData = file_get_contents("php://input");

// TODO: this logic needs to be rewritten. Remove all "option" magic, just update the order straight away. Also make it work for production.

$optionName = MYPARCEL_WEBHOOK_RESPONSE;
if (get_option($optionName) !== false) {
    update_option($optionName, $responseData);
} else {
    $deprecated = null;
    $autoload   = 'no';
    add_option($optionName, $responseData, $deprecated, $autoload);
}
