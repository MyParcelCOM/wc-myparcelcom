<?php
	$responseData = file_get_contents("php://input");
	$log_filename = 'request.log';
	$responseData = 'New Webhook log ::- '.date('Y-m-d H:i:s').' array : ' .$responseData; 	
	file_put_contents($log_filename, $responseData, FILE_APPEND);
?>
