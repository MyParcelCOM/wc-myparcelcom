<?php 

use MyParcelCom\ApiSdk\Resources\ShipmentItem;

// Fetch order data using orderid
function getOrderData($orderId) 
{
	$order          = wc_get_order( $orderId );
    $order_data     = $order->get_data();
    return $order_data; 
}

// Fetch order items using orderid
function getOrderItems($orderId) 
{
	$order          = wc_get_order( $orderId );
    $items          = $order->get_items();
    return $items; 
}

/* Fetch order items using orderid*/
function getWeightByProductId($productId) {
	$product     = wc_get_product( $productId );          
    $weight      = $product->get_weight();   
    return $weight; 
}

// fetching total weight by postId
function getTotalWeightByPostID($postId) 
{
	$items = getOrderItems($postId);
    $totalWeight = 0;
    foreach ($items as $item) {
        $product = wc_get_product($item['product_id']);
        // Now you have access to (see above)...
        $quantity = $item->get_quantity(); // get quantity
        $product = $item->get_product(); // get the WC_Product object
        $product_weight = $product->get_weight(); // get the product weight
        $totalWeight += floatval($product_weight * $quantity);
    }
    return $totalWeight; 
}

//set Ship item for non EU country by post id
function setItemForNonEuCountries($orderId, $currency) 
{
	global  $woocommerce;
	$items = getOrderItems($orderId);	
	$shipAddItems = array();
	foreach ($items as $item) {
	    $shipItems = new ShipmentItem();
	    $product = wc_get_product($item['product_id']);
	    // Now you have access to (see above)...
	    $quantity = $item->get_quantity(); // get quantity
	    $product = $item->get_product(); // get the WC_Product object
	    $product_weight = $product->get_weight(); // get the product weight
	    $order_shipping_weight = $product->get_weight();
	    $productName = $product->get_name();
	    $sku = ($product->get_sku()) ? $product->get_sku() : 'NA';    // Get the product SKU
	    $price = $product->get_price(); // Get the product price
	    $itemValue = ($price * 1) * 100;
	    $shipItems
	        ->setSku($sku)
	        ->setDescription($productName)
	        ->setQuantity($quantity)
	        ->setItemValue($itemValue)
	        ->setCurrency($currency);

	    $shipAddItems[] = $shipItems;
	}
	return $shipAddItems; 
}

//set Ship item for EU country by post id
//set Ship item for EU country by post id
function setItemForEuCountries($orderId) 
{
	global  $woocommerce;
	$items = getOrderItems($orderId);
	$shipAddItems = array();
	foreach ($items as $item) {
        $shipItems = new ShipmentItem();
        $product = wc_get_product($item['product_id']);
        $price = $product->get_price(); // Get the product price
        $quantity = $item->get_quantity(); // get quantity
        $productName = $product->get_name();
        $shipItems
            ->setDescription($productName)
            ->setQuantity($quantity);
        $shipAddItems[] = $shipItems;
    }
    return $shipAddItems; 
}

function setOrderShipment($orderId, $itemId, $shipQty, $totalShipQty, $qty, $type="shipped", $weight, $remainQty, $flagStatus) 
{
	$shipmentNewAr = array();
	$shipmentNewAr['order_id'] 		= $orderId;
    $shipmentNewAr['item_id'] 		= $itemId;
    $shipmentNewAr['shipped'] 		= $shipQty;
    $shipmentNewAr['total_shipped'] = $totalShipQty;
    $shipmentNewAr['qty'] 			= $qty;
    $shipmentNewAr['type'] 			= $type;
    $shipmentNewAr['weight'] 		= $weight;
    $shipmentNewAr['remain_qty'] 	= $remainQty;
    $shipmentNewAr['flagStatus'] 	= $flagStatus;
    return $shipmentNewAr;     
}

function extractShipmentItemArr($shippedItems, $ifShipmentTrue, &$totalWeight) {
	$shippedItemeArray 	= array();
	$shippedItemsNewArr = [];
	$shippedCount 		= 0;
	foreach ($shippedItems as $key => $shippedItem) {
	    $type               = $shippedItem['type'];
	    $shippedQtyNew      = $shippedItem['shipped'];
	    $totalShippedQtyNew = $shippedItem['total_shipped'];
	    $totalQtyNew        = $shippedItem['qty'];
	    $remainQtyNew       = $shippedItem['remain_qty'];
	    $weightNew          = $shippedItem['weight'];
	    $flagStatus         = $shippedItem['flagStatus'];
	    $item_id            = $shippedItem['item_id'];
	    if (1 == $ifShipmentTrue) {
	        if ($remainQtyNew == 0) {  //logic for weight > 0
	            $totalWeight += $weightNew * $totalQtyNew;
	        } else {
	            $totalWeight += $weightNew * $totalShippedQtyNew;  // All shipped quantity
	        }
	        $shippedItem["flagStatus"] = 1;
	    } else {
	        if (0 == $flagStatus) {
	            $totalWeight += $weightNew * $shippedQtyNew;
	            $shippedItem["flagStatus"] = 1;
	            array_push($shippedItemeArray, array(
	                "item_id" => $item_id,
	                "shipped" => $shippedQtyNew,
	                "weight" => $totalWeight
	            ));
	        } else {
	            $shippedCount++;
	        }
	    }
	    array_push($shippedItemsNewArr, $shippedItem);
	}
	$response = array(
		"shippedItemeArray" 	=> $shippedItemeArray,
		"shippedItemsNewArr"	=> $shippedItemsNewArr,
		"shippedCount"  		=> $shippedCount
	);
	return $response;
}
