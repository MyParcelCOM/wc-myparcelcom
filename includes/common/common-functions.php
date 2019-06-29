<?php 
/* Fetch order data using orderid*/
function getOrderData($orderId) {
	$order          = wc_get_order( $orderId );
    $order_data     = $order->get_data();
    return $order_data; 
}

/* Fetch order items using orderid*/
function getOrderItems($orderId) {
	$order          = wc_get_order( $orderId );
    $items          = $order->get_items();
    return $items; 
}