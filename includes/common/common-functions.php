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
function setItemForNonEuCountries($orderId, $currency, $shippedItemsNewArr) 
{
	global  $woocommerce;
	$items = getOrderItems($orderId);	
	$shipAddItems = array();
	
	$getShippedItems = json_decode($shippedItemsNewArr, true);
	if ($getShippedItems) {
		foreach ($getShippedItems as $getShippedItem) {
			$item_id 		= $getShippedItem["item_id"];			
			$shipItems = new ShipmentItem();
			$product = wc_get_product($items[$item_id]['product_id']);
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
	} else {		
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
	}
	return $shipAddItems; 
}

//set Ship item for EU country by post id
function setItemForEuCountries($orderId, $shippedItemsNewArr) 
{
	global  $woocommerce;
	$items = getOrderItems($orderId);
	$shipAddItems = array();

	$getShippedItems = json_decode($shippedItemsNewArr, true);
	if ($getShippedItems) {
		foreach ($getShippedItems as $getShippedItem) {
			$item_id 		= $getShippedItem["item_id"];
			$shipItems 		= new ShipmentItem();
	        $product 		= wc_get_product($items[$item_id]['product_id']);
	        $price 			= $product->get_price(); // Get the product price
	        $quantity 		= $getShippedItem["shipped"]; // get quantity
	        $productName 	= $product->get_name();
	        $shipItems
	            ->setDescription($productName)
	            ->setQuantity($quantity);
	        $shipAddItems[] = $shipItems;
		}
	}else{
		foreach ($items as $item) {
	        $shipItems 		= new ShipmentItem();
	        $product 		= wc_get_product($item['product_id']);
	        $price 			= $product->get_price(); // Get the product price
	        $quantity 		= $item->get_quantity(); // get quantity
	        $productName 	= $product->get_name();
	        $shipItems
	            ->setDescription($productName)
	            ->setQuantity($quantity);
	        $shipAddItems[] = $shipItems;
	    }
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

function setShipmentTrackingMeta($shippedTrackingArray, $shipmentTrackKey, $shippedItemeArray, $postId) 
{
	$shipTrackingArray = array(
        "trackingKey" =>  $shipmentTrackKey,
        "items"       =>  $shippedItemeArray     
    );
    array_push($shippedTrackingArray,$shipTrackingArray);
    $shippedTrackingArray = json_encode($shippedTrackingArray);
    update_post_meta($postId,'shipment_track_key',$shippedTrackingArray);
}

function updateShipmentKey($shipKey, $postId)
{
	if (!empty($shipKey)) {
        update_post_meta($postId, 'myparcel_shipment_key', $shipKey); //Update the shipment key on database
    } else {
        add_post_meta($postId, 'myparcel_shipment_key', uniqid()); //Update the shipment key on database
    }
}

function prepareHtmlForUpdateQuantity($shipped, $key, $itemQuantity, $orderId, $itemId, &$qtyHtml, &$tdHtml, &$remainHtml) {
	if (is_int($key)) {
        if (isset($shipped[$key]['type']) && 'shipped' == $shipped[$key]['type']) {
            if (isset($shipped[$key]['total_shipped']) && $shipped[$key]['total_shipped'] == $itemQuantity) {
                $addRemainQty = (isset($shipped[$key]['remain_qty'])) ? $shipped[$key]['remain_qty'] : $shipped[$key]['qty']; 

                $qtyHtml = '<input type="text" name="ship_qty" class="ship_qty ship_qty_'.$itemId.'" value="'.$shipped[$key]['total_shipped'].'" data-flag-id="0" data-rqty="'.$addRemainQty.'" data-qty="'.$itemQuantity.'" data-old-qty="'.$shipped[$key]['total_shipped'].'" data-item-id="'.$itemId.'" data-order-id="'.$orderId.'" style="width: 43px;"/>';
                
                $tdHtml  = '<a href="javascript:void(0);" class="partial-anchor-top partial-anchor-top-'.$itemId.'" title="Shipped: '.$shipped[$key]['total_shipped'].'/'.$itemQuantity.'"><span class="new-shipped-color ship-status ship-status-'.$itemId.'">Updated Shipping Qty - '.$shipped[$key]['total_shipped'].'</span></a>';
                
                $remainHtml    = '<a href="javascript:void(0);" class="partial-anchor-remain-'.$itemId.'"><span class="remain-qty">'.$shipped[$key]['remain_qty'].'</span></a>';

            } elseif(isset($shipped[$key]['total_shipped']) && $shipped[$key]['total_shipped']>0 && isset($shipped[$key]['total_shipped']) && $shipped[$key]['total_shipped']<$itemQuantity) {
                $addRemainQty = (!empty($shipped[$key]['remain_qty'])) ? $shipped[$key]['remain_qty'] : $shipped[$key]['qty'];$qtyHtml = '<input type="text" name="ship_qty" class="ship_qty ship_qty_'.$itemId.'" value="'.$shipped[$key]['total_shipped'].'" data-flag-id="0" data-rqty="'.$addRemainQty.'" data-qty="'.$itemQuantity.'" data-old-qty="'.$shipped[$key]['total_shipped'].'" data-item-id="'.$itemId.'" data-order-id="'.$orderId.'" style="width: 43px;"/>';
                $tdHtml = '<a href="javascript:void(0);" class="partial-anchor-top partial-anchor-top-'.$itemId.'" title="Partially Shipped: '.$shipped[$key]['total_shipped'].'/'.$itemQuantity.'"><span class="partial-shipped-color ship-status ship-status-'.$itemId.'">Partially Shipped - '.$shipped[$key]['total_shipped'].'</span></a>';                                
                $remainHtml    = '<a href="javascript:void(0);" class="partial-anchor-remain-'.$itemId.'"><span class="remain-qty">'.$shipped[$key]['remain_qty'].'</span></a>';
            }
        }
    } else {
        $qtyHtml    = '<input type="text" name="ship_qty" class="ship_qty ship_qty_'.$itemId.'" value="'.$itemQuantity.'" data-flag-id="0" data-rqty="'.$itemQuantity.'" data-qty="'.$itemQuantity.'" data-old-qty="0" data-item-id="'.$itemId.'" data-order-id="'.$orderId.'" style="width: 43px;"/>';
        $tdHtml     = '<a href="javascript:void(0);" class="partial-anchor-top partial-anchor-top-'.$itemId.'" title="Not Shipped"><span class="not-shipped-color ship-status ship-status-'.$itemId.'">Not Shipped - '.$itemQuantity.'</span></a>';
        $remainHtml    = '<a href="javascript:void(0);" class="partial-anchor-remain-'.$itemId.'"><span class="remain-qty">'.$itemQuantity.'</span></a>';
    }
}

