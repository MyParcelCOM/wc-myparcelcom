<?php declare(strict_types=1);

add_action('admin_enqueue_scripts','settingPageJsCss',999);

/**
 *
 * @return void
 */
function settingPageJsCss(): void
{	
	wp_enqueue_script('validation',plugins_url('',__FILE__).'/../assets/admin/js/jquery.validate.js','','',false);
	wp_register_script('setting_page_js',plugins_url('',__FILE__).'/../assets/admin/js/setting-page.js','','',true);
	wp_enqueue_script('setting_page_js');
}

add_action( 'admin_init', 'registerSettings' );

/** 
 * Register setting in setting panel
 *
 * @return void
 */
function registerSettings(): void
{
   	add_option( 'api_url', '');   
	add_option( 'api_auth_url', '');
	add_option( 'client_key', '');
	add_option( 'client_secret_key', '');
	add_option( 'ship_exists', '0');
	add_option( 'label_size', '1');
	add_option( 'street1', '');   
	add_option( 'street_number', '');
	add_option( 'city', '');
	add_option( 'postal_code', '');
	add_option( 'country_code', '');	
	add_option( 'phone_number', '');
	add_option( 'company_name', '');
   	register_setting( 'myplugin_options_group', 'api_url');
   	register_setting( 'myplugin_options_group', 'api_auth_url');
   	register_setting( 'myplugin_options_group', 'client_key');
   	register_setting( 'myplugin_options_group', 'client_secret_key');
   	register_setting( 'myplugin_options_group', 'ship_exists');
   	register_setting( 'myplugin_options_group', 'label_size');
   	register_setting( 'myplugin_options_group', 'street1');
   	register_setting( 'myplugin_options_group', 'street_number');
   	register_setting( 'myplugin_options_group', 'city');
   	register_setting( 'myplugin_options_group', 'postal_code');
   	register_setting( 'myplugin_options_group', 'country_code');
   	register_setting( 'myplugin_options_group', 'phone_number');
   	register_setting( 'myplugin_options_group', 'company_name');
   	register_setting( 'myplugin_options_group', 'checkValidation','validationCallBack');
}

/**
 *
 * @return bool
 */
function validationCallBack(): bool
{
	$error = false;
	$apiUrl 	= get_option('api_url');
	$authUrl 	= get_option('api_auth_url');
	$clientKey 	= get_option('client_key');
	$secretKey 	= get_option('client_secret_key');
	
	$street1 	= get_option( 'street1');   
	$streetNumber = get_option( 'street_number');
	$city = get_option('city');
	$postalCode = get_option('postal_code');
	$countryCode = get_option('country_code');	
	$phoneNumber = get_option('phone_number');
	$companyName = get_option('company_name');

	if (empty($apiUrl) || empty($authUrl) || empty($clientKey) || empty($secretKey) 
		|| empty($street1) || empty($streetNumber) || empty($city) 
		|| empty($postalCode) || empty($countryCode) || empty($countryCode) || empty($companyName) ) {
		$error = true;
	}	
	if ($error) {	
		add_settings_error('show_message',esc_attr('settings_updated'),__('Settings NOT saved. Please fill all the required fields.'),'error');
    	add_action('admin_notices', 'printErrors');	
    	updateOption();

    	return false;
	}else{
		
		add_settings_error('show_message',esc_attr('settings_updated'),__('Settings saved.'),'updated');
    	add_action('admin_notices', 'printErrors');	

    	return true;
	}
    
}
/**
 *
 * @return void
 */
function printErrors(): void
{
    settings_errors( 'show_message' );
}

/**
 *
 * @return void
 */
function updateOption(): void
{
	update_option('api_url','');
	update_option('api_auth_url','');
	update_option('client_key','');
	update_option('client_secret_key','');
	update_option( 'street1', '');   
	update_option( 'street_number', '');
	update_option( 'city', '');
	update_option( 'postal_code', '');
	update_option( 'country_code', '');	
	update_option( 'phone_number', '');
	update_option( 'company_name', '');
}

add_action('admin_menu', 'addSettingMenu');

/** 
 *
 * @return void
 */
function addSettingMenu(): void
{
  add_options_page('API Setting', 'My Parcel API Setting', 'manage_options', 'api_setting', 'settingPage');
  //add_menu_page('API Setting', 'My Parcel API Setting', 'manage_options', 'api_setting', 'settingPage');
}

/** 
 *
 * @return void
 */
function settingPage(): void
{
	global $woocommerce;
    $countries_obj = new WC_Countries();
    $countries = $countries_obj->__get('countries');

?>
  	<div>
	  
	  	<h2>Myparcle.com API Setting</h2>

	  	<form method="post" action="options.php" id="api-setting-form">
	  		<?php 
	  		settings_fields( 'myplugin_options_group' );

	  		?>
	  		 
		    <table class="form-table">
				
				<tbody>

					<tr valign="top">
					  	<th scope="row"><label for="api_url">* Api Url </label></th>
					  	<td>
					  		<input type="text" id="api_url" class="regular-text" name="api_url" value="<?php echo get_option('api_url'); ?>" />
					  	</td>
					</tr>
		
					<tr valign="top">
					  	<th scope="row"><label for="api_auth_url">* Api Auth Url </label></th>
					  	<td>
					  		<input type="text" id="api_auth_url" class="regular-text" name="api_auth_url" value="<?php echo get_option('api_auth_url'); ?>" />
					  	</td>
					</tr>
					<tr valign="top">
					  	<th scope="row"><label for="client_key">* Client Key </label></th>
					  	<td>
					  		<input type="text" id="client_key" class="regular-text" name="client_key" value="<?php echo get_option('client_key'); ?>" />
					  	</td>
					</tr>
					<tr valign="top">

					  	<th scope="row"><label for="client_secret_key">* Client Secret Key </label></th>
					  	<td>
					  		<input type="text" id="client_secret_key"  class="regular-text" name="client_secret_key" value="<?php echo get_option('client_secret_key'); ?>" />
					  	</td>
					</tr>

				</tbody>
				

		    </table>

		    <h2>Shipment Setting</h2>

		    <table class="form-table">
				
				<tbody>

					<tr valign="top">
					  	<th scope="row"><label for="ship_exists">* Create New Shipment if one already exists </label></th>
					  	<td>
					  		<select name="ship_exists" id="ship_exists" class="regular-text">
					  			<option value="0" <?php if(get_option('ship_exists') == 0){ ?> selected="selected" <?php } ?> >No</option>
					  			<option value="1" <?php if(get_option('ship_exists') == 1){ ?> selected="selected" <?php } ?>>Yes</option>
					  		</select>
					  	</td>
					</tr>

					<tr valign="top">
					  	<th scope="row"><label for="label_size">* Label Size </label></th>
					  	<td>
					  		<select name="label_size" id="label_size" class="regular-text">
					  			<option value="1" <?php if(get_option('label_size') == 1){ ?> selected="selected" <?php } ?> >A-4</option>
					  			<option value="2" <?php if(get_option('label_size') == 2){ ?> selected="selected" <?php } ?>>A-6</option>
					  		</select>
					  	</td>
					</tr>

					<tr><th>Shipment Sender Address</th></tr>
					<tr valign="top">
					  	<th scope="row"><label for="street1">* Street1 </label></th>
					  	<td>
					  		<input type="text" id="street1" class="regular-text" name="street1" value="<?php echo get_option('street1'); ?>" />
					  	</td>
					</tr>
					<tr valign="top">

					  	<th scope="row"><label for="street_number">* Street Number </label></th>
					  	<td>
					  		<input type="text" id="street_number"  class="regular-text" name="street_number" value="<?php echo get_option('street_number'); ?>" />
					  	</td>
					</tr>
					<tr valign="top">

					  	<th scope="row"><label for="city">* City </label></th>
					  	<td>
					  		<input type="text" id="city"  class="regular-text" name="city" value="<?php echo get_option('city'); ?>" />
					  	</td>
					</tr>
					<tr valign="top">
					  	<th scope="row"><label for="postal_code">* Postal Code </label></th>
					  	<td>
					  		<input type="text" id="postal_code"  class="regular-text" name="postal_code" value="<?php echo get_option('postal_code'); ?>" />
					  	</td>
					</tr>
					<tr valign="top">
					  	<th scope="row"><label for="country_code">* Country </label></th>
					  	<td>
					  		<select name="country_code" id="country_code" class="regular-text">
					  			<option value=""> Select Country </option>
					  			<?php if (!empty($countries)) {?>

					  				<?php foreach ($countries as $key => $value) { ?>
					  					
					  					<option value="<?php echo $key; ?>" <?php if(get_option('country_code') == $key){ ?> selected="selected" <?php } ?> ><?php echo $value; ?></option>

					  				<?php }?>

					  			<?php } ?>
					  			
					  		</select>
					  	</td>
					</tr>
					<tr valign="top">
					  	<th scope="row"><label for="phone_number">* Phone Number </label></th>
					  	<td>
					  		<input type="text" id="phone_number"  class="regular-text" name="phone_number" value="<?php echo get_option('phone_number'); ?>" />
					  	</td>
					</tr>
					<tr valign="top">
					  	<th scope="row"><label for="company_name">* Company </label></th>
					  	<td>
					  		<input type="text" id="company_name"  class="regular-text" name="company_name" value="<?php echo get_option('company_name'); ?>" />
					  	</td>
					</tr>

				</tbody>
		    </table>
			<h2>Myparcle.com</h2>
			<table  cellpadding = "5" cellspacing = "5" class="form-table">
		         <tr valign="top">
		            <th scope="row"><label>Current Version</label> </th>
		            <td>1.0</td>
		         </tr>
		         <tr valign="top">
		            <th scope="row"><label>Myparcle.com Support</label> </th>
		            <td><a href="http://help.myparcel.com/support/home" target="_blank">http://help.myparcel.com/support/home</a></td>
		         </tr>
		         <tr valign="top">
		            <th scope="row"><label>MyParcel.com backoffice</label></th>
		            <td><a href="https://sandbox-backoffice.myparcel.com/settings" target="_blank">https://sandbox-backoffice.myparcel.com/settings</a></td>
		         </tr>
		     </table>			
	  		<?php submit_button(); ?>
	  	</form>
  	</div>
<?php
} ?>