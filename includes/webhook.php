<?php
	$responseData = file_get_contents("php://input");
	$log_filename = 'request.log';	
	file_put_contents($log_filename, $responseData);
?>
