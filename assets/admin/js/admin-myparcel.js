jQuery(function($){    
    // $(document).on("click","#doaction,#doaction2", function(event){

    //     var selectedBulkVal = $("#bulk-action-selector-top option:selected").text();
    //     // console.log('currecnt val: ',selectedVal);
    //     if("Export orders to MyParcel.com" === selectedBulkVal){
    //         var orderArr = [] ; 
    //         $("th.check-column input[type='checkbox']:checked").each(function() {
    //             if($(this).val()){
    //                     orderArr.push($(this).val());
    //                     var current_parent = $(this).parent().parent().find('td.partial_shipment_status').text();
    //                     if('Yes' === current_parent){
    //                         event.preventDefault();
    //                         // console.log('Vinod: ',orderArr);
    //                         $("#confirm-modal").modal('show');

    //                          $('#getConfirmValue').click(function() {
    //                                 var selOrderTypeValue = $('input[name=partialshippend]:checked').val(); 
    //                                 var dataStr = 'action=order_set_export&check_order_id='+selOrderTypeValue+'&orderArr='+orderArr;
    //                                 exportToMyParcle(dataStr);
    //                                 $("#confirm-modal").modal('hide');                                    
    //                             });


    //                     }else{
    //                         $("#posts-filter").submit();
    //                     }                    
    //                 }
    //         });        
    //     }else{
    //         $("#posts-filter").submit();
    //     }
    // });
    function getURLParameter(url, name) {
        return (RegExp(name + '=' + '(.+?)(&|$)').exec(url)||[,null])[1];
    }    

    // $(document).on('change','.ship_qty',function(){
    $(document).ready(function () {   
        $('.btn-quanity-update').click(function (){
            var sortVal = $(this).parent();            
            var postVal = sortVal.parent().find('td.name').children('a.wc-order-item-name').attr('href');
            var productId = getURLParameter(postVal, 'post');
            
            var qty = $(this).parent().find('.ship_qty').data('qty');
            var rqty = parseInt($(this).parent().find('.ship_qty').data('rqty'));
            // var qty = $(this).data('qty');
            var flagStatus = parseInt($(this).parent().find('.ship_qty').data('flag-id'));
            var shipQty = $(this).parent().find('.ship_qty').val();

            var oldQty = $(this).parent().find('.ship_qty').data('old-qty');
            var digitReg = /[^0-9]/g;

            var itemId = $(this).parent().find('.ship_qty').data('item-id');
            var orderId = $(this).parent().find('.ship_qty').data('order-id');
            // console.log('itemId ',itemId, 'orderId ',orderId);
            // return false; 
            
            if(digitReg.test(shipQty))
            {
                var validationError = '<span class="qty-error"><br>Please enter only number.</span>';
                console.log('Checking old values:- ',this);
                $(this).parent().find('.ship_qty').val(oldQty);
                $('.qty-error').remove();
                $(this).parent().find('.btn-quanity-update').after(validationError);
                return false;

            }
            // console.log("shipQty",shipQty);
            // console.log("rqty",rqty);

            if(shipQty == 0)
            {
                var validationError = '<span class="qty-error"><br>Value should be greater then 0.</span>';
                console.log('Checking old values:- ',this);
                $(this).parent().find('.ship_qty').val(oldQty);
                $('.qty-error').remove();
                $(this).parent().find('.btn-quanity-update').after(validationError);
                return false;
            }else if(rqty <= 0)
            {
                var validationError = '<span class="qty-error"><br>All quantity are shipped for this item!.</span>';
                console.log('Checking old values:- ',this);
                $(this).parent().find('.ship_qty').val(oldQty);
                $('.qty-error').remove();
                $(this).parent().find('.btn-quanity-update').after(validationError);
                return false;
            }else if(shipQty > rqty)
            {
                var validationError = '<span class="qty-error"><br>You can\'t enter qty greater than '+rqty+'.</span>';
                $(this).parent().find('.ship_qty').val(oldQty);
                $('.qty-error').remove();
                $(this).parent().find('.btn-quanity-update').after(validationError);
                return false;
            }else
            {
                var cur = $(this).parent().parent();
                $(".qty-error", cur).remove();
            }
            
            var dataStr = 'action=order_set_shipped&order_id='+orderId+'&item_id='+itemId+'&qty='+qty+'&ship_quantity='+shipQty+'&productId='+productId+'&flagStatus='+flagStatus;
            console.log('Get Datastr:-',dataStr); 
            orderSetShipped(dataStr);
        });
    }) ;    
    // });

    function orderSetShipped(dataStr){

        $.ajax({
            type: "POST",
            data: dataStr,
            dataType: 'json',
            cache: false,
            async: false,
            url: ajaxUrl,
            success: function(res){
                console.log(res); 
                var orderId = res.order_id;
                var itemId = res.item_id;
                var shipQty = res.shipped;
                var qty = res.qty;
                var remain_qty = res.remain_qty;
                
                if(shipQty == 0){
                    
                    $('.partial-anchor-top-'+itemId).attr('title','Not Shipped');
                    $('.partial-anchor-top-'+itemId).html('<span class="not-shipped-color ship-status ship-status-'+itemId+'">Not Shipped - '+qty+'</span>');
                    $('.partial-anchor-remain-'+itemId).html('<span class=".remain-qty">'+remain_qty+'</span>');
                    $('.ship_qty_'+itemId).data('rqty',qty);                    
                }
                
                if(shipQty != 0 && shipQty < qty){

                    $('.partial-anchor-top-'+itemId).attr('title','Partially Shipped: '+shipQty+'/'+qty);
                    $('.partial-anchor-top-'+itemId).html('<span class="partial-shipped-color ship-status ship-status-'+itemId+'">Partially Shipped - '+shipQty+'</span>');
                    $('.partial-anchor-remain-'+itemId).html('<span class=".remain-qty">'+remain_qty+'</span>');
                    $('.ship_qty_'+itemId).data('rqty',remain_qty);
                }
                
                if(shipQty == qty){
                    
                    $('.partial-anchor-top-'+itemId).attr('title','Shipped: '+shipQty+'/'+qty);
                    $('.partial-anchor-top-'+itemId).html('<span class="shipped-color ship-status ship-status-'+itemId+'">Shipped - '+shipQty+'</span>')
                    $('.partial-anchor-remain-'+itemId).html('<span class=".remain-qty">'+remain_qty+'</span>');
                    $('.ship_qty_'+itemId).data('rqty',remain_qty);
                }

                $('.ship_qty_'+itemId).data('old-qty',shipQty);
                $('.ship_qty_'+itemId).val(shipQty);
            }
        });

    }

    // function exportToMyParcle(dataStr){
    //     $.ajax({
    //         type: "POST",
    //         data: dataStr,
    //         dataType: 'json',
    //         // cache: false,
    //         // async: false,
    //         url: ajaxUrl,
    //         success: function(res){
    //             console.log('Recieving the response: ',res);
    //         }
    //     });

    // }

});