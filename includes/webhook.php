<?php
require_once("../../../../wp-load.php");
$responseData = file_get_contents("php://input");
$log_filename = plugin_dir_path( __DIR__ ).'includes/request.log';	
file_put_contents($log_filename, $responseData);
?>
