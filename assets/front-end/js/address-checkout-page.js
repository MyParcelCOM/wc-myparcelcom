jQuery(document).on('click','.different_delivery',function(){

	jQuery('.myparcelcom-pudo-location').each(function(){

		if(jQuery(this).hasClass('myparcelcom-active')){


			var companyName 	= jQuery.trim(jQuery(this).find('.myparcelcom-pudo-location-info .myparcelcom-pudo-location-company').text());
			var streetAddress 	= jQuery.trim(jQuery(this).find('.myparcelcom-pudo-location-info .myparcelcom-pudo-location-details:eq(0)').text());
			var city 			= jQuery.trim(jQuery(this).find('.myparcelcom-pudo-location-info .myparcelcom-pudo-location-details:eq(1)').text());
			var postalCode  ='NW16XE';
			var countryCode ='GB';

			jQuery('#ship-to-different-address-checkbox').attr('checked',true);
			jQuery('.shipping_address').show();
			jQuery("#shipping_country option:selected").removeAttr("selected");

			jQuery('#shipping_country option[value="'+countryCode+'"]').attr('selected','selected');
			
			var countryText = jQuery('#shipping_country option:selected').text();

			jQuery('#select2-shipping_country-container').text(countryText);
			jQuery('#select2-shipping_country-container').attr('title',countryText);

			jQuery('#shipping_company').val(companyName);
			jQuery('#shipping_address_1').val(streetAddress);
			jQuery('#shipping_city').val(city);
			jQuery('#shipping_postcode').val(postalCode);

            
		}

	});
	

});